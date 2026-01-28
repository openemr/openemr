<?php

/**
 * Dashboard Context Service
 *
 * Manages care context configurations for the patient dashboard.
 * Allows users to control which widgets are visible based on care contexts
 * such as Primary Care, Outpatient, Inpatient, Emergency, Specialty, etc.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\DashboardContext\Services;

use OpenEMR\Common\Database\QueryUtils;

class DashboardContextService
{
    /**
     * Default care contexts available in the system
     */
    public const CONTEXT_PRIMARY_CARE = 'primary_care';
    public const CONTEXT_OUTPATIENT = 'outpatient';
    public const CONTEXT_INPATIENT = 'inpatient';
    public const CONTEXT_EMERGENCY = 'emergency';
    public const CONTEXT_SPECIALTY = 'specialty';
    public const CONTEXT_TELEHEALTH = 'telehealth';
    public const CONTEXT_BEHAVIORAL_HEALTH = 'behavioral_health';
    public const CONTEXT_PEDIATRIC = 'pediatric';
    public const CONTEXT_GERIATRIC = 'geriatric';
    public const CONTEXT_CUSTOM = 'custom';

    /**
     * Widget identifiers that can be controlled by context
     *
     * These map to actual element IDs in demographics.php
     * Some use _ps_expand suffix, others use card_ prefix
     * The PortalCard and other secondary cards use getIdentifier()_expand
     *
     * IMPORTANT: To find the exact ID, inspect the HTML element in browser DevTools
     */
    private const MANAGEABLE_WIDGETS = [
        // Top row cards (issues) - these use _ps_expand pattern
        'allergy_ps_expand' => 'Allergies',
        'medical_problem_ps_expand' => 'Medical Problems',
        'medication_ps_expand' => 'Medications',
        'prescriptions_ps_expand' => 'Prescriptions',
        'careteam_ps_expand' => 'Care Team',
        'treatmentpref_ps_expand' => 'Treatment Intervention Preferences',
        'carepref_ps_expand' => 'Care Experience Preferences',
        'demographics_ps_expand' => 'Demographics',
        'billing_ps_expand' => 'Billing',
        'insurance_ps_expand' => 'Insurance',
        'pnotes_ps_expand' => 'Patient Notes/Messages',
        'patient_reminders_ps_expand' => 'Patient Reminders',
        'clinical_reminders_ps_expand' => 'Clinical Reminders',
        'disclosures_ps_expand' => 'Disclosures',
        'amendments_ps_expand' => 'Amendments',
        'labdata_ps_expand' => 'Lab Results',
        'vitals_ps_expand' => 'Vitals',
        'photos_ps_expand' => 'ID Card / Photos',
        'adv_directives_ps_expand' => 'Advance Directives',
        'appointments_ps_expand' => 'Appointments',
        'recall_ps_expand' => 'Recalls',
        'track_anything_ps_expand' => 'Tracks',
        'patient_portal_expand' => 'Patient Portal / API Access',
        'health_concern_ps_expand' => 'Health Concerns',
        'medical_device_ps_expand' => 'Medical Devices',
        'immunizations_ps_expand' => 'Immunizations',
    ];

    /**
     * Mapping from widget IDs to hiddenCards values used in demographics.php
     */
    private const WIDGET_TO_HIDDEN_CARD_MAP = [
        'allergy_ps_expand' => 'card_allergies',
        'medical_problem_ps_expand' => 'card_medicalproblems',
        'medication_ps_expand' => 'card_medication',
        'prescriptions_ps_expand' => 'card_prescriptions',
        'careteam_ps_expand' => 'card_care_team',
        'treatmentpref_ps_expand' => 'treatmentpref_ps_expand',
        'carepref_ps_expand' => 'carepref_ps_expand',
        'insurance_ps_expand' => 'card_insurance',
        'patient_reminders_ps_expand' => 'card_patientreminders',
        'disclosures_ps_expand' => 'card_disclosure',
        'amendments_ps_expand' => 'card_amendments',
        'labdata_ps_expand' => 'card_lab',
        'vitals_ps_expand' => 'card_vitals',
        'track_anything_ps_expand' => 'card_tracks',
        'patient_portal_expand' => 'card_portal',
        'adv_directives_ps_expand' => 'card_advance directives',
        'recall_ps_expand' => 'card_recalls',
        'appointments_ps_expand' => 'card_appointments',
        'health_concern_ps_expand' => 'card_health_concern',
        'medical_device_ps_expand' => 'card_medical_device',
        'immunizations_ps_expand' => 'card_immunizations',
    ];

    /**
     * Default widget configurations for each context
     * true = visible, false/not present = hidden
     */
    private const DEFAULT_CONTEXT_WIDGETS = [
        self::CONTEXT_PRIMARY_CARE => [
            // Show everything for primary care
            'allergy_ps_expand' => true,
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'prescriptions_ps_expand' => true,
            'careteam_ps_expand' => true,
            'treatmentpref_ps_expand' => true,
            'carepref_ps_expand' => true,
            'demographics_ps_expand' => true,
            'billing_ps_expand' => true,
            'insurance_ps_expand' => true,
            'pnotes_ps_expand' => true,
            'patient_reminders_ps_expand' => true,
            'clinical_reminders_ps_expand' => true,
            'disclosures_ps_expand' => true,
            'amendments_ps_expand' => true,
            'labdata_ps_expand' => true,
            'vitals_ps_expand' => true,
            'photos_ps_expand' => true,
            'adv_directives_ps_expand' => true,
            'appointments_ps_expand' => true,
            'recall_ps_expand' => true,
            'track_anything_ps_expand' => true,
            'patient_portal_expand' => true,
            'health_concern_ps_expand' => true,
            'immunizations_ps_expand' => true,
        ],
        self::CONTEXT_OUTPATIENT => [
            'allergy_ps_expand' => true,
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'prescriptions_ps_expand' => true,
            'careteam_ps_expand' => true,
            'demographics_ps_expand' => true,
            'billing_ps_expand' => true,
            'insurance_ps_expand' => true,
            'appointments_ps_expand' => true,
            'vitals_ps_expand' => true,
            'labdata_ps_expand' => true,
            'clinical_reminders_ps_expand' => true,
            'immunizations_ps_expand' => true,
        ],
        self::CONTEXT_INPATIENT => [
            'allergy_ps_expand' => true,
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'careteam_ps_expand' => true,
            'demographics_ps_expand' => true,
            'insurance_ps_expand' => true,
            'vitals_ps_expand' => true,
            'labdata_ps_expand' => true,
            'pnotes_ps_expand' => true,
            'adv_directives_ps_expand' => true,
        ],
        self::CONTEXT_EMERGENCY => [
            // Minimal critical info for emergency
            'allergy_ps_expand' => true,
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'demographics_ps_expand' => true,
            'insurance_ps_expand' => true,
            'vitals_ps_expand' => true,
            'adv_directives_ps_expand' => true,
        ],
        self::CONTEXT_SPECIALTY => [
            'allergy_ps_expand' => true,
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'prescriptions_ps_expand' => true,
            'careteam_ps_expand' => true,
            'demographics_ps_expand' => true,
            'billing_ps_expand' => true,
            'insurance_ps_expand' => true,
            'appointments_ps_expand' => true,
            'labdata_ps_expand' => true,
            'pnotes_ps_expand' => true,
        ],
        self::CONTEXT_TELEHEALTH => [
            'allergy_ps_expand' => true,
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'demographics_ps_expand' => true,
            'vitals_ps_expand' => true,
            'appointments_ps_expand' => true,
            'patient_portal_expand' => true,
            'photos_ps_expand' => true,
        ],
        self::CONTEXT_BEHAVIORAL_HEALTH => [
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'prescriptions_ps_expand' => true,
            'careteam_ps_expand' => true,
            'demographics_ps_expand' => true,
            'insurance_ps_expand' => true,
            'appointments_ps_expand' => true,
            'pnotes_ps_expand' => true,
            'treatmentpref_ps_expand' => true,
            'carepref_ps_expand' => true,
            'health_concern_ps_expand' => true,
        ],
        self::CONTEXT_PEDIATRIC => [
            'allergy_ps_expand' => true,
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'careteam_ps_expand' => true,
            'demographics_ps_expand' => true,
            'insurance_ps_expand' => true,
            'vitals_ps_expand' => true,
            'appointments_ps_expand' => true,
            'patient_reminders_ps_expand' => true,
            'patient_portal_expand' => true,
            'immunizations_ps_expand' => true,
        ],
        self::CONTEXT_GERIATRIC => [
            'allergy_ps_expand' => true,
            'medical_problem_ps_expand' => true,
            'medication_ps_expand' => true,
            'prescriptions_ps_expand' => true,
            'careteam_ps_expand' => true,
            'treatmentpref_ps_expand' => true,
            'carepref_ps_expand' => true,
            'demographics_ps_expand' => true,
            'billing_ps_expand' => true,
            'insurance_ps_expand' => true,
            'vitals_ps_expand' => true,
            'appointments_ps_expand' => true,
            'recall_ps_expand' => true,
            'adv_directives_ps_expand' => true,
            'health_concern_ps_expand' => true,
        ],
    ];

    // Table for tracking user's active context only
    private string $tableName = 'user_dashboard_context';
    // Table for storing widget configs per user per context
    private string $configTableName = 'user_dashboard_context_config';
    // Table for custom context definitions
    private string $contextTableName = 'dashboard_context_definitions';
    // Table for admin assignments
    private string $assignmentTableName = 'dashboard_context_assignments';
    // Table for widget display order per context
    private string $widgetOrderTable = 'dashboard_widget_order';
    // Table for custom widget labels per context
    private string $widgetLabelsTable = 'dashboard_widget_labels';

    /**
     * Get the current active context for a user
     */
    public function getActiveContext(int $userId): string
    {
        $sql = "SELECT active_context FROM {$this->tableName} WHERE user_id = ? LIMIT 1";
        $result = QueryUtils::querySingleRow($sql, [$userId]);

        return $result['active_context'] ?? self::CONTEXT_PRIMARY_CARE;
    }

    /**
     * Set the active context for a user
     * NOTE: This only changes which context is active, NOT the widget config
     */
    public function setActiveContext(int $userId, string $context): bool
    {
        if ($this->isUserContextLocked($userId)) {
            return false;
        }

        $existing = QueryUtils::querySingleRow(
            "SELECT id FROM {$this->tableName} WHERE user_id = ?",
            [$userId]
        );

        if ($existing) {
            return QueryUtils::sqlStatementThrowException(
                "UPDATE {$this->tableName} SET active_context = ?, updated_at = NOW() WHERE user_id = ?",
                [$context, $userId]
            ) !== false;
        }

        return QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$this->tableName} (user_id, active_context, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
            [$userId, $context]
        ) !== false;
    }

    /**
     * Get widget visibility configuration for a specific context
     *
     * Order of precedence:
     * 1. User's custom config for this specific context (user_dashboard_context_config)
     * 2. Custom context definition (dashboard_context_definitions)
     * 3. System defaults (DEFAULT_CONTEXT_WIDGETS)
     */
    public function getContextWidgets(int $userId, ?string $context = null): array
    {
        if ($context === null) {
            $context = $this->getActiveContext($userId);
        }

        // First check for user-customized settings for THIS SPECIFIC context
        // This is stored in a separate table keyed by user_id AND context_key
        $sql = "SELECT widget_config FROM {$this->configTableName} WHERE user_id = ? AND context_key = ?";
        $result = QueryUtils::querySingleRow($sql, [$userId, $context]);

        if (!empty($result['widget_config'])) {
            $config = json_decode((string) $result['widget_config'], true);
            if (is_array($config)) {
                return $config;
            }
        }

        // Check for custom context definition (for user-created or global custom contexts)
        $customContext = QueryUtils::querySingleRow(
            "SELECT widget_config FROM {$this->contextTableName} WHERE context_key = ? AND (user_id = ? OR is_global = 1)",
            [$context, $userId]
        );

        if (!empty($customContext['widget_config'])) {
            $config = json_decode((string) $customContext['widget_config'], true);
            if (is_array($config)) {
                return $config;
            }
        }

        // Fall back to system defaults
        return self::DEFAULT_CONTEXT_WIDGETS[$context] ?? self::DEFAULT_CONTEXT_WIDGETS[self::CONTEXT_PRIMARY_CARE];
    }

    /**
     * Save widget configuration for a SPECIFIC context for a user
     * This saves to user_dashboard_context_config table, keyed by user_id + context_key
     */
    public function saveContextWidgets(int $userId, string $context, array $widgetConfig): bool
    {
        $jsonConfig = json_encode($widgetConfig);

        // Check if user already has a config for this specific context
        $existing = QueryUtils::querySingleRow(
            "SELECT id FROM {$this->configTableName} WHERE user_id = ? AND context_key = ?",
            [$userId, $context]
        );

        if ($existing) {
            // Update existing config for this context
            return QueryUtils::sqlStatementThrowException(
                "UPDATE {$this->configTableName} SET widget_config = ?, updated_at = NOW() WHERE user_id = ? AND context_key = ?",
                [$jsonConfig, $userId, $context]
            ) !== false;
        }

        // Insert new config for this context
        return QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$this->configTableName} (user_id, context_key, widget_config, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
            [$userId, $context, $jsonConfig]
        ) !== false;
    }

    /**
     * Check if a specific widget should be visible in the current context
     */
    public function isWidgetVisible(int $userId, string $widgetId): bool
    {
        $widgets = $this->getContextWidgets($userId);
        return $widgets[$widgetId] ?? false;
    }

    /**
     * Get all available system contexts
     */
    public function getAvailableContexts(): array
    {
        return [
            self::CONTEXT_PRIMARY_CARE => xl('Primary Care'),
            self::CONTEXT_OUTPATIENT => xl('Outpatient'),
            self::CONTEXT_INPATIENT => xl('Inpatient'),
            self::CONTEXT_EMERGENCY => xl('Emergency'),
            self::CONTEXT_SPECIALTY => xl('Specialty'),
            self::CONTEXT_TELEHEALTH => xl('Telehealth'),
            self::CONTEXT_BEHAVIORAL_HEALTH => xl('Behavioral Health'),
            self::CONTEXT_PEDIATRIC => xl('Pediatric'),
            self::CONTEXT_GERIATRIC => xl('Geriatric'),
        ];
    }

    /**
     * Get all manageable widgets with translated labels
     */
    public function getManageableWidgets(): array
    {
        $widgets = [];
        foreach (self::MANAGEABLE_WIDGETS as $id => $label) {
            $widgets[$id] = xl($label);
        }
        return $widgets;
    }

    /**
     * Get default widgets for a context
     */
    public function getDefaultContextWidgets(string $context): array
    {
        return self::DEFAULT_CONTEXT_WIDGETS[$context] ?? self::DEFAULT_CONTEXT_WIDGETS[self::CONTEXT_PRIMARY_CARE];
    }

    /**
     * Reset user's widget config for a specific context to system defaults
     */
    public function resetToDefaults(int $userId, string $context): bool
    {
        // Delete the user's custom config for this specific context
        return QueryUtils::sqlStatementThrowException(
            "DELETE FROM {$this->configTableName} WHERE user_id = ? AND context_key = ?",
            [$userId, $context]
        ) !== false;
    }

    /**
     * Get user's custom contexts
     */
    public function getUserCustomContexts(int $userId): array
    {
        $sql = "SELECT * FROM {$this->contextTableName} WHERE user_id = ? OR is_global = 1 ORDER BY context_name";
        $results = QueryUtils::sqlStatementThrowException($sql, [$userId]);

        $contexts = [];
        while ($row = QueryUtils::fetchArrayFromResultSet($results)) {
            $contexts[] = [
                'id' => $row['id'],
                'context_key' => $row['context_key'],
                'context_name' => $row['context_name'],
                'description' => $row['description'],
                'is_global' => (bool)$row['is_global'],
                'widget_config' => json_decode($row['widget_config'] ?? '{}', true),
            ];
        }

        return $contexts;
    }

    /**
     * Create a custom context
     */
    public function createCustomContext(
        int $userId,
        string $contextName,
        string $description,
        array $widgetConfig,
        bool $isGlobal = false
    ): int|false {
        $contextKey = 'custom_' . strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '_', $contextName)) . '_' . time();

        $result = QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$this->contextTableName} (user_id, context_key, context_name, description, widget_config, is_global, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [$userId, $contextKey, $contextName, $description, json_encode($widgetConfig), $isGlobal ? 1 : 0]
        );

        if ($result !== false) {
            return QueryUtils::getLastInsertId();
        }

        return false;
    }

    /**
     * Delete a custom context
     */
    public function deleteCustomContext(int $contextId, int $userId): bool
    {
        return QueryUtils::sqlStatementThrowException(
            "DELETE FROM {$this->contextTableName} WHERE id = ? AND user_id = ? AND is_global = 0",
            [$contextId, $userId]
        ) !== false;
    }

    /**
     * Check if user's context is locked by admin
     */
    public function isUserContextLocked(int $userId): bool
    {
        $result = QueryUtils::querySingleRow(
            "SELECT is_locked FROM {$this->assignmentTableName} WHERE user_id = ? AND is_active = 1",
            [$userId]
        );

        return !empty($result['is_locked']);
    }

    /**
     * Get the hidden cards array for use with demographics.php hiddenCards integration
     */
    public function getHiddenCardsForContext(int $userId): array
    {
        $widgets = $this->getContextWidgets($userId);
        $hidden = [];

        foreach (self::WIDGET_TO_HIDDEN_CARD_MAP as $widgetId => $cardName) {
            if (!isset($widgets[$widgetId]) || $widgets[$widgetId] === false) {
                $hidden[] = $cardName;
            }
        }

        return $hidden;
    }

    /**
     * Get all widgets with their visibility state for a given context
     * Returns complete widget list with true/false for each
     */
    public function getFullWidgetConfig(int $userId, ?string $context = null): array
    {
        $activeWidgets = $this->getContextWidgets($userId, $context);
        $allWidgets = [];

        foreach (self::MANAGEABLE_WIDGETS as $widgetId => $label) {
            $allWidgets[$widgetId] = isset($activeWidgets[$widgetId]) && $activeWidgets[$widgetId] === true;
        }

        return $allWidgets;
    }

    /**
     * Get the widget to card mapping for external use
     */
    public function getWidgetToCardMap(): array
    {
        return self::WIDGET_TO_HIDDEN_CARD_MAP;
    }

    /**
     * Get widget display order for a context.
     *
     * Precedence: user override > context default > MANAGEABLE_WIDGETS key order
     */
    public function getWidgetOrder(int $userId, ?string $context = null): array
    {
        if ($context === null) {
            $context = $this->getActiveContext($userId);
        }

        // Check for user-specific override
        $result = QueryUtils::querySingleRow(
            "SELECT widget_order FROM {$this->widgetOrderTable} WHERE context_key = ? AND user_id = ?",
            [$context, $userId]
        );

        if (!empty($result['widget_order'])) {
            $order = json_decode((string) $result['widget_order'], true);
            if (is_array($order)) {
                return $order;
            }
        }

        // Check for context-level default (user_id IS NULL)
        $result = QueryUtils::querySingleRow(
            "SELECT widget_order FROM {$this->widgetOrderTable} WHERE context_key = ? AND user_id IS NULL",
            [$context]
        );

        if (!empty($result['widget_order'])) {
            $order = json_decode((string) $result['widget_order'], true);
            if (is_array($order)) {
                return $order;
            }
        }

        // Fall back to MANAGEABLE_WIDGETS key order
        return array_keys(self::MANAGEABLE_WIDGETS);
    }

    /**
     * Save widget display order for a context
     */
    public function saveWidgetOrder(string $context, array $order, ?int $userId = null): bool
    {
        $jsonOrder = json_encode(array_values($order));

        if ($userId !== null) {
            $existing = QueryUtils::querySingleRow(
                "SELECT id FROM {$this->widgetOrderTable} WHERE context_key = ? AND user_id = ?",
                [$context, $userId]
            );
        } else {
            $existing = QueryUtils::querySingleRow(
                "SELECT id FROM {$this->widgetOrderTable} WHERE context_key = ? AND user_id IS NULL",
                [$context]
            );
        }

        if ($existing) {
            return QueryUtils::sqlStatementThrowException(
                "UPDATE {$this->widgetOrderTable} SET widget_order = ?, updated_at = NOW() WHERE id = ?",
                [$jsonOrder, $existing['id']]
            ) !== false;
        }

        return QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$this->widgetOrderTable} (context_key, user_id, widget_order, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
            [$context, $userId, $jsonOrder]
        ) !== false;
    }

    /**
     * Get custom widget labels for a context
     *
     * @return array [widget_id => custom_label]
     */
    public function getWidgetLabels(string $context): array
    {
        $sql = "SELECT widget_id, custom_label FROM {$this->widgetLabelsTable} WHERE context_key = ?";
        $result = QueryUtils::sqlStatementThrowException($sql, [$context]);
        $labels = [];

        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $labels[$row['widget_id']] = $row['custom_label'];
        }

        return $labels;
    }

    /**
     * Save or update a custom widget label for a context
     */
    public function saveWidgetLabel(string $context, string $widgetId, string $label): bool
    {
        $existing = QueryUtils::querySingleRow(
            "SELECT id FROM {$this->widgetLabelsTable} WHERE context_key = ? AND widget_id = ?",
            [$context, $widgetId]
        );

        if ($existing) {
            return QueryUtils::sqlStatementThrowException(
                "UPDATE {$this->widgetLabelsTable} SET custom_label = ?, updated_at = NOW() WHERE id = ?",
                [$label, $existing['id']]
            ) !== false;
        }

        return QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$this->widgetLabelsTable} (context_key, widget_id, custom_label, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
            [$context, $widgetId, $label]
        ) !== false;
    }

    /**
     * Delete a custom widget label for a context
     */
    public function deleteWidgetLabel(string $context, string $widgetId): bool
    {
        return QueryUtils::sqlStatementThrowException(
            "DELETE FROM {$this->widgetLabelsTable} WHERE context_key = ? AND widget_id = ?",
            [$context, $widgetId]
        ) !== false;
    }

    /**
     * Get all manageable widgets with context-specific label overrides applied
     */
    public function getManageableWidgetsWithLabels(string $context): array
    {
        $widgets = $this->getManageableWidgets();
        $labels = $this->getWidgetLabels($context);

        foreach ($labels as $widgetId => $customLabel) {
            if (isset($widgets[$widgetId])) {
                $widgets[$widgetId] = $customLabel;
            }
        }

        return $widgets;
    }
}
