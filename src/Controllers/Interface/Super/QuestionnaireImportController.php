<?php

/**
 * Questionnaire import screen, the Import tab of the patient dashboard's assessments.
 *
 * Importing is gated on admin/forms, as it always was. Choosing where a new questionnaire is
 * offered changes what every user sees, so that part follows the availability screen and
 * needs admin/super; anyone else gets the default assignment.
 *
 * The page does its work through JSON posts, so each queued questionnaire can be reviewed and
 * reported on separately.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super;

use InvalidArgumentException;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\FHIR\Questionnaire\LhcFormConversionIssue;
use OpenEMR\Services\FHIR\Questionnaire\LhcFormToFhirConverter;
use OpenEMR\Services\Globals\CollapsedSections;
use OpenEMR\Services\Questionnaire\Import\LoincFormClient;
use OpenEMR\Services\Questionnaire\Import\LoincFormUnavailable;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRefusal;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRefused;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportRequest;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportReview;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class QuestionnaireImportController
{
    public const TEMPLATE = 'super/questionnaire_import.html.twig';
    public const PREVIEW_TEMPLATE = 'super/questionnaire_preview.html.twig';

    /** Cards that fold away and stay that way for the user. */
    public const SECTIONS = [
        'questionnaire_import_add_expand',
        'questionnaire_import_queue_expand',
        'questionnaire_import_destination_expand',
        'questionnaire_import_review_expand',
    ];

    /**
     * @param array{stylesheet: string, script: string, version: string, strings: string} $runtime
     */
    public function __construct(
        private readonly QuestionnaireImportService $importer,
        private readonly LoincFormClient $loinc,
        private readonly LhcFormToFhirConverter $converter,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
        private readonly LoggerInterface $logger,
        private readonly array $runtime,
        private readonly bool $portalEnabled,
        private readonly bool $showLoincNote,
        private readonly CollapsedSections $sections = new CollapsedSections(),
    ) {
    }

    /**
     * Show the importer or the preview page, or answer one of its JSON actions.
     *
     * Requires admin/forms; choosing where new questionnaires are offered also needs
     * admin/super. Every POST needs a valid CSRF token.
     */
    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('admin', 'forms')) {
            AccessDeniedHelper::denyWithTemplate(
                'ACL check failed for admin/forms: Questionnaire Import',
                xl('Questionnaire Import')
            );
        }
        $canAssign = AclMain::aclCheckCore('admin', 'super');

        if ($request->getMethod() === 'POST') {
            if (!CsrfUtils::verifyCsrfToken($request->request->getString('csrf_token_form'), $this->session)) {
                CsrfUtils::csrfNotVerified();
            }

            return $this->handlePost($request->request, $canAssign);
        }

        if ($request->query->getString('view') === 'preview') {
            return $this->renderPage(self::PREVIEW_TEMPLATE, $this->previewView($request->query->getInt('id')));
        }

        return $this->renderPage(self::TEMPLATE, $this->importView($request->query, $canAssign));
    }

    /**
     * Apply one submitted action. The CSRF token has already been checked.
     *
     * @param InputBag<bool|float|int|string> $post
     */
    public function handlePost(InputBag $post, bool $canAssign): JsonResponse
    {
        try {
            return match ($post->getString('action')) {
                'review' => new JsonResponse($this->reviewPayload($this->importer->review(
                    $post->getString('questionnaire'),
                    $post->getString('name') !== '' ? $post->getString('name') : null
                ))),
                'import' => $this->import($post, $canAssign),
                'loinc_search' => $this->loincSearch($post->getString('terms')),
                'loinc_fetch' => $this->loincForm($post->getString('loinc_number')),
                default => new JsonResponse(['error' => xl('Unknown action.')], Response::HTTP_BAD_REQUEST),
            };
        } catch (QuestionnaireImportRefused $e) {
            $this->logger->info('Questionnaire import refused', ['refusal' => $e->refusal->name, 'exception' => $e]);

            return new JsonResponse(['error' => self::refusalMessage($e->refusal)], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (InvalidArgumentException $e) {
            // A reviewed or imported resource with a field the FHIR model rejects. The LOINC
            // actions handle their own failures, so this is always the file or paste source.
            $this->logger->info('Questionnaire import refused', ['refusal' => QuestionnaireImportRefusal::InvalidJson->name, 'exception' => $e]);

            return new JsonResponse(
                ['error' => self::refusalMessage(QuestionnaireImportRefusal::InvalidJson)],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * Search NLM's LOINC forms by name or code.
     */
    private function loincSearch(string $terms): JsonResponse
    {
        try {
            return new JsonResponse(['results' => $this->loinc->search($terms)]);
        } catch (LoincFormUnavailable $e) {
            return $this->loincUnavailable($e);
        }
    }

    /**
     * Fetch a LOINC form and hand it back ready to review like any other source.
     */
    private function loincForm(string $loincNumber): JsonResponse
    {
        try {
            $conversion = $this->converter->convert($this->loinc->fetch($loincNumber));
        } catch (LoincFormUnavailable | InvalidArgumentException $e) {
            return $this->loincUnavailable($e);
        }
        $json = json_encode($conversion->questionnaire, JSON_THROW_ON_ERROR);

        $notes = [];
        foreach ($conversion->issues as $issue) {
            $notes[] = self::conversionNote($issue['issue'], $issue['question']);
        }

        return new JsonResponse(['json' => $json, 'notes' => $notes]);
    }

    /**
     * NLM being down, or a form it has no usable definition for, leaves the rest of the
     * screen working; the file and paste sources do not depend on it.
     */
    private function loincUnavailable(LoincFormUnavailable | InvalidArgumentException $e): JsonResponse
    {
        $this->logger->warning('LOINC form request failed', ['exception' => $e]);

        return new JsonResponse(
            ['error' => xl('That LOINC form could not be loaded. Try again, or import it from a file.')],
            Response::HTTP_BAD_GATEWAY
        );
    }

    /**
     * @param InputBag<bool|float|int|string> $post
     */
    private function import(InputBag $post, bool $canAssign): JsonResponse
    {
        $result = $this->importer->import(new QuestionnaireImportRequest(
            $post->getString('questionnaire'),
            $post->getString('name'),
            $post->getString('category'),
            self::parseTags($post->getString('tags')),
            $post->getBoolean('replace_existing'),
            $canAssign ? self::parseSurfaces($post->getString('surfaces')) : null,
            $this->portalEnabled && $post->getBoolean('create_template')
                ? $post->getString('template_category')
                : null,
        ));

        return new JsonResponse(['id' => $result->id, 'replaced' => $result->replaced]);
    }

    /**
     * @return array<string, mixed>
     */
    private function reviewPayload(QuestionnaireImportReview $review): array
    {
        return [
            'questionnaire' => $review->questionnaire,
            'name' => $review->name,
            'tags' => $review->tags,
            'suggestedCategory' => $review->suggestedCategory,
            'existing' => $review->existing,
        ];
    }

    /**
     * @param InputBag<string> $query
     * @return array<string, mixed>
     */
    public function importView(InputBag $query, bool $canAssign): array
    {
        $surfaces = [];
        if ($canAssign) {
            foreach (QuestionnaireImportService::SELECTABLE_SURFACES as $surface) {
                $surfaces[] = [
                    'value' => $surface->value,
                    'label' => self::surfaceLabel($surface),
                    'checked' => $surface === QuestionnaireSurface::Dashboard,
                ];
            }
        }

        return [
            'csrfToken' => CsrfUtils::collectCsrfToken($this->session),
            'runtime' => $this->runtime,
            'categories' => $this->importer->categories(),
            'surfaces' => $surfaces,
            'canAssign' => $canAssign,
            'portalEnabled' => $this->portalEnabled,
            'showLoincNote' => $this->showLoincNote,
            'templateCategories' => $this->portalEnabled ? $this->importer->templateCategories() : [],
            'templateCategory' => $query->getString('template_category'),
            'isDialog' => $query->getBoolean('dialog'),
            'source' => $query->getString('source'),
            'sections' => $this->sections,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function previewView(int $id): array
    {
        $preview = $this->importer->findForPreview($id);

        return [
            'runtime' => $this->runtime,
            'name' => $preview['name'] ?? '',
            'questionnaireJson' => $preview['json'] ?? '',
        ];
    }

    /**
     * @param array<string, mixed> $view
     */
    private function renderPage(string $template, array $view): Response
    {
        return new Response(
            $this->twig->render($template, $view),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * @return list<string>
     */
    private static function parseTags(string $tags): array
    {
        $labels = [];
        foreach (explode(',', $tags) as $tag) {
            $label = trim($tag);
            if ($label !== '' && !in_array($label, $labels, true)) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /**
     * @return list<QuestionnaireSurface>
     */
    private static function parseSurfaces(string $surfaces): array
    {
        $parsed = [];
        foreach (QuestionnaireImportService::SELECTABLE_SURFACES as $surface) {
            if (in_array($surface->value, explode(',', $surfaces), true)) {
                $parsed[] = $surface;
            }
        }

        return $parsed;
    }

    /**
     * A line for the review panel naming a question and what did not convert from LOINC.
     */
    private static function conversionNote(LhcFormConversionIssue $issue, string $question): string
    {
        $note = match ($issue) {
            LhcFormConversionIssue::UnknownDataType => xl('Unknown answer type, shown as text'),
            LhcFormConversionIssue::SkipLogicDropped => xl('Condition for showing this item could not be converted, so it always shows'),
            LhcFormConversionIssue::ScoreDropped => xl('Calculated score could not be converted'),
            LhcFormConversionIssue::RestrictionDropped => xl('An answer restriction could not be converted'),
        };

        return $question . ': ' . $note;
    }

    /**
     * The label shown for a surface a questionnaire can be offered on.
     */
    private static function surfaceLabel(QuestionnaireSurface $surface): string
    {
        return match ($surface) {
            QuestionnaireSurface::Dashboard => xl('Patient dashboard'),
            QuestionnaireSurface::Encounter => xl('Encounter forms'),
            QuestionnaireSurface::Portal => xl('Patient portal'),
            QuestionnaireSurface::Smart => xl('SMART launch'),
        };
    }

    /**
     * The message shown when the importer refuses a questionnaire.
     */
    private static function refusalMessage(QuestionnaireImportRefusal $refusal): string
    {
        return match ($refusal) {
            QuestionnaireImportRefusal::InvalidJson => xl('This is not valid FHIR Questionnaire JSON.'),
            QuestionnaireImportRefusal::NotQuestionnaire => xl('Only FHIR Questionnaire resources can be imported.'),
            QuestionnaireImportRefusal::MissingName => xl('Enter a name for this questionnaire.'),
            QuestionnaireImportRefusal::MissingCategory => xl('Choose a category.'),
            QuestionnaireImportRefusal::UnknownCategory => xl('That category is not available. Choose another.'),
            QuestionnaireImportRefusal::UnknownTemplateCategory => xl('That portal template category is not available. Choose another.'),
            QuestionnaireImportRefusal::ExistingNotConfirmed => xl('A questionnaire with this name already exists. Confirm replacing it or change the name.'),
        };
    }
}
