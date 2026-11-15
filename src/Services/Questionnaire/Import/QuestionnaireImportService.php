<?php

/**
 * Imports FHIR Questionnaires into the repository.
 *
 * Every staff import comes through here, whatever its source, so the same rules hold for a
 * file, pasted JSON or a LOINC form: the resource must parse, a category is required, a
 * questionnaire that already exists is replaced only when that was confirmed, and the name is
 * the FHIR title unless the administrator changed it.
 *
 * The FHIR API and encounter registration write through QuestionnaireService directly. They
 * are not screens, and FHIR has no required category to hold a client to.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire\Import;

use InvalidArgumentException;
use JsonException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;
use OpenEMR\Services\FHIR\Questionnaire\QuestionnaireItemNormalizer;
use OpenEMR\Services\Questionnaire\AssignmentRecord;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityLookups;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use OpenEMR\Services\QuestionnaireService;

class QuestionnaireImportService
{
    /**
     * Surfaces an import can offer a questionnaire on. The portal is absent because it does
     * not read assignments; the portal template option is what reaches it.
     */
    public const SELECTABLE_SURFACES = [
        QuestionnaireSurface::Dashboard,
        QuestionnaireSurface::Encounter,
        QuestionnaireSurface::Smart,
    ];

    private const PORTAL_REPOSITORY_PID = -1;
    private const DEFAULT_TEMPLATE_CATEGORY = 'questionnaire';

    public function __construct(
        private readonly QuestionnaireService $questionnaires,
        private readonly QuestionnaireAvailabilityService $availability,
        private readonly QuestionnaireAvailabilityLookups $lookups,
        private readonly DocumentTemplateService $templates,
    ) {
    }

    /**
     * Parse a questionnaire and report what importing it would do. Writes nothing.
     *
     * @param ?string $name the administrator's name for it, or null to use the resource's own
     * @throws QuestionnaireImportRefused if the JSON is not a usable Questionnaire
     */
    public function review(string $json, ?string $name = null): QuestionnaireImportReview
    {
        $questionnaire = $this->parse($json);
        $resolvedName = $this->resolveName($questionnaire, $name);
        $existing = $this->findExisting($questionnaire, $resolvedName);

        return new QuestionnaireImportReview(
            $questionnaire,
            $resolvedName,
            self::readTagLabels($questionnaire),
            $this->suggestCategory($questionnaire, $existing),
            $existing,
        );
    }

    /**
     * @throws QuestionnaireImportRefused if the import breaks one of the rules above
     */
    public function import(QuestionnaireImportRequest $request): QuestionnaireImportResult
    {
        $questionnaire = $this->parse($request->json);
        $name = $this->resolveName($questionnaire, $request->name);
        if ($name === '') {
            throw new QuestionnaireImportRefused(QuestionnaireImportRefusal::MissingName);
        }
        $this->assertCategory($request->category);
        $this->assertTemplateCategory($request->portalTemplateCategory);

        $existing = $this->findExisting($questionnaire, $name);
        if ($existing !== null && !$request->replaceExisting) {
            throw new QuestionnaireImportRefused(QuestionnaireImportRefusal::ExistingNotConfirmed);
        }

        $questionnaire = self::withTags($questionnaire, $request->tags);
        $questionnaire['status'] ??= 'active';

        // One transaction, so a failure in a follow-up write does not leave the questionnaire
        // saved without the availability or portal template that was asked for.
        $id = QueryUtils::inTransaction(function () use ($questionnaire, $name, $request, $existing): int {
            $id = $this->save($questionnaire, $name, $request->category, $existing);

            // Availability belongs to the first import. Replacing a questionnaire must leave
            // alone whatever an administrator has decided about it since.
            if ($existing === null && $request->surfaces !== null) {
                $this->applySurfaces($id, $name, $request->surfaces);
            }
            if ($request->portalTemplateCategory !== null) {
                $this->createPortalTemplate($id, $name, $request->portalTemplateCategory);
            }

            return $id;
        });

        return new QuestionnaireImportResult($id, $existing !== null);
    }

    /**
     * @return array<string, string> option id => title
     */
    public function categories(): array
    {
        return $this->lookups->categoryTitles();
    }

    /**
     * @return array<string, string> portal template category option id => title, '' is General
     */
    public function templateCategories(): array
    {
        $categories = [];
        foreach ($this->templates->fetchDefaultCategories() as $key => $row) {
            if (!is_array($row) || stripos((string)$key, 'repository') !== false) {
                continue;
            }
            $optionId = is_string($row['option_id'] ?? null) ? $row['option_id'] : '';
            $title = is_string($row['title'] ?? null) ? $row['title'] : '';
            $categories[$optionId] = $optionId === '' ? xl('General') : $title;
        }

        return $categories;
    }

    /**
     * An active questionnaire to preview. Inactive ones stay hidden, matching what every
     * surface will offer.
     *
     * @return ?array{name: string, json: string}
     */
    public function findForPreview(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $row = $this->questionnaires->fetchQuestionnaireById($id);
        $isActive = is_numeric($row['active'] ?? null) && (int)$row['active'] === 1;
        if (!$isActive || !is_string($row['questionnaire'] ?? null)) {
            return null;
        }

        return ['name' => is_string($row['name'] ?? null) ? $row['name'] : '', 'json' => $row['questionnaire']];
    }

    /**
     * @return array<mixed>
     * @throws QuestionnaireImportRefused
     */
    private function parse(string $json): array
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new QuestionnaireImportRefused(QuestionnaireImportRefusal::InvalidJson, $e);
        }
        if (!is_array($decoded) || ($decoded['resourceType'] ?? null) !== 'Questionnaire') {
            throw new QuestionnaireImportRefused(QuestionnaireImportRefusal::NotQuestionnaire);
        }

        try {
            [$normalized] = QuestionnaireItemNormalizer::normalizeQuestionnaire($decoded, true);
        } catch (InvalidArgumentException $e) {
            throw new QuestionnaireImportRefused(QuestionnaireImportRefusal::InvalidJson, $e);
        }

        return $normalized;
    }

    /**
     * @param array<mixed> $questionnaire
     */
    private function resolveName(array $questionnaire, ?string $name): string
    {
        foreach ([$name, $questionnaire['title'] ?? null, $questionnaire['name'] ?? null] as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return '';
    }

    /**
     * @param array<mixed> $questionnaire
     * @return ?array{id: int, name: string, version: int, category: string}
     */
    private function findExisting(array $questionnaire, string $name): ?array
    {
        $resourceId = is_string($questionnaire['id'] ?? null) ? $questionnaire['id'] : null;
        if ($name === '' && $resourceId === null) {
            return null;
        }
        $match = $this->questionnaires->getQuestionnaireIdAndVersion($name, $resourceId);
        $id = is_numeric($match['id'] ?? null) ? (int)$match['id'] : 0;
        if ($id <= 0) {
            return null;
        }
        $row = $this->questionnaires->fetchQuestionnaireById($id);

        return [
            'id' => $id,
            'name' => is_string($row['name'] ?? null) ? $row['name'] : $name,
            'version' => is_numeric($row['version'] ?? null) ? (int)$row['version'] : 1,
            'category' => is_string($row['category'] ?? null) ? $row['category'] : '',
        ];
    }

    /**
     * A tag naming a known category wins; otherwise the category it already has.
     *
     * @param array<mixed> $questionnaire
     * @param ?array{id: int, name: string, version: int, category: string} $existing
     */
    private function suggestCategory(array $questionnaire, ?array $existing): ?string
    {
        $categories = $this->lookups->categoryTitles();
        $byLabel = [];
        foreach ($categories as $optionId => $title) {
            $byLabel[strtolower($optionId)] = $optionId;
            $byLabel[strtolower($title)] = $optionId;
        }
        foreach (self::readTagCodings($questionnaire) as $coding) {
            foreach (['code', 'display'] as $field) {
                $value = $coding[$field] ?? null;
                if (is_string($value) && isset($byLabel[strtolower($value)])) {
                    return $byLabel[strtolower($value)];
                }
            }
        }
        if ($existing !== null && isset($categories[$existing['category']])) {
            return $existing['category'];
        }

        return null;
    }

    /**
     * Refuse an import with no category, or one not in the Observation_Types list.
     *
     * @throws QuestionnaireImportRefused
     */
    private function assertCategory(string $category): void
    {
        if ($category === '') {
            throw new QuestionnaireImportRefused(QuestionnaireImportRefusal::MissingCategory);
        }
        if (!array_key_exists($category, $this->lookups->categoryTitles())) {
            throw new QuestionnaireImportRefused(QuestionnaireImportRefusal::UnknownCategory);
        }
    }

    /**
     * Refuse a portal template category that is not in the template category list.
     * Null means no portal template was asked for.
     *
     * @throws QuestionnaireImportRefused
     */
    private function assertTemplateCategory(?string $category): void
    {
        if ($category !== null && !array_key_exists($category, $this->templateCategories())) {
            throw new QuestionnaireImportRefused(QuestionnaireImportRefusal::UnknownTemplateCategory);
        }
    }

    /**
     * @param array<mixed> $questionnaire
     * @param ?array{id: int, name: string, version: int, category: string} $existing
     */
    private function save(array $questionnaire, string $name, string $category, ?array $existing): int
    {
        if ($existing === null) {
            $saved = $this->questionnaires->saveQuestionnaireResource(
                $questionnaire,
                $name,
                null,
                null,
                null,
                null,
                $category
            );

            return is_numeric($saved) ? (int)$saved : 0;
        }

        $current = $this->questionnaires->fetchQuestionnaireById($existing['id']);
        // Keeps the row's own type: a questionnaire registered from an encounter stays one.
        $type = is_string($current['type'] ?? null) ? $current['type'] : null;
        $saved = $this->questionnaires->saveQuestionnaireResource(
            $questionnaire,
            $name,
            ['id' => $existing['id'], 'version' => $current['version'] ?? $existing['version']],
            null,
            null,
            $type,
            $category
        );

        return is_numeric($saved) ? (int)$saved : $existing['id'];
    }

    /**
     * Saving a new questionnaire offers it on the dashboard. A dashboard the administrator
     * left unticked is withdrawn rather than deleted, which is how the availability screen
     * records the same decision.
     *
     * @param list<QuestionnaireSurface> $surfaces
     */
    private function applySurfaces(int $id, string $name, array $surfaces): void
    {
        foreach (self::SELECTABLE_SURFACES as $surface) {
            $selected = in_array($surface, $surfaces, true);
            if ($selected || $surface === QuestionnaireSurface::Dashboard) {
                $this->availability->assign(new AssignmentRecord(0, $surface, resourceId: $id, active: $selected));
            }
            if ($selected && $surface === QuestionnaireSurface::Encounter) {
                $this->registerEncounterForm($id, $name);
            }
        }
    }

    /**
     * The encounter surface only filters forms in the registry, so offering a questionnaire
     * there also has to register it, as saving one from an encounter does.
     */
    private function registerEncounterForm(int $id, string $name): void
    {
        $directory = QuestionnaireAvailabilityService::ENCOUNTER_FORM_DIRECTORY;
        $registered = QueryUtils::fetchSingleValue(
            'SELECT `id` FROM `registry` WHERE `directory` = ? AND `name` = ? AND `form_foreign_id` > 0',
            'id',
            [$directory, $name]
        );
        if ($registered !== null) {
            return;
        }
        QueryUtils::sqlInsert(
            'INSERT INTO `registry` SET `name` = ?, `state` = 1, `directory` = ?, `sql_run` = 1,
                `unpackaged` = 1, `category` = ?, `date` = NOW(), `form_foreign_id` = ?',
            [$name, $directory, 'Questionnaires', $id]
        );
    }

    /**
     * Add a portal template to the repository that renders the questionnaire.
     */
    private function createPortalTemplate(int $id, string $name, string $category): void
    {
        $this->templates->insertTemplate(
            self::PORTAL_REPOSITORY_PID,
            $category !== '' ? $category : self::DEFAULT_TEMPLATE_CATEGORY,
            $name,
            '{ParseAsHTML}{Questionnaire:' . $id . '}' . "\n",
            'application/text'
        );
    }

    /**
     * @param array<mixed> $questionnaire
     * @return list<array<mixed>>
     */
    private static function readTagCodings(array $questionnaire): array
    {
        $meta = $questionnaire['meta'] ?? null;
        $tags = is_array($meta) ? ($meta['tag'] ?? null) : null;

        return is_array($tags) ? array_values(array_filter($tags, is_array(...))) : [];
    }

    /**
     * @param array<mixed> $questionnaire
     * @return list<string>
     */
    private static function readTagLabels(array $questionnaire): array
    {
        $labels = [];
        foreach (self::readTagCodings($questionnaire) as $coding) {
            $label = self::tagLabel($coding);
            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /**
     * @param array<mixed> $coding
     */
    private static function tagLabel(array $coding): string
    {
        foreach (['display', 'code'] as $field) {
            if (is_string($coding[$field] ?? null) && $coding[$field] !== '') {
                return $coding[$field];
            }
        }

        return '';
    }

    /**
     * Keeps the coding of any tag still listed, so a system or version from the source
     * survives, and adds the rest as new codings.
     *
     * @param array<mixed> $questionnaire
     * @param list<string> $labels
     * @return array<mixed>
     */
    private static function withTags(array $questionnaire, array $labels): array
    {
        $byLabel = [];
        foreach (self::readTagCodings($questionnaire) as $coding) {
            $byLabel[self::tagLabel($coding)] = $coding;
        }

        $tags = [];
        foreach ($labels as $label) {
            $tags[] = $byLabel[$label] ?? [
                'code' => strtolower((string)preg_replace('/\s+/', '-', $label)),
                'display' => $label,
            ];
        }

        $meta = is_array($questionnaire['meta'] ?? null) ? $questionnaire['meta'] : [];
        unset($meta['tag']);
        if ($tags !== []) {
            $meta['tag'] = $tags;
        }
        unset($questionnaire['meta']);
        if ($meta !== []) {
            $questionnaire['meta'] = $meta;
        }

        return $questionnaire;
    }
}
