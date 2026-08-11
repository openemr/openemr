<?php

/**
 * CarePlanController - MVC controller for the Care Plan encounter form.
 *
 * Delegates persistence to CarePlanFormService and renders output via Twig templates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Forms\CarePlan;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\ReasonStatusCodes;
use OpenEMR\Services\Forms\CarePlanFormService;
use OpenEMR\Services\FormService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class CarePlanController
{
    private const FORM_DIR = 'care_plan';

    private const TEMPLATE_ROOT = '/forms/care_plan/templates/';

    public function __construct(
        private readonly CarePlanFormService $carePlanFormService,
        private readonly FormService $formService,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
        private readonly LoggerInterface $logger,
        private readonly string $rootdir,
        private readonly string $webroot,
        private readonly string $jsIncludesVersion = '',
    ) {
    }

    /**
     * Render the care plan form. Backs both new.php and view.php.
     */
    public function newAction(Request $request): Response
    {
        if (!$this->formService->hasFormPermission(self::FORM_DIR)) {
            return $this->denyAccess('render');
        }

        $pid = $this->sessionInt('pid');
        $encounter = $this->sessionInt('encounter');
        $formId = $request->query->getInt('id');

        $existingFormMessage = '';
        if ($formId === 0) {
            $formId = $this->carePlanFormService->getExistingFormId($pid, $encounter);
            if ($formId > 0) {
                $existingFormMessage = xl("Already a Care Plan form for this encounter. Using existing Care Plan form.");
            }
        }

        $rows = [];
        if ($formId > 0) {
            $rows = $this->carePlanFormService->getCarePlanRows($formId, $pid, $encounter);
        }

        $reasonCodeStatii = ReasonStatusCodes::getCodesWithDescriptions();
        $reasonCodeStatii[ReasonStatusCodes::NONE]['description'] = xl("Select a status code");

        return $this->createResponse($this->twig->render($this->getTemplatePath('care_plan_form.html.twig'), [
            'formId' => $formId,
            'rows' => $rows,
            'existingFormMessage' => $existingFormMessage,
            'csrfToken' => CsrfUtils::collectCsrfToken(session: $this->session),
            'webroot' => $this->webroot,
            'rootdir' => $this->rootdir,
            'v_js_includes' => $this->jsIncludesVersion,
            'reasonCodeStatii' => $reasonCodeStatii,
            'authUser' => $this->sessionString('authUser'),
        ]));
    }

    /**
     * Persist the submitted form. Rows are replaced wholesale on every save.
     */
    public function saveAction(Request $request, int $userAuthorized): Response
    {
        if (!$this->formService->hasFormPermission(self::FORM_DIR)) {
            return $this->denyAccess('save');
        }

        CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

        $pid = $this->sessionInt('pid');
        $encounter = $this->sessionInt('encounter');
        $formId = $request->query->getInt('id');

        /** @var array<string, mixed> $postData */
        $postData = $request->request->all();

        // The submitted id is attacker-controlled. Confirm it names a care plan form this
        // encounter actually owns -- otherwise a positive id belonging to another
        // encounter deletes nothing and the rows below are written under a foreign,
        // unregistered form id.
        if (!$this->isSubmittedFormIdValid($formId, $pid, $encounter)) {
            $this->logger->warning('Care plan save rejected: form id does not belong to this encounter', [
                'formId' => $formId,
                'pid' => $pid,
                'encounter' => $encounter,
            ]);

            return $this->createResponse(xlt("Unauthorized access"), Response::HTTP_UNAUTHORIZED);
        }

        // Rows are replaced wholesale, so the delete and the re-insert have to be one
        // unit -- a failure part way through would otherwise leave the form truncated.
        QueryUtils::inTransaction(function () use ($postData, $formId, $pid, $encounter, $userAuthorized): void {
            if ($formId > 0) {
                $this->carePlanFormService->deleteCarePlanRows($formId, $pid, $encounter);
                $newId = $formId;
            } else {
                $newId = $this->carePlanFormService->getNextFormId();
                $this->carePlanFormService->registerForm($encounter, $newId, $pid, $userAuthorized);
            }

            $this->persistRows($postData, $newId, $pid, $encounter, $userAuthorized);
        });

        return $this->createResponse('');
    }

    /**
     * Render the care plan report view. Backs the global care_plan_report() function.
     */
    public function reportAction(int $pid, int $encounter, int $cols, ?int $id): Response
    {
        if (!$this->formService->hasFormPermission(self::FORM_DIR)) {
            return $this->denyAccess('report');
        }

        $pid = $pid !== 0 ? $pid : $this->sessionInt('pid');
        $encounter = $encounter !== 0 ? $encounter : $this->sessionInt('encounter');

        $rows = ($id === null || $id === 0) ? [] : $this->carePlanFormService->getCarePlanRows($id, $pid, $encounter);

        if ($rows === []) {
            return $this->createResponse('');
        }

        return $this->createResponse($this->twig->render($this->getTemplatePath('care_plan_report.html.twig'), [
            'rows' => $rows,
        ]));
    }

    /**
     * Whether a submitted form id may be used as the persistence identity for this save.
     *
     * Zero means "create a new form". Anything else has to be a care plan form already
     * registered against this patient and encounter; a foreign id would delete nothing
     * and then write rows under an unregistered form id.
     *
     * This is a membership test, not equality with "the" encounter's form id -- an
     * encounter can carry several care plan forms, so comparing against a single
     * `LIMIT 1` lookup would reject legitimate saves of any but the first.
     *
     * Public so it can be exercised directly -- saveAction() itself is unreachable in
     * tests because CsrfUtils::checkCsrfInput() reads INPUT_POST, which PHPUnit never
     * populates, and dies on failure.
     */
    public function isSubmittedFormIdValid(int $formId, int $pid, int $encounter): bool
    {
        if ($formId <= 0) {
            return true;
        }

        return $this->carePlanFormService->formBelongsToEncounter($formId, $pid, $encounter);
    }

    /**
     * @param array<string, mixed> $postData
     */
    private function persistRows(array $postData, int $formId, int $pid, int $encounter, int $userAuthorized): void
    {
        foreach ($this->mapPostToRows($postData, $pid, $encounter, $userAuthorized) as $row) {
            $this->carePlanFormService->insertCarePlanRow($formId, $row);
        }
    }

    /**
     * Map the submitted form into one row array per care plan entry.
     *
     * Separate from the insert loop so the mapping rules -- notably the per-row
     * reason-code failsafe -- can be exercised without a database.
     *
     * @param array<string, mixed> $postData
     *
     * @return list<array<string, mixed>>
     */
    public function mapPostToRows(array $postData, int $pid, int $encounter, int $userAuthorized): array
    {
        $counts = array_filter($this->asArray($postData['count'] ?? null));
        if ($counts === []) {
            return [];
        }

        $rows = [];
        $authUser = $this->sessionString('authUser');
        $groupName = $this->sessionString('authProvider');

        $codes = $this->asArray($postData['code'] ?? null);
        $codeTexts = $this->asArray($postData['codetext'] ?? null);
        $descriptions = $this->asArray($postData['description'] ?? null);
        $carePlanTypes = $this->asArray($postData['care_plan_type'] ?? null);
        $users = $this->asArray($postData['user'] ?? null);
        $startDates = $this->asArray($postData['code_date'] ?? null);
        $endDates = $this->asArray($postData['end_date'] ?? null);
        $proposedDates = $this->asArray($postData['proposed_date'] ?? null);
        $planStatuses = $this->asArray($postData['plan_status'] ?? null);
        $engagementCategories = $this->asArray($postData['plan_engagement_category'] ?? null);
        // The select is client-controlled, so the submitted value is checked against the
        // list rather than trusted. Fetched once per save, not once per row.
        $validEngagementCategories = $this->carePlanFormService->getEngagementCategoryOptionIds();
        $reasonCodes = $this->asArray($postData['reasonCode'] ?? null);
        $reasonStatuses = $this->asArray($postData['reasonCodeStatus'] ?? null);
        $reasonTexts = $this->asArray($postData['reasonCodeText'] ?? null);
        $reasonLows = $this->asArray($postData['reasonDateLow'] ?? null);
        $reasonHighs = $this->asArray($postData['reasonDateHigh'] ?? null);

        foreach (array_keys($counts) as $key) {
            $description = $this->stringAt($descriptions, $key);
            $user = $this->stringAt($users, $key) ?: $authUser;

            $reasonCode = trim($this->stringAt($reasonCodes, $key));
            $reasonStatus = trim($this->stringAt($reasonStatuses, $key));
            $reasonDescription = trim($this->stringAt($reasonTexts, $key));
            $reasonLow = trim($this->stringAt($reasonLows, $key));
            $reasonHigh = trim($this->stringAt($reasonHighs, $key));

            // Without a reason code the remaining reason fields have no meaning. Note this
            // is evaluated per row -- the pre-refactor code tested the whole POST array, so
            // the failsafe only fired when no row at all carried a reason code.
            if ($reasonCode === '') {
                $reasonStatus = '';
                $reasonDescription = '';
                $reasonLow = '';
                $reasonHigh = '';
            }

            $rows[] = [
                'pid' => $pid,
                'groupname' => $groupName,
                'user' => $user,
                'encounter' => $encounter,
                'authorized' => $userAuthorized,
                'code' => $this->stringAt($codes, $key),
                'codetext' => $this->stringAt($codeTexts, $key),
                'description' => $description,
                'date' => $this->carePlanFormService->normalizeNullableString($this->stringAt($startDates, $key)),
                'date_end' => $this->carePlanFormService->normalizeNullableString($this->stringAt($endDates, $key)),
                'proposed_date' => $this->carePlanFormService->normalizeNullableString($this->stringAt($proposedDates, $key)),
                'plan_status' => $this->carePlanFormService->normalizeNullableString($this->stringAt($planStatuses, $key)),
                'care_plan_type' => $this->stringAt($carePlanTypes, $key),
                'note_related_to' => $this->carePlanFormService->parseNote($description),
                'reason_code' => $reasonCode,
                'reason_status' => $reasonStatus,
                'reason_description' => $reasonDescription,
                'reason_date_low' => $reasonLow,
                'reason_date_high' => $reasonHigh,
                'plan_engagement_category' => $this->resolveEngagementCategory(
                    $this->stringAt($engagementCategories, $key),
                    $validEngagementCategories
                ),
            ];
        }

        return $rows;
    }

    /**
     * Narrow a submitted engagement category to a value the list actually defines.
     *
     * Anything else is discarded rather than persisted: an unknown option id has no
     * localized title, so it would render blank in the form and the report and export as
     * an unresolvable value in EHI.
     *
     * @param list<string> $validOptionIds
     */
    private function resolveEngagementCategory(string $submitted, array $validOptionIds): ?string
    {
        $value = $this->carePlanFormService->normalizeNullableString($submitted);

        if ($value === null || in_array($value, $validOptionIds, true)) {
            return $value;
        }

        // The rejected value is deliberately not logged. It is client-controlled free text
        // that could carry PHI, and application logs are not an appropriate place for it.
        $this->logger->warning('Discarded care plan engagement category: value is not a defined list option', [
            'listId' => CarePlanFormService::ENGAGEMENT_CATEGORY_LIST_ID,
        ]);

        return null;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function asArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function stringAt(array $values, int|string $key): string
    {
        $value = $values[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }

    private function sessionInt(string $key): int
    {
        $value = $this->session->get($key);

        return is_numeric($value) ? (int) $value : 0;
    }

    private function sessionString(string $key): string
    {
        $value = $this->session->get($key);

        return is_scalar($value) ? (string) $value : '';
    }

    private function denyAccess(string $operation): Response
    {
        $this->logger->warning('Care plan form access denied', [
            'operation' => $operation,
            'formDir' => self::FORM_DIR,
            'authUser' => $this->sessionString('authUser'),
        ]);

        return $this->createResponse(xlt("Unauthorized access"), Response::HTTP_UNAUTHORIZED);
    }

    private function getTemplatePath(string $templateName): string
    {
        return self::TEMPLATE_ROOT . $templateName;
    }

    private function createResponse(string $content, int $status = Response::HTTP_OK): Response
    {
        return new Response($content, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
