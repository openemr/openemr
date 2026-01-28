<?php

/**
 * User Context Controller
 *
 * Handles user-facing AJAX requests for context management.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\DashboardContext\Controller;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\DashboardContext\Services\DashboardContextService;

class UserContextController
{
    private readonly DashboardContextService $contextService;
    private readonly int $userId;

    public function __construct()
    {
        $this->contextService = new DashboardContextService();
        $this->userId = (int)($_SESSION['authUserID'] ?? 0);
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

        if ($this->userId <= 0) {
            $this->sendError('Not authenticated', 401);
            return;
        }

        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        try {
            match ($action) {
                'get_active_context' => $this->getActiveContext(),
                'set_active_context' => $this->setActiveContext(),
                'get_context_widgets' => $this->getContextWidgets(),
                'save_context_widgets' => $this->saveContextWidgets(),
                'get_available_contexts' => $this->getAvailableContexts(),
                'get_manageable_widgets' => $this->getManageableWidgets(),
                'get_default_widgets' => $this->getDefaultWidgets(),
                'reset_to_defaults' => $this->resetToDefaults(),
                'create_custom_context' => $this->createCustomContext(),
                'delete_custom_context' => $this->deleteCustomContext(),
                'get_full_config' => $this->getFullConfig(),
                'get_widget_order' => $this->getWidgetOrder(),
                'save_widget_order' => $this->saveWidgetOrder(),
                'get_widget_labels' => $this->getWidgetLabels(),
                default => $this->sendError('Invalid action'),
            };
        } catch (\Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function getActiveContext(): void
    {
        $context = $this->contextService->getActiveContext($this->userId);
        $contexts = $this->contextService->getAvailableContexts();

        $this->sendSuccess([
            'context' => $context,
            'label' => $contexts[$context] ?? $context,
        ]);
    }

    private function setActiveContext(): void
    {
        $context = $_POST['context'] ?? '';
        if (empty($context)) {
            $this->sendError('Context is required');
            return;
        }

        // Check if user is locked
        if ($this->contextService->isUserContextLocked($this->userId)) {
            $this->sendError('Your context is locked by an administrator');
            return;
        }

        $success = $this->contextService->setActiveContext($this->userId, $context);
        $this->sendSuccess(['context' => $context], $success);
    }

    private function getContextWidgets(): void
    {
        $context = $_POST['context'] ?? null;
        $widgets = $this->contextService->getContextWidgets($this->userId, $context);
        $effectiveContext = $context ?? $this->contextService->getActiveContext($this->userId);
        $widgetOrder = $this->contextService->getWidgetOrder($this->userId, $effectiveContext);
        $widgetLabels = $this->contextService->getWidgetLabels($effectiveContext);
        $this->sendSuccess([
            'widgets' => $widgets,
            'widget_order' => $widgetOrder,
            'widget_labels' => $widgetLabels,
        ]);
    }

    private function saveContextWidgets(): void
    {
        $context = $_POST['context'] ?? '';
        $widgetsJson = $_POST['widgets'] ?? '{}';
        $widgets = json_decode((string) $widgetsJson, true);

        if (empty($context) || !is_array($widgets)) {
            $this->sendError('Invalid context or widgets data');
            return;
        }

        $success = $this->contextService->saveContextWidgets($this->userId, $context, $widgets);
        $this->sendSuccess([], $success);
    }

    private function getAvailableContexts(): void
    {
        $contexts = $this->contextService->getAvailableContexts();
        $customContexts = $this->contextService->getUserCustomContexts($this->userId);

        $this->sendSuccess([
            'contexts' => $contexts,
            'custom_contexts' => $customContexts,
        ]);
    }

    private function getManageableWidgets(): void
    {
        $widgets = $this->contextService->getManageableWidgets();
        $this->sendSuccess(['widgets' => $widgets]);
    }

    private function getDefaultWidgets(): void
    {
        $context = $_POST['context'] ?? 'primary_care';
        $widgets = $this->contextService->getDefaultContextWidgets($context);
        $this->sendSuccess(['widgets' => $widgets]);
    }

    private function resetToDefaults(): void
    {
        $context = $_POST['context'] ?? '';
        if (empty($context)) {
            $this->sendError('Context is required');
            return;
        }

        $success = $this->contextService->resetToDefaults($this->userId, $context);
        $this->sendSuccess([], $success);
    }

    private function createCustomContext(): void
    {
        $contextName = $_POST['context_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $widgetsJson = $_POST['widgets'] ?? '{}';
        $widgets = json_decode((string) $widgetsJson, true);

        if (empty($contextName)) {
            $this->sendError('Context name is required');
            return;
        }

        $contextId = $this->contextService->createCustomContext(
            $this->userId,
            $contextName,
            $description,
            $widgets ?? [],
            false // User-created contexts are not global
        );

        $this->sendSuccess(['context_id' => $contextId], $contextId !== false);
    }

    private function deleteCustomContext(): void
    {
        $contextId = (int)($_POST['context_id'] ?? 0);
        if ($contextId <= 0) {
            $this->sendError('Invalid context ID');
            return;
        }

        $success = $this->contextService->deleteCustomContext($contextId, $this->userId);
        $this->sendSuccess([], $success);
    }

    private function getFullConfig(): void
    {
        $activeContext = $this->contextService->getActiveContext($this->userId);
        $contexts = $this->contextService->getAvailableContexts();
        $manageableWidgets = $this->contextService->getManageableWidgets();
        $currentWidgets = $this->contextService->getContextWidgets($this->userId);
        $customContexts = $this->contextService->getUserCustomContexts($this->userId);
        $isLocked = $this->contextService->isUserContextLocked($this->userId);
        $widgetOrder = $this->contextService->getWidgetOrder($this->userId, $activeContext);
        $widgetLabels = $this->contextService->getWidgetLabels($activeContext);

        $this->sendSuccess([
            'active_context' => $activeContext,
            'contexts' => $contexts,
            'manageable_widgets' => $manageableWidgets,
            'current_widgets' => $currentWidgets,
            'custom_contexts' => $customContexts,
            'is_locked' => $isLocked,
            'widget_order' => $widgetOrder,
            'widget_labels' => $widgetLabels,
        ]);
    }

    private function getWidgetOrder(): void
    {
        $context = $_POST['context'] ?? null;
        $order = $this->contextService->getWidgetOrder($this->userId, $context);
        $this->sendSuccess(['widget_order' => $order]);
    }

    private function saveWidgetOrder(): void
    {
        $context = $_POST['context'] ?? '';
        $orderJson = $_POST['widget_order'] ?? '[]';
        $order = json_decode((string) $orderJson, true);

        if (empty($context) || !is_array($order)) {
            $this->sendError('Invalid context or order data');
            return;
        }

        $success = $this->contextService->saveWidgetOrder($context, $order, $this->userId);
        $this->sendSuccess([], $success);
    }

    private function getWidgetLabels(): void
    {
        $context = $_POST['context'] ?? $this->contextService->getActiveContext($this->userId);
        $labels = $this->contextService->getWidgetLabels($context);
        $this->sendSuccess(['widget_labels' => $labels]);
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
