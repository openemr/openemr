<?php

namespace GeneralDermatology\Services;

use OpenEMR\Common\Database\QueryUtils;

/**
 * SOAP template load/save helpers and template manager operations.
 *
 * Templates are snapshot rows in general_dermatology with a non-empty
 * general_dermatology_form_template_name and pid = NULL.
 */
class TemplateFormService
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    private const NAME_MIN_LEN = 3;
    private const NAME_MAX_LEN = 255;

    /** @var list<string> */
    private const CONTENT_FLAG_FIELDS = [
        'skinlesion',
        'skincheck2',
        'rash2',
        'cosmetic2',
        'surgical2',
        'acne2',
        'hairloss2',
        'naildisorder2',
        'other2',
        'patient_type_new',
        'patient_type_established',
        'well_developed',
        'well_nourished',
        'obese',
        'anorexic',
        'scalp',
        'hair',
        'face',
        'externalears',
        'eyelids',
        'lips',
        'nose',
        'neck',
        'chest',
        'shoulders',
        'back',
        'arms',
        'forearms',
        'hands',
        'fingernails',
        'legs',
        'feet',
        'buttocks',
        'axillae',
        'toenails',
        'groin',
        'abdomen',
    ];

    /**
     * @param array<string, mixed> $row
     */
    public static function isTemplateRow(array $row): bool
    {
        return trim((string) ($row['general_dermatology_form_template_name'] ?? '')) !== '';
    }

    /**
     * @return array{mode: string, saveFormId: int}
     */
    public static function resolvePatientSaveTarget(int $encounterFormId): array
    {
        if ($encounterFormId > 0) {
            return [
                'mode' => 'update',
                'saveFormId' => $encounterFormId,
            ];
        }

        return [
            'mode' => 'new',
            'saveFormId' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array{
     *     formData: array<string, mixed>,
     *     loadedTemplateId: int,
     *     encounterFormId: int,
     *     mode: string,
     *     saveFormId: int
     * }
     */
    public static function prepareLoadedFormData(array $row, int $requestedId, int $encounterFormId): array
    {
        $loadedTemplateId = 0;
        $formData = $row;

        if (self::isTemplateRow($row)) {
            $loadedTemplateId = $requestedId;
            $formData['general_dermatology_form_template_name'] = '';
            $formData['id'] = $encounterFormId > 0 ? $encounterFormId : null;
        } else {
            $formData['id'] = $requestedId > 0 ? $requestedId : ($encounterFormId > 0 ? $encounterFormId : null);
            if ($encounterFormId === 0 && $requestedId > 0) {
                $encounterFormId = $requestedId;
            }
        }

        $target = self::resolvePatientSaveTarget($encounterFormId);

        return [
            'formData' => $formData,
            'loadedTemplateId' => $loadedTemplateId,
            'encounterFormId' => $encounterFormId,
            'mode' => $target['mode'],
            'saveFormId' => $target['saveFormId'],
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function prepareTemplateEditFormData(array $row, int $templateId): array
    {
        $formData = $row;
        $formData['id'] = $templateId;
        $formData['general_dermatology_form_template_name'] = trim(
            (string) ($row['general_dermatology_form_template_name'] ?? '')
        );

        return $formData;
    }

    public static function buildPatientSaveAction(
        string $mode,
        int $saveFormId,
        string $saveScriptPath = '../../forms/general_dermatology/save.php'
    ): string {
        if ($mode === 'update' && $saveFormId > 0) {
            return $saveScriptPath . '?mode=update&id=' . rawurlencode((string) $saveFormId);
        }

        return $saveScriptPath . '?mode=new';
    }

    public static function buildTemplateSaveAction(
        bool $isUpdate,
        int $templateId = 0,
        string $saveScriptPath = '../../forms/general_dermatology/save.php'
    ): string {
        if ($isUpdate && $templateId > 0) {
            return $saveScriptPath . '?mode=updateTemplate&id=' . rawurlencode((string) $templateId);
        }

        return $saveScriptPath . '?mode=saveTemplate';
    }

    public static function buildTemplateLoadUrl(string $webroot, int|string $templateId): string
    {
        $root = rtrim($webroot, '/');
        $path = '/interface/patient_file/encounter/load_form.php';
        $query = http_build_query([
            'formname' => 'general_dermatology',
            'id' => (string) $templateId,
        ]);

        return $root . $path . '?' . $query;
    }

    public static function buildTemplateManagerUrl(string $webroot): string
    {
        return rtrim($webroot, '/') . '/interface/forms/general_dermatology/templates.php';
    }

    public static function buildTemplateEditUrl(string $webroot, int $templateId = 0): string
    {
        $base = rtrim($webroot, '/') . '/interface/forms/general_dermatology/new.php';
        $params = ['template_mode' => '1'];
        if ($templateId > 0) {
            $params['id'] = (string) $templateId;
        }

        return $base . '?' . http_build_query($params);
    }

    public static function resolveUpdateFormId(int $requestFormId, int $encounterFormId): int
    {
        if ($requestFormId > 0) {
            return $requestFormId;
        }

        return $encounterFormId > 0 ? $encounterFormId : 0;
    }

    public static function shouldCreateInsteadOfUpdate(int $resolvedFormId): bool
    {
        return $resolvedFormId <= 0;
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    public static function sanitizePatientSavePayload(array $post): array
    {
        $post['general_dermatology_form_template_name'] = '';
        unset($post['template_description'], $post['template_status'], $post['template_mode']);

        return $post;
    }

    public static function normalizeTemplateName(?string $templateName): string
    {
        $name = trim((string) $templateName);
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return $name;
    }

    public static function isValidTemplateName(?string $templateName): bool
    {
        $name = self::normalizeTemplateName($templateName);
        if ($name === '') {
            return false;
        }

        $len = mb_strlen($name);
        if ($len < self::NAME_MIN_LEN || $len > self::NAME_MAX_LEN) {
            return false;
        }

        return (bool) preg_match('/^[\p{L}\p{N}\s\-\/\(\)\&\.]+$/u', $name);
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<string>
     */
    public static function validateTemplateContent(array $payload): array
    {
        if (self::hasMeaningfulContent($payload)) {
            return [];
        }

        return ['Add at least one clinical field before saving this template.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function hasMeaningfulContent(array $payload): bool
    {
        $textFields = [
            'subjective_text',
            'severity_text',
            'location_initially',
            'location_later',
            'location_custom',
            'sites_examined_text',
            'general_derm_patient_follow_up',
            'general_derm_note',
            'prior_tx1',
            'prior_1',
            'prior_tx2',
            'prior_2',
        ];

        foreach ($textFields as $field) {
            if (trim((string) ($payload[$field] ?? '')) !== '') {
                return true;
            }
        }

        foreach (self::CONTENT_FLAG_FIELDS as $field) {
            $value = $payload[$field] ?? 0;
            if ((string) $value === '1' || $value === 1 || $value === true) {
                return true;
            }
        }

        $treatment = $payload['treatment'] ?? null;
        if (is_string($treatment) && $treatment !== '') {
            $decoded = json_decode($treatment, true);
            if (is_array($decoded)) {
                $treatment = $decoded;
            }
        }

        if (is_array($treatment)) {
            $findings = $treatment;
            if (array_key_exists('findings', $treatment) && is_array($treatment['findings'])) {
                $findings = $treatment['findings'];
            }
            foreach ($findings as $item) {
                if (!is_array($item)) {
                    continue;
                }
                foreach (['finding_location', 'dx_s', 'dx_desc', 'plan', 'plan_desc', 'procedure_plans'] as $key) {
                    if (trim((string) ($item[$key] ?? '')) !== '') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{ok: bool, errors: list<string>, name: string, warnings: list<string>}
     */
    public static function validateTemplateSave(array $payload, ?int $excludeId = null): array
    {
        $errors = [];
        $warnings = [];
        $name = self::normalizeTemplateName($payload['general_dermatology_form_template_name'] ?? '');

        if ($name === '') {
            $errors[] = 'Template name is required.';
        } elseif (!self::isValidTemplateName($name)) {
            $errors[] = 'Template name must be 3–255 characters and may only contain letters, numbers, spaces, and - / ( ) & .';
        } elseif (self::templateNameExists($name, $excludeId)) {
            $errors[] = "A template named '{$name}' already exists.";
        }

        foreach (self::validateTemplateContent($payload) as $contentError) {
            $errors[] = $contentError;
        }

        foreach (self::detectPhiWarnings($payload) as $warning) {
            $warnings[] = $warning;
        }

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'name' => $name,
            'warnings' => $warnings,
        ];
    }

    /**
     * Name-only validation for rename (content already exists on the row).
     *
     * @return array{ok: bool, errors: list<string>, name: string}
     */
    public static function validateTemplateNameOnly(?string $templateName, ?int $excludeId = null): array
    {
        $errors = [];
        $name = self::normalizeTemplateName($templateName);

        if ($name === '') {
            $errors[] = 'Template name is required.';
        } elseif (!self::isValidTemplateName($name)) {
            $errors[] = 'Template name must be 3–255 characters and may only contain letters, numbers, spaces, and - / ( ) & .';
        } elseif (self::templateNameExists($name, $excludeId)) {
            $errors[] = "A template named '{$name}' already exists.";
        }

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'name' => $name,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<string>
     */
    public static function detectPhiWarnings(array $payload): array
    {
        $warnings = [];
        $haystack = implode("\n", [
            (string) ($payload['subjective_text'] ?? ''),
            (string) ($payload['general_derm_note'] ?? ''),
            (string) ($payload['general_derm_patient_follow_up'] ?? ''),
            (string) ($payload['sites_examined_text'] ?? ''),
        ]);

        if (preg_match('/\b\d{3}-\d{2}-\d{4}\b/', $haystack)) {
            $warnings[] = 'Possible SSN pattern detected. Remove patient identifiers before saving templates.';
        }
        if (preg_match('/\bMRN\s*[:#]?\s*\d+\b/i', $haystack)) {
            $warnings[] = 'Possible MRN detected. Remove patient identifiers before saving templates.';
        }

        return $warnings;
    }

    public static function templateNameExists(string $name, ?int $excludeId = null): bool
    {
        $name = self::normalizeTemplateName($name);
        if ($name === '') {
            return false;
        }

        $sql = "SELECT id FROM general_dermatology
                WHERE IFNULL(general_dermatology_form_template_name, '') <> ''
                  AND LOWER(TRIM(general_dermatology_form_template_name)) = LOWER(?)";
        $params = [$name];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }

        $sql .= ' LIMIT 1';
        $row = QueryUtils::querySingleRow($sql, $params) ?: [];

        return !empty($row['id']);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function fetchActiveTemplatesForDropdown(): array
    {
        return QueryUtils::fetchRecords(
            "SELECT id, general_dermatology_form_template_name AS name
             FROM general_dermatology
             WHERE IFNULL(general_dermatology_form_template_name, '') <> ''
               AND IFNULL(template_status, 'active') = 'active'
             ORDER BY general_dermatology_form_template_name ASC",
            []
        ) ?: [];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function listTemplates(
        string $search = '',
        string $status = 'all',
        string $sort = 'name',
        string $dir = 'asc',
        int $page = 1,
        int $perPage = 25
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ["IFNULL(general_dermatology_form_template_name, '') <> ''"];
        $params = [];

        $search = trim($search);
        if ($search !== '') {
            $where[] = 'general_dermatology_form_template_name LIKE ?';
            $params[] = '%' . $search . '%';
        }

        $status = strtolower(trim($status));
        if ($status === self::STATUS_ACTIVE || $status === self::STATUS_INACTIVE) {
            $where[] = "IFNULL(template_status, 'active') = ?";
            $params[] = $status;
        }

        $whereSql = implode(' AND ', $where);

        $sortMap = [
            'name' => 'general_dermatology_form_template_name',
            'updated' => 'COALESCE(template_updated_at, date)',
            'owner' => 'COALESCE(template_created_by, user)',
            'status' => "IFNULL(template_status, 'active')",
        ];
        $sortCol = $sortMap[$sort] ?? $sortMap['name'];
        $dirSql = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';

        $countRow = QueryUtils::querySingleRow(
            "SELECT COUNT(*) AS cnt FROM general_dermatology WHERE {$whereSql}",
            $params
        ) ?: ['cnt' => 0];
        $total = (int) ($countRow['cnt'] ?? 0);

        $rows = QueryUtils::fetchRecords(
            "SELECT id,
                    general_dermatology_form_template_name AS name,
                    IFNULL(template_status, 'active') AS status,
                    template_description,
                    COALESCE(template_created_by, user) AS owner,
                    COALESCE(template_updated_at, date) AS updated_at,
                    date AS created_at
             FROM general_dermatology
             WHERE {$whereSql}
             ORDER BY {$sortCol} {$dirSql}
             LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        ) ?: [];

        $totalPages = max(1, (int) ceil(max(1, $total) / $perPage));
        if ($total === 0) {
            $totalPages = 1;
        }

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getTemplateById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT * FROM general_dermatology WHERE id = ?',
            [$id]
        ) ?: [];

        if ($row === [] || !self::isTemplateRow($row)) {
            return null;
        }

        return $row;
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    public static function prepareTemplateWritePayload(array $post, string $normalizedName, ?string $authUser = null): array
    {
        unset($post['claim'], $post['billing_code'], $post['mode'], $post['template_mode'], $post['csrf_token_form']);

        $post['general_dermatology_form_template_name'] = $normalizedName;
        $post['template_status'] = self::STATUS_ACTIVE;
        if (array_key_exists('template_description', $post)) {
            $post['template_description'] = mb_substr(trim((string) $post['template_description']), 0, 500);
        }
        $post['template_updated_at'] = date('Y-m-d H:i:s');
        if ($authUser !== null && $authUser !== '') {
            $post['template_created_by'] = $authUser;
        }
        $post['encounter'] = null;
        $post['total_time_spent'] = null;
        $post['total_spent'] = null;

        return $post;
    }

    public static function markTemplateMetadata(int $id, ?string $authUser = null): void
    {
        if ($id <= 0) {
            return;
        }

        if ($authUser !== null && $authUser !== '') {
            QueryUtils::sqlStatementThrowException(
                "UPDATE general_dermatology
                 SET pid = NULL,
                     template_updated_at = ?,
                     template_status = IFNULL(template_status, 'active'),
                     template_created_by = COALESCE(template_created_by, ?)
                 WHERE id = ?",
                [date('Y-m-d H:i:s'), $authUser, $id]
            );
            return;
        }

        QueryUtils::sqlStatementThrowException(
            "UPDATE general_dermatology
             SET pid = NULL,
                 template_updated_at = ?,
                 template_status = IFNULL(template_status, 'active')
             WHERE id = ?",
            [date('Y-m-d H:i:s'), $id]
        );
    }

    /**
     * @return array{ok: bool, errors: list<string>, name?: string}
     */
    public static function renameTemplate(int $id, string $newName): array
    {
        $template = self::getTemplateById($id);
        if ($template === null) {
            return ['ok' => false, 'errors' => ['Template not found.']];
        }

        $validation = self::validateTemplateNameOnly($newName, $id);
        if (!$validation['ok']) {
            return $validation;
        }

        QueryUtils::sqlStatementThrowException(
            'UPDATE general_dermatology
             SET general_dermatology_form_template_name = ?,
                 template_updated_at = ?,
                 pid = NULL
             WHERE id = ?',
            [$validation['name'], date('Y-m-d H:i:s'), $id]
        );

        return ['ok' => true, 'errors' => [], 'name' => $validation['name']];
    }

    /**
     * @return array{ok: bool, errors: list<string>, status?: string}
     */
    public static function setTemplateStatus(int $id, string $status): array
    {
        $template = self::getTemplateById($id);
        if ($template === null) {
            return ['ok' => false, 'errors' => ['Template not found.']];
        }

        $status = strtolower(trim($status));
        if ($status !== self::STATUS_ACTIVE && $status !== self::STATUS_INACTIVE) {
            return ['ok' => false, 'errors' => ['Invalid template status.']];
        }

        QueryUtils::sqlStatementThrowException(
            'UPDATE general_dermatology
             SET template_status = ?, template_updated_at = ?, pid = NULL
             WHERE id = ?',
            [$status, date('Y-m-d H:i:s'), $id]
        );

        return ['ok' => true, 'errors' => [], 'status' => $status];
    }

    /**
     * @return array{ok: bool, errors: list<string>}
     */
    public static function deleteTemplate(int $id): array
    {
        $template = self::getTemplateById($id);
        if ($template === null) {
            return ['ok' => false, 'errors' => ['Template not found.']];
        }

        $status = (string) ($template['template_status'] ?? self::STATUS_ACTIVE);
        if ($status !== self::STATUS_INACTIVE) {
            return ['ok' => false, 'errors' => ['Deactivate the template before deleting it.']];
        }

        QueryUtils::sqlStatementThrowException(
            "DELETE FROM general_dermatology WHERE id = ? AND IFNULL(general_dermatology_form_template_name, '') <> ''",
            [$id]
        );

        return ['ok' => true, 'errors' => []];
    }

    /**
     * @return array{ok: bool, errors: list<string>, id?: int, name?: string}
     */
    public static function duplicateTemplate(int $id, string $newName, ?string $authUser = null): array
    {
        $template = self::getTemplateById($id);
        if ($template === null) {
            return ['ok' => false, 'errors' => ['Template not found.']];
        }

        $probe = $template;
        $probe['general_dermatology_form_template_name'] = self::normalizeTemplateName($newName);
        $validation = self::validateTemplateSave($probe, null);
        if (!$validation['ok']) {
            return ['ok' => false, 'errors' => $validation['errors']];
        }

        $name = $validation['name'];
        $columns = QueryUtils::fetchRecords('SHOW COLUMNS FROM general_dermatology', []) ?: [];
        $colNames = [];
        foreach ($columns as $col) {
            $field = (string) ($col['Field'] ?? '');
            if ($field !== '' && $field !== 'id') {
                $colNames[] = $field;
            }
        }

        if ($colNames === []) {
            return ['ok' => false, 'errors' => ['Unable to read template table schema.']];
        }

        $selectParts = [];
        $params = [];
        foreach ($colNames as $col) {
            if ($col === 'general_dermatology_form_template_name') {
                $selectParts[] = '?';
                $params[] = $name;
            } elseif ($col === 'pid') {
                $selectParts[] = 'NULL';
            } elseif ($col === 'template_status') {
                $selectParts[] = '?';
                $params[] = self::STATUS_ACTIVE;
            } elseif ($col === 'template_updated_at') {
                $selectParts[] = '?';
                $params[] = date('Y-m-d H:i:s');
            } elseif ($col === 'template_created_by') {
                $selectParts[] = '?';
                $params[] = $authUser ?: (string) ($template['template_created_by'] ?? $template['user'] ?? '');
            } elseif ($col === 'date') {
                $selectParts[] = 'NOW()';
            } elseif ($col === 'encounter') {
                $selectParts[] = 'NULL';
            } else {
                $selectParts[] = '`' . str_replace('`', '``', $col) . '`';
            }
        }

        $params[] = $id;
        $colList = '`' . implode('`,`', array_map(
            static fn(string $c): string => str_replace('`', '``', $c),
            $colNames
        )) . '`';
        $sql = 'INSERT INTO general_dermatology (' . $colList . ')
                SELECT ' . implode(', ', $selectParts) . '
                FROM general_dermatology WHERE id = ?';

        QueryUtils::sqlStatementThrowException($sql, $params);
        $newIdRow = QueryUtils::querySingleRow('SELECT LAST_INSERT_ID() AS id', []) ?: [];
        $newId = (int) ($newIdRow['id'] ?? 0);

        return [
            'ok' => $newId > 0,
            'errors' => $newId > 0 ? [] : ['Duplicate failed.'],
            'id' => $newId,
            'name' => $name,
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array{subjective: string, plans: int, follow_up: string, note: string}
     */
    public static function buildPreviewSummary(array $row): array
    {
        $treatments = [];
        if (!empty($row['treatment'])) {
            $decoded = json_decode((string) $row['treatment'], true);
            if (is_array($decoded)) {
                $treatments = $decoded;
            }
        }

        return [
            'subjective' => mb_substr(trim((string) ($row['subjective_text'] ?? '')), 0, 240),
            'plans' => count($treatments),
            'follow_up' => mb_substr(trim((string) ($row['general_derm_patient_follow_up'] ?? '')), 0, 240),
            'note' => mb_substr(trim((string) ($row['general_derm_note'] ?? '')), 0, 240),
        ];
    }
}
