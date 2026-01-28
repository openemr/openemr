<?php

/**
 * Dashboard Context Admin Service
 *
 * Administrative service for managing dashboard contexts, user assignments,
 * role-based defaults, and facility-specific configurations.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\DashboardContext\Services;

use OpenEMR\Common\Database\QueryUtils;

class DashboardContextAdminService
{
    private string $contextTable = 'dashboard_context_definitions';
    private string $userContextTable = 'user_dashboard_context';
    private string $userContextConfigTable = 'user_dashboard_context_config';
    private string $assignmentTable = 'dashboard_context_assignments';
    private string $roleDefaultsTable = 'dashboard_context_role_defaults';
    private string $facilityDefaultsTable = 'dashboard_context_facility_defaults';
    private string $auditLogTable = 'dashboard_context_audit_log';
    private string $widgetOrderTable = 'dashboard_widget_order';
    private string $widgetLabelsTable = 'dashboard_widget_labels';

    /**
     * Get all context definitions (system + custom)
     *
     * @param bool $includeSystem Include built-in system contexts
     * @return array
     */
    public function getAllContexts(bool $includeSystem = true): array
    {
        $contexts = [];
        $contextService = new DashboardContextService();

        if ($includeSystem) {
            $systemContexts = $contextService->getAvailableContexts();
            foreach ($systemContexts as $key => $label) {
                $contexts[] = [
                    'id' => null,
                    'context_key' => $key,
                    'context_name' => $label,
                    'description' => '',
                    'is_system' => true,
                    'is_global' => true,
                    'created_by' => null,
                    'widget_config' => json_encode($contextService->getDefaultContextWidgets($key)),
                ];
            }
        }

        $sql = "SELECT cd.*, u.username as created_by_username
                FROM {$this->contextTable} cd
                LEFT JOIN users u ON cd.user_id = u.id
                ORDER BY cd.sort_order, cd.context_name";
        $result = QueryUtils::sqlStatementThrowException($sql);

        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $contexts[] = [
                'id' => $row['id'],
                'context_key' => $row['context_key'],
                'context_name' => $row['context_name'],
                'description' => $row['description'],
                'is_system' => false,
                'is_global' => (bool)$row['is_global'],
                'created_by' => $row['created_by_username'],
                'user_id' => $row['user_id'],
                'widget_config' => $row['widget_config'],
                'sort_order' => $row['sort_order'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];
        }

        return $contexts;
    }

    /**
     * Create a new global context definition
     */
    public function createContext(array $data, int $createdBy): int|false
    {
        $contextKey = $data['context_key'] ?? $this->generateContextKey($data['context_name']);

        $existing = QueryUtils::querySingleRow(
            "SELECT id FROM {$this->contextTable} WHERE context_key = ?",
            [$contextKey]
        );
        if ($existing) {
            return false;
        }

        $result = QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$this->contextTable}
            (user_id, context_key, context_name, description, widget_config, is_global, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $createdBy,
                $contextKey,
                $data['context_name'],
                $data['description'] ?? '',
                $data['widget_config'] ?? '{}',
                ($data['is_global'] ?? true) ? 1 : 0,
                $data['sort_order'] ?? 0,
            ]
        );

        return QueryUtils::getLastInsertId();
    }

    /**
     * Update an existing context definition
     */
    public function updateContext(int $contextId, array $data): bool
    {
        $fields = [];
        $params = [];

        if (isset($data['context_name'])) {
            $fields[] = 'context_name = ?';
            $params[] = $data['context_name'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $params[] = $data['description'];
        }
        if (isset($data['widget_config'])) {
            $fields[] = 'widget_config = ?';
            $params[] = is_array($data['widget_config']) ? json_encode($data['widget_config']) : $data['widget_config'];
        }
        if (isset($data['is_global'])) {
            $fields[] = 'is_global = ?';
            $params[] = $data['is_global'] ? 1 : 0;
        }
        if (isset($data['sort_order'])) {
            $fields[] = 'sort_order = ?';
            $params[] = $data['sort_order'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $contextId;

        return QueryUtils::sqlStatementThrowException(
                "UPDATE {$this->contextTable} SET " . implode(', ', $fields) . " WHERE id = ?",
                $params
            ) !== false;
    }

    /**
     * Delete a custom context
     */
    public function deleteContext(int $contextId): bool
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM {$this->assignmentTable} WHERE context_id = ?",
            [$contextId]
        );

        return QueryUtils::sqlStatementThrowException(
                "DELETE FROM {$this->contextTable} WHERE id = ?",
                [$contextId]
            ) !== false;
    }

    /**
     * Get all users with their current context assignments
     *
     * Updated to use the new user_dashboard_context_config table for checking
     * if user has custom widget configurations.
     */
    public function getUsersWithContexts(array $filters = []): array
    {
        $where = ['u.active = 1'];
        $params = [];

        if (!empty($filters['facility_id'])) {
            $where[] = 'u.facility_id = ?';
            $params[] = $filters['facility_id'];
        }

        if (!empty($filters['user_type'])) {
            $where[] = 'u.abook_type = ?';
            $params[] = $filters['user_type'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(u.username LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        } else {
            $where[] = '(u.fname > "" AND u.lname > "")';
        }

        // Updated query: removed widget_config from user_dashboard_context
        // Now we check user_dashboard_context_config for custom configs
        $sql = "SELECT
                    u.id,
                    u.username,
                    u.fname,
                    u.lname,
                    u.facility_id,
                    u.abook_type,
                    f.name as facility_name,
                    udc.active_context,
                    (SELECT COUNT(*) FROM {$this->userContextConfigTable} ucc WHERE ucc.user_id = u.id) as custom_config_count,
                    dca.context_id as assigned_context_id,
                    dca.context_key as assigned_context_key,
                    dca.is_locked,
                    dca.assigned_by,
                    dca.assigned_at
                FROM users u
                LEFT JOIN facility f ON u.facility_id = f.id
                LEFT JOIN {$this->userContextTable} udc ON u.id = udc.user_id
                LEFT JOIN {$this->assignmentTable} dca ON u.id = dca.user_id AND dca.is_active = 1
                WHERE " . implode(' AND ', $where) . "
                ORDER BY u.lname, u.fname";

        $result = QueryUtils::sqlStatementThrowException($sql, $params);
        $users = [];

        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $users[] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'name' => trim($row['fname'] . ' ' . $row['lname']),
                'fname' => $row['fname'],
                'lname' => $row['lname'],
                'facility_id' => $row['facility_id'],
                'facility_name' => $row['facility_name'],
                'user_type' => $row['abook_type'],
                'active_context' => $row['active_context'] ?? 'primary_care',
                'has_custom_config' => ((int)($row['custom_config_count'] ?? 0)) > 0,
                'custom_config_count' => (int)($row['custom_config_count'] ?? 0),
                'is_locked' => (bool)($row['is_locked'] ?? false),
                'assigned_context_id' => $row['assigned_context_id'],
                'assigned_context_key' => $row['assigned_context_key'],
                'assigned_by' => $row['assigned_by'],
                'assigned_at' => $row['assigned_at'],
            ];
        }

        return $users;
    }

    /**
     * Get user's custom widget configurations for all contexts
     */
    public function getUserContextConfigs(int $userId): array
    {
        $sql = "SELECT context_key, widget_config, created_at, updated_at
                FROM {$this->userContextConfigTable}
                WHERE user_id = ?
                ORDER BY context_key";

        $result = QueryUtils::sqlStatementThrowException($sql, [$userId]);
        $configs = [];

        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $configs[$row['context_key']] = [
                'widget_config' => json_decode((string) $row['widget_config'], true),
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];
        }

        return $configs;
    }

    /**
     * Clear all custom widget configurations for a user
     */
    public function clearUserContextConfigs(int $userId): bool
    {
        return QueryUtils::sqlStatementThrowException(
            "DELETE FROM {$this->userContextConfigTable} WHERE user_id = ?",
            [$userId]
        ) !== false;
    }

    /**
     * Assign a context to a user
     */
    public function assignContextToUser(int $userId, string $contextKey, int $assignedBy, bool $isLocked = false, ?int $contextId = null): bool
    {
        $currentContext = QueryUtils::querySingleRow(
            "SELECT active_context FROM {$this->userContextTable} WHERE user_id = ?",
            [$userId]
        );
        $oldContext = $currentContext['active_context'] ?? null;

        QueryUtils::sqlStatementThrowException(
            "UPDATE {$this->assignmentTable} SET is_active = 0 WHERE user_id = ?",
            [$userId]
        );

        $result = QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$this->assignmentTable}
            (user_id, context_id, context_key, assigned_by, is_locked, is_active, assigned_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())",
            [$userId, $contextId, $contextKey, $assignedBy, $isLocked ? 1 : 0]
        );

        if ($result !== false) {
            $existing = QueryUtils::querySingleRow(
                "SELECT id FROM {$this->userContextTable} WHERE user_id = ?",
                [$userId]
            );

            if ($existing) {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE {$this->userContextTable} SET active_context = ?, updated_at = NOW() WHERE user_id = ?",
                    [$contextKey, $userId]
                );
            } else {
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO {$this->userContextTable} (user_id, active_context, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
                    [$userId, $contextKey]
                );
            }

            $this->logAction($userId, 'assign', $oldContext, $contextKey, $assignedBy);
            return true;
        }

        return false;
    }

    /**
     * Remove context assignment from user
     */
    public function removeContextAssignment(int $userId, int $performedBy): bool
    {
        $result = QueryUtils::sqlStatementThrowException(
            "UPDATE {$this->assignmentTable} SET is_active = 0 WHERE user_id = ?",
            [$userId]
        );

        if ($result !== false) {
            $this->logAction($userId, 'unassign', null, null, $performedBy);
            return true;
        }

        return false;
    }

    /**
     * Lock/unlock a user's context
     */
    public function setUserContextLock(int $userId, bool $locked, int $performedBy): bool
    {
        $existing = QueryUtils::querySingleRow(
            "SELECT id FROM {$this->assignmentTable} WHERE user_id = ? AND is_active = 1",
            [$userId]
        );

        if (!$existing) {
            return false;
        }

        $result = QueryUtils::sqlStatementThrowException(
            "UPDATE {$this->assignmentTable} SET is_locked = ? WHERE user_id = ? AND is_active = 1",
            [$locked ? 1 : 0, $userId]
        );

        if ($result !== false) {
            $this->logAction($userId, $locked ? 'lock' : 'unlock', null, null, $performedBy);
            return true;
        }

        return false;
    }

    /**
     * Bulk assign context to multiple users
     */
    public function bulkAssignContext(array $userIds, string $contextKey, int $assignedBy, bool $isLocked = false): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($userIds as $userId) {
            if ($this->assignContextToUser($userId, $contextKey, $assignedBy, $isLocked)) {
                $results['success'][] = $userId;
            } else {
                $results['failed'][] = $userId;
            }
        }

        return $results;
    }

    /**
     * Set default context for a role type
     */
    public function setRoleDefaultContext(string $roleType, string $contextKey, int $setBy): bool
    {
        $existing = QueryUtils::querySingleRow(
            "SELECT id FROM {$this->roleDefaultsTable} WHERE role_type = ?",
            [$roleType]
        );

        if ($existing) {
            return QueryUtils::sqlStatementThrowException(
                    "UPDATE {$this->roleDefaultsTable} SET context_key = ?, updated_by = ?, updated_at = NOW() WHERE role_type = ?",
                    [$contextKey, $setBy, $roleType]
                ) !== false;
        }

        return QueryUtils::sqlStatementThrowException(
                "INSERT INTO {$this->roleDefaultsTable} (role_type, context_key, created_by, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
                [$roleType, $contextKey, $setBy]
            ) !== false;
    }

    /**
     * Get default context for a role type
     */
    public function getRoleDefaultContext(string $roleType): ?string
    {
        $result = QueryUtils::querySingleRow(
            "SELECT context_key FROM {$this->roleDefaultsTable} WHERE role_type = ?",
            [$roleType]
        );
        return $result['context_key'] ?? null;
    }

    /**
     * Get all role default assignments
     */
    public function getAllRoleDefaults(): array
    {
        $sql = "SELECT rd.*, u.username as set_by_username
                FROM {$this->roleDefaultsTable} rd
                LEFT JOIN users u ON rd.updated_by = u.id OR rd.created_by = u.id
                ORDER BY rd.role_type";

        $result = QueryUtils::sqlStatementThrowException($sql);
        $defaults = [];

        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $defaults[] = $row;
        }

        return $defaults;
    }

    /**
     * Set default context for a facility
     */
    public function setFacilityDefaultContext(int $facilityId, string $contextKey, int $setBy): bool
    {
        $existing = QueryUtils::querySingleRow(
            "SELECT id FROM {$this->facilityDefaultsTable} WHERE facility_id = ?",
            [$facilityId]
        );

        if ($existing) {
            return QueryUtils::sqlStatementThrowException(
                    "UPDATE {$this->facilityDefaultsTable} SET context_key = ?, updated_by = ?, updated_at = NOW() WHERE facility_id = ?",
                    [$contextKey, $setBy, $facilityId]
                ) !== false;
        }

        return QueryUtils::sqlStatementThrowException(
                "INSERT INTO {$this->facilityDefaultsTable} (facility_id, context_key, created_by, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
                [$facilityId, $contextKey, $setBy]
            ) !== false;
    }

    /**
     * Check if user's context is locked by admin
     */
    public function isUserContextLocked(int $userId): bool
    {
        $result = QueryUtils::querySingleRow(
            "SELECT is_locked FROM {$this->assignmentTable} WHERE user_id = ? AND is_active = 1",
            [$userId]
        );
        return (bool)($result['is_locked'] ?? false);
    }

    /**
     * Get context usage statistics
     */
    public function getContextUsageStats(): array
    {
        $sql = "SELECT
                    COALESCE(udc.active_context, 'primary_care') as context_key,
                    COUNT(*) as user_count
                FROM users u
                LEFT JOIN {$this->userContextTable} udc ON u.id = udc.user_id
                WHERE u.active = 1
                GROUP BY COALESCE(udc.active_context, 'primary_care')
                ORDER BY user_count DESC";

        $result = QueryUtils::sqlStatementThrowException($sql);
        $stats = [];

        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $stats[] = $row;
        }

        return $stats;
    }

    /**
     * Get custom config statistics (how many users have customized each context)
     */
    public function getCustomConfigStats(): array
    {
        $sql = "SELECT
                    context_key,
                    COUNT(DISTINCT user_id) as user_count
                FROM {$this->userContextConfigTable}
                GROUP BY context_key
                ORDER BY user_count DESC";

        $result = QueryUtils::sqlStatementThrowException($sql);
        $stats = [];

        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $stats[] = $row;
        }

        return $stats;
    }

    /**
     * Get available user roles/types for assignment
     */
    public function getAvailableUserTypes(): array
    {
        return [
            'physician' => xl('Physician'),
            'nurse' => xl('Nurse'),
            'medical_assistant' => xl('Medical Assistant'),
            'front_office' => xl('Front Office'),
            'billing' => xl('Billing'),
            'admin' => xl('Administrator'),
            'specialist' => xl('Specialist'),
            'therapist' => xl('Therapist'),
            'pharmacist' => xl('Pharmacist'),
            'lab_tech' => xl('Lab Technician'),
        ];
    }

    /**
     * Get widget order for a context (admin-level, context defaults only)
     *
     * @return array Ordered array of widget IDs, or empty array if none set
     */
    public function getWidgetOrderForContext(string $context): array
    {
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

        return [];
    }

    /**
     * Save widget order for a context (admin-level, context defaults)
     */
    public function saveWidgetOrderForContext(string $context, array $order): bool
    {
        $contextService = new DashboardContextService();
        return $contextService->saveWidgetOrder($context, $order, null);
    }

    /**
     * Get custom widget labels for a context
     *
     * @return array [widget_id => custom_label]
     */
    public function getWidgetLabelsForContext(string $context): array
    {
        $contextService = new DashboardContextService();
        return $contextService->getWidgetLabels($context);
    }

    /**
     * Save a custom widget label for a context
     */
    public function saveWidgetLabelForContext(string $context, string $widgetId, string $label): bool
    {
        $contextService = new DashboardContextService();
        return $contextService->saveWidgetLabel($context, $widgetId, $label);
    }

    /**
     * Delete a custom widget label for a context
     */
    public function deleteWidgetLabelForContext(string $context, string $widgetId): bool
    {
        $contextService = new DashboardContextService();
        return $contextService->deleteWidgetLabel($context, $widgetId);
    }

    /**
     * Export context configuration
     */
    public function exportContextConfig(?int $contextId = null): array
    {
        $contexts = [];

        if ($contextId !== null) {
            $context = QueryUtils::querySingleRow(
                "SELECT * FROM {$this->contextTable} WHERE id = ?",
                [$contextId]
            );
            $contexts = $context ? [$context] : [];
        } else {
            $contexts = $this->getAllContexts(false);
        }

        // Include widget_order and widget_labels for each context
        foreach ($contexts as &$ctx) {
            $contextKey = $ctx['context_key'];
            $ctx['widget_order'] = $this->getWidgetOrderForContext($contextKey);
            $ctx['widget_labels'] = $this->getWidgetLabelsForContext($contextKey);
        }

        return $contexts;
    }

    /**
     * Import context configuration
     */
    public function importContextConfig(array $config, int $importedBy): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($config as $ctx) {
            $contextId = $this->createContext([
                'context_key' => $ctx['context_key'] ?? null,
                'context_name' => $ctx['context_name'],
                'description' => $ctx['description'] ?? '',
                'widget_config' => $ctx['widget_config'] ?? '{}',
                'is_global' => $ctx['is_global'] ?? true,
                'sort_order' => $ctx['sort_order'] ?? 0,
            ], $importedBy);

            if ($contextId) {
                $results['success'][] = $ctx['context_name'];
                $contextKey = $ctx['context_key'] ?? null;

                // Import widget order if present
                if (!empty($ctx['widget_order']) && is_array($ctx['widget_order']) && $contextKey) {
                    $this->saveWidgetOrderForContext($contextKey, $ctx['widget_order']);
                }

                // Import widget labels if present
                if (!empty($ctx['widget_labels']) && is_array($ctx['widget_labels']) && $contextKey) {
                    foreach ($ctx['widget_labels'] as $widgetId => $label) {
                        $this->saveWidgetLabelForContext($contextKey, $widgetId, $label);
                    }
                }
            } else {
                $results['failed'][] = $ctx['context_name'];
            }
        }

        return $results;
    }

    /**
     * Get audit log entries
     */
    public function getAuditLog(array $filters = [], int $limit = 100): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'al.user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = 'al.action = ?';
            $params[] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'al.created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'al.created_at <= ?';
            $params[] = $filters['date_to'];
        }

        $sql = "SELECT al.*,
                       u.username as user_username,
                       u.fname as user_fname,
                       u.lname as user_lname,
                       p.username as performer_username
                FROM {$this->auditLogTable} al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN users p ON al.performed_by = p.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY al.created_at DESC
                LIMIT " . $limit;

        $result = QueryUtils::sqlStatementThrowException($sql, $params);
        $logs = [];

        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $logs[] = $row;
        }

        return $logs;
    }

    /**
     * Generate a context key from a name
     */
    private function generateContextKey(string $name): string
    {
        $key = strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '_', $name));
        $key = preg_replace('/_+/', '_', $key);
        $key = trim((string) $key, '_');
        return 'custom_' . $key;
    }

    /**
     * Log an action for audit
     */
    private function logAction(int $userId, string $action, ?string $oldContext, ?string $newContext, int $performedBy): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO {$this->auditLogTable} (user_id, action, old_context, new_context, performed_by, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$userId, $action, $oldContext, $newContext, $performedBy, $_SERVER['REMOTE_ADDR'] ?? null]
        );
    }
}
