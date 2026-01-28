<?php

/**
 * Admin Controller
 *
 * Handles administrative actions for the Dashboard Context Manager module.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\DashboardContext\Controller;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\DashboardContext\Services\DashboardContextService;
use OpenEMR\Modules\DashboardContext\Services\DashboardContextAdminService;
use OpenEMR\Common\Database\QueryUtils;

class AdminController
{
    private readonly DashboardContextService $contextService;
    private readonly DashboardContextAdminService $adminService;
    private readonly int $adminUserId;

    public function __construct()
    {
        $this->contextService = new DashboardContextService();
        $this->adminService = new DashboardContextAdminService();
        $this->adminUserId = (int)($_SESSION['authUserID'] ?? 0);
    }

    /**
     * Check if user has admin access
     */
    public function hasAccess(): bool
    {
        return AclMain::aclCheckCore('admin', 'super') || AclMain::aclCheckCore('admin', 'users');
    }

    /**
     * Handle AJAX request
     */
    public function handleRequest(): void
    {
        if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? $_GET["csrf_token_form"] ?? '')) {
            $this->sendError('CSRF verification failed', 403);
            return;
        }

        if (!$this->hasAccess()) {
            $this->sendError('Access denied', 403);
            return;
        }

        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        try {
            match ($action) {
                'get_all_contexts' => $this->getAllContexts(),
                'create_context' => $this->createContext(),
                'update_context' => $this->updateContext(),
                'delete_context' => $this->deleteContext(),
                'get_users' => $this->getUsers(),
                'assign_context_to_user' => $this->assignContextToUser(),
                'bulk_assign_context' => $this->bulkAssignContext(),
                'remove_user_assignment' => $this->removeUserAssignment(),
                'set_role_default' => $this->setRoleDefault(),
                'get_role_defaults' => $this->getRoleDefaults(),
                'set_facility_default' => $this->setFacilityDefault(),
                'get_usage_stats' => $this->getUsageStats(),
                'export_contexts' => $this->exportContexts(),
                'import_contexts' => $this->importContexts(),
                'get_manageable_widgets' => $this->getManageableWidgets(),
                'get_user_types' => $this->getUserTypes(),
                'get_facilities' => $this->getFacilities(),
                'get_admin_config' => $this->getAdminConfig(),
                'get_audit_log' => $this->getAuditLog(),
                'get_widget_order' => $this->getWidgetOrder(),
                'save_widget_order' => $this->saveWidgetOrder(),
                'get_widget_labels' => $this->getWidgetLabels(),
                'save_widget_label' => $this->saveWidgetLabel(),
                'delete_widget_label' => $this->deleteWidgetLabel(),
                default => $this->sendError('Invalid action'),
            };
        } catch (\Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function getAllContexts(): void
    {
        $includeSystem = ($_POST['include_system'] ?? '1') === '1';
        $contexts = $this->adminService->getAllContexts($includeSystem);
        $this->sendSuccess(['contexts' => $contexts]);
    }

    private function createContext(): void
    {
        $data = [
            'context_name' => $_POST['context_name'] ?? '',
            'context_key' => $_POST['context_key'] ?? null,
            'description' => $_POST['description'] ?? '',
            'widget_config' => $_POST['widget_config'] ?? '{}',
            'is_global' => ($_POST['is_global'] ?? '1') === '1',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        if (empty($data['context_name'])) {
            $this->sendError('Context name is required');
            return;
        }

        $contextId = $this->adminService->createContext($data, $this->adminUserId);
        $this->sendSuccess(['context_id' => $contextId], $contextId !== false);
    }

    private function updateContext(): void
    {
        $contextId = (int)($_POST['context_id'] ?? 0);
        if ($contextId <= 0) {
            $this->sendError('Invalid context ID');
            return;
        }

        $data = [];
        foreach (['context_name', 'description', 'widget_config', 'is_global', 'sort_order'] as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
                if ($field === 'is_global') {
                    $data[$field] = $_POST[$field] === '1';
                }
                if ($field === 'sort_order') {
                    $data[$field] = (int)$_POST[$field];
                }
            }
        }

        $success = $this->adminService->updateContext($contextId, $data);
        $this->sendSuccess([], $success);
    }

    private function deleteContext(): void
    {
        $contextId = (int)($_POST['context_id'] ?? 0);
        if ($contextId <= 0) {
            $this->sendError('Invalid context ID');
            return;
        }

        $success = $this->adminService->deleteContext($contextId);
        $this->sendSuccess([], $success);
    }

    private function getUsers(): void
    {
        $filters = [
            'facility_id' => $_POST['facility_id'] ?? null,
            'user_type' => $_POST['user_type'] ?? null,
            'search' => $_POST['search'] ?? null,
        ];

        $users = $this->adminService->getUsersWithContexts($filters);
        $this->sendSuccess(['users' => $users]);
    }

    private function assignContextToUser(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        $contextKey = $_POST['context_key'] ?? '';
        $lockContext = ($_POST['lock_context'] ?? '0') === '1';

        if ($userId <= 0 || empty($contextKey)) {
            $this->sendError('User ID and context key are required');
            return;
        }

        $success = $this->adminService->assignContextToUser($userId, $contextKey, $this->adminUserId, $lockContext);
        $this->sendSuccess([], $success);
    }

    private function bulkAssignContext(): void
    {
        $userIdsJson = $_POST['user_ids'] ?? '[]';
        $userIds = json_decode((string) $userIdsJson, true);
        $contextKey = $_POST['context_key'] ?? '';
        $lockContext = ($_POST['lock_context'] ?? '0') === '1';

        if (empty($userIds) || empty($contextKey)) {
            $this->sendError('User IDs and context key are required');
            return;
        }

        $results = $this->adminService->bulkAssignContext($userIds, $contextKey, $this->adminUserId, $lockContext);
        $successCount = count(array_filter($results));

        $this->sendSuccess([
            'results' => $results,
            'success_count' => $successCount,
            'total_count' => count($userIds),
        ]);
    }

    private function removeUserAssignment(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->sendError('Invalid user ID');
            return;
        }

        $success = $this->adminService->removeUserAssignment($userId);
        $this->sendSuccess([], $success);
    }

    private function setRoleDefault(): void
    {
        $roleType = $_POST['role_type'] ?? '';
        $contextKey = $_POST['context_key'] ?? '';

        if (empty($roleType) || empty($contextKey)) {
            $this->sendError('Role type and context key are required');
            return;
        }

        $success = $this->adminService->setRoleDefaultContext($roleType, $contextKey, $this->adminUserId);
        $this->sendSuccess([], $success);
    }

    private function getRoleDefaults(): void
    {
        $defaults = $this->adminService->getAllRoleDefaults();
        $userTypes = $this->adminService->getAvailableUserTypes();
        $this->sendSuccess([
            'defaults' => $defaults,
            'user_types' => $userTypes,
        ]);
    }

    private function setFacilityDefault(): void
    {
        $facilityId = (int)($_POST['facility_id'] ?? 0);
        $contextKey = $_POST['context_key'] ?? '';

        if ($facilityId <= 0 || empty($contextKey)) {
            $this->sendError('Facility ID and context key are required');
            return;
        }

        $success = $this->adminService->setFacilityDefaultContext($facilityId, $contextKey, $this->adminUserId);
        $this->sendSuccess([], $success);
    }

    private function getUsageStats(): void
    {
        $stats = $this->adminService->getContextUsageStats();
        $contexts = $this->contextService->getAvailableContexts();

        foreach ($stats as &$stat) {
            $stat['context_label'] = $contexts[$stat['context_key']] ?? $stat['context_key'];
        }

        $this->sendSuccess(['stats' => $stats]);
    }

    private function exportContexts(): void
    {
        $contextId = !empty($_POST['context_id']) ? (int)$_POST['context_id'] : null;
        $config = $this->adminService->exportContextConfig($contextId);
        $this->sendSuccess(['config' => $config]);
    }

    private function importContexts(): void
    {
        $configJson = $_POST['config'] ?? '[]';
        $config = json_decode((string) $configJson, true);

        if (!is_array($config)) {
            $this->sendError('Invalid configuration data');
            return;
        }

        $results = $this->adminService->importContextConfig($config, $this->adminUserId);
        $this->sendSuccess(['results' => $results]);
    }

    private function getManageableWidgets(): void
    {
        $widgets = $this->contextService->getManageableWidgets();
        $this->sendSuccess(['widgets' => $widgets]);
    }

    private function getUserTypes(): void
    {
        $userTypes = $this->adminService->getAvailableUserTypes();
        $this->sendSuccess(['user_types' => $userTypes]);
    }

    private function getFacilities(): void
    {
        $sql = "SELECT id, name FROM facility WHERE service_location = 1 ORDER BY name";
        $result = QueryUtils::sqlStatementThrowException($sql);
        $facilities = [];
        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $facilities[] = $row;
        }
        $this->sendSuccess(['facilities' => $facilities]);
    }

    private function getAdminConfig(): void
    {
        $contexts = $this->adminService->getAllContexts(true);
        $userTypes = $this->adminService->getAvailableUserTypes();
        $roleDefaults = $this->adminService->getAllRoleDefaults();
        $usageStats = $this->adminService->getContextUsageStats();
        $widgets = $this->contextService->getManageableWidgets();

        $sql = "SELECT id, name FROM facility WHERE service_location = 1 ORDER BY name";
        $result = QueryUtils::sqlStatementThrowException($sql);
        $facilities = [];
        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $facilities[] = $row;
        }

        // Include widget_order and widget_labels per context
        $widgetOrders = [];
        $widgetLabels = [];
        foreach ($contexts as $ctx) {
            $key = $ctx['context_key'];
            $widgetOrders[$key] = $this->adminService->getWidgetOrderForContext($key);
            $widgetLabels[$key] = $this->adminService->getWidgetLabelsForContext($key);
        }

        $this->sendSuccess([
            'contexts' => $contexts,
            'user_types' => $userTypes,
            'role_defaults' => $roleDefaults,
            'usage_stats' => $usageStats,
            'widgets' => $widgets,
            'facilities' => $facilities,
            'widget_orders' => $widgetOrders,
            'widget_labels' => $widgetLabels,
        ]);
    }

    private function getWidgetOrder(): void
    {
        $contextKey = $_POST['context_key'] ?? '';
        if (empty($contextKey)) {
            $this->sendError('Context key is required');
            return;
        }

        $order = $this->adminService->getWidgetOrderForContext($contextKey);
        $this->sendSuccess(['widget_order' => $order]);
    }

    private function saveWidgetOrder(): void
    {
        $contextKey = $_POST['context_key'] ?? '';
        $orderJson = $_POST['widget_order'] ?? '[]';
        $order = json_decode((string) $orderJson, true);

        if (empty($contextKey) || !is_array($order)) {
            $this->sendError('Context key and widget order are required');
            return;
        }

        $success = $this->adminService->saveWidgetOrderForContext($contextKey, $order);
        $this->sendSuccess([], $success);
    }

    private function getWidgetLabels(): void
    {
        $contextKey = $_POST['context_key'] ?? '';
        if (empty($contextKey)) {
            $this->sendError('Context key is required');
            return;
        }

        $labels = $this->adminService->getWidgetLabelsForContext($contextKey);
        $this->sendSuccess(['widget_labels' => $labels]);
    }

    private function saveWidgetLabel(): void
    {
        $contextKey = $_POST['context_key'] ?? '';
        $widgetId = $_POST['widget_id'] ?? '';
        $label = $_POST['label'] ?? '';

        if (empty($contextKey) || empty($widgetId) || empty($label)) {
            $this->sendError('Context key, widget ID, and label are required');
            return;
        }

        $success = $this->adminService->saveWidgetLabelForContext($contextKey, $widgetId, $label);
        $this->sendSuccess([], $success);
    }

    private function deleteWidgetLabel(): void
    {
        $contextKey = $_POST['context_key'] ?? '';
        $widgetId = $_POST['widget_id'] ?? '';

        if (empty($contextKey) || empty($widgetId)) {
            $this->sendError('Context key and widget ID are required');
            return;
        }

        $success = $this->adminService->deleteWidgetLabelForContext($contextKey, $widgetId);
        $this->sendSuccess([], $success);
    }

    private function getAuditLog(): void
    {
        $filters = [
            'user_id' => $_POST['user_id'] ?? null,
            'action' => $_POST['audit_action'] ?? null,
            'date_from' => $_POST['date_from'] ?? null,
            'date_to' => $_POST['date_to'] ?? null,
        ];
        $limit = (int)($_POST['limit'] ?? 100);

        $logs = $this->adminService->getAuditLog($filters, $limit);
        $this->sendSuccess(['logs' => $logs]);
    }

    private function sendSuccess(array $data = [], bool $success = true): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success], $data));
    }

    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    }
}
