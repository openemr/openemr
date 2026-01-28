<?php

/**
 * Context Widget Controller
 *
 * Handles rendering of the context manager widget on the patient dashboard.
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
use OpenEMR\Core\OEGlobalsBag;

class ContextWidgetController
{
    private readonly DashboardContextService $contextService;
    private readonly int $userId;

    public function __construct()
    {
        $this->contextService = new DashboardContextService();
        $this->userId = (int)($_SESSION['authUserID'] ?? 0);
    }

    /**
     * Render a compact navbar dropdown for the page heading title nav
     * This is a streamlined version for the title bar area
     *
     * @return string
     */
    public function renderNavbarDropdown(): string
    {
        if ($this->userId <= 0) {
            return '';
        }

        $activeContext = $this->contextService->getActiveContext($this->userId);
        $contexts = $this->contextService->getAvailableContexts();
        $customContexts = $this->contextService->getUserCustomContexts($this->userId);
        $manageableWidgets = $this->contextService->getManageableWidgets();
        $currentWidgets = $this->contextService->getFullWidgetConfig($this->userId);
        $isLocked = $this->contextService->isUserContextLocked($this->userId);
        $canSwitch = (OEGlobalsBag::getInstance()->get('dashboard_context_user_can_switch') ?? true) && !$isLocked;
        $widgetOrder = $this->contextService->getWidgetOrder($this->userId, $activeContext);
        $widgetLabels = $this->contextService->getWidgetLabels($activeContext);

        $csrfToken = CsrfUtils::collectCsrfToken();
        $webRoot = OEGlobalsBag::getInstance()->get('webroot') ?? '';
        $moduleUrl = $webRoot . '/interface/modules/custom_modules/oe-module-dashboard-context';

        // Get current context label for display
        $currentLabel = $contexts[$activeContext] ?? $activeContext;
        foreach ($customContexts as $ctx) {
            if ($ctx['context_key'] === $activeContext) {
                $currentLabel = $ctx['context_name'];
                break;
            }
        }

        ob_start();
        ?>
        <style>
            /* Navbar Context Dropdown Styles */
            .context-nav-dropdown {
                display: inline-flex;
                align-items: center;
                margin-left: 1rem;
            }
            .context-nav-dropdown .context-label {
                font-size: 0.8rem;
                color: var(--gray);
                margin-right: 0.5rem;
                white-space: nowrap;
            }
            .context-nav-dropdown .context-select {
                min-width: 140px;
                max-width: 180px;
                font-size: 0.9rem;
                padding: 0.2rem 0.5rem;
                /*height: auto;*/
                border-radius: 0.25rem;
            }
            .context-nav-dropdown .context-settings-btn {
                padding: 0.2rem 0.4rem;
                margin-left: 0.25rem;
                font-size: 0.8rem;
            }
            .context-nav-dropdown .locked-icon {
                color: var(--warning);
                margin-left: 0.25rem;
                font-size: 0.75rem;
            }
            /* Hide on very small screens */
            @media (max-width: 768px) {
                .context-nav-dropdown .context-label {
                    display: none;
                }
                .context-nav-dropdown .context-select {
                    min-width: 120px;
                }
            }
        </style>

        <div class="context-nav-dropdown" id="context-nav-widget">
            <span class="context-label"><?php echo xlt('Context'); ?>:</span>
            <select id="nav-context-selector"
                    class="form-control form-control-sm context-select"
                    <?php echo $canSwitch ? '' : 'disabled'; ?>
                    title="<?php echo xla('Select Care Context'); ?>">
                <?php foreach ($contexts as $key => $label) : ?>
                    <option value="<?php echo attr($key); ?>" <?php echo $key === $activeContext ? 'selected' : ''; ?>>
                        <?php echo text($label); ?>
                    </option>
                <?php endforeach; ?>
                <?php if (!empty($customContexts)) : ?>
                    <option disabled>──────────</option>
                    <?php foreach ($customContexts as $ctx) : ?>
                        <option value="<?php echo attr($ctx['context_key']); ?>"
                            <?php echo $ctx['context_key'] === $activeContext ? 'selected' : ''; ?>>
                            <?php echo text($ctx['context_name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if ($canSwitch) : ?>
                <button type="button"
                        class="btn btn-sm btn-outline-secondary context-settings-btn"
                        id="nav-context-settings"
                        title="<?php echo xla('Configure Widget Visibility'); ?>">
                    <i class="fa fa-cog"></i>
                </button>
            <?php endif; ?>
            <?php if ($isLocked) : ?>
                <i class="fa fa-lock locked-icon" title="<?php echo xla('Context locked by administrator'); ?>"></i>
            <?php endif; ?>
        </div>

        <script>
        (function($) {
            'use strict';

            // Navbar Context Controller - lightweight version
            const NavContextController = {
                config: {
                    ajaxUrl: <?php echo js_escape($moduleUrl . '/public/ajax.php'); ?>,
                    csrfToken: <?php echo js_escape($csrfToken); ?>,
                    currentContext: <?php echo js_escape($activeContext); ?>,
                    contexts: <?php echo js_escape($contexts); ?>,
                    widgets: <?php echo js_escape($currentWidgets); ?>,
                    manageableWidgets: <?php echo js_escape($manageableWidgets); ?>,
                    customContexts: <?php echo js_escape($customContexts); ?>,
                    canSwitch: <?php echo js_escape($canSwitch); ?>,
                    widgetOrder: <?php echo js_escape($widgetOrder); ?>,
                    widgetLabels: <?php echo js_escape($widgetLabels); ?>
                },

                xl: {
                    widgetSettings: <?php echo xlj('Context Settings'); ?>,
                    currentContext: <?php echo xlj('Current Context'); ?>,
                    selectWidgets: <?php echo xlj('Select which widgets to display for this context.'); ?>,
                    createCustom: <?php echo xlj('Create Custom Context'); ?>,
                    contextName: <?php echo xlj('Context Name'); ?>,
                    description: <?php echo xlj('Description'); ?>,
                    createContext: <?php echo xlj('Create Context'); ?>,
                    resetDefaults: <?php echo xlj('Reset to Defaults'); ?>,
                    cancel: <?php echo xlj('Cancel'); ?>,
                    saveSettings: <?php echo xlj('Save Settings'); ?>,
                    contextSwitched: <?php echo xlj('Context switched'); ?>,
                    switchFailed: <?php echo xlj('Failed to switch context'); ?>,
                    settingsSaved: <?php echo xlj('Settings saved'); ?>,
                    saveFailed: <?php echo xlj('Failed to save settings'); ?>,
                    resetConfirm: <?php echo xlj('Reset to default settings?'); ?>,
                    resetDone: <?php echo xlj('Reset to defaults'); ?>,
                    enterName: <?php echo xlj('Please enter a context name'); ?>,
                    contextCreated: <?php echo xlj('Custom context created'); ?>,
                    createFailed: <?php echo xlj('Failed to create context'); ?>
                },

                init: function() {
                    this.bindEvents();
                    // Load and watch for late-loading widgets (loaded after page ready)
                    this.observeWidgetAdditions();
                },

                observeWidgetAdditions: function() {
                    const self = this;
                    let debounceTimer = null;

                    // Use MutationObserver to detect when new widgets are added to the DOM
                    // TODO: sjp This is too reliant widget is a card. Address next iteration.
                    const observer = new MutationObserver(function(mutations) {
                        let hasNewCards = false;

                        for (const mutation of mutations) {
                            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                                for (const node of mutation.addedNodes) {
                                    if (node.nodeType === Node.ELEMENT_NODE) {
                                        // Check if the added node is a card or contains cards
                                        if (node.classList && node.classList.contains('card')) {
                                            hasNewCards = true;
                                            break;
                                        }
                                        if (node.querySelector && node.querySelector('.card')) {
                                            hasNewCards = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            if (hasNewCards) break;
                        }

                        if (hasNewCards) {
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(function() {
                                self.applyWidgetVisibility();
                            }, 50);
                        }
                    });

                    // Observe the main content area for widget additions
                    const targetNode = document.getElementById('container_div') || document.body;
                    observer.observe(targetNode, {
                        childList: true,
                        subtree: true
                    });
                },

                bindEvents: function() {
                    const self = this;

                    // Navbar context selector change
                    $('#nav-context-selector').on('change', function() {
                        if (self.config.canSwitch) {
                            self.switchContext($(this).val());
                        }
                    });

                    // Navbar settings button
                    $('#nav-context-settings').on('click', function() {
                        self.showSettingsDialog();
                    });

                    // Also sync with main dashboard widget if present
                    $(document).on('change', '#dashboard-context-selector', function() {
                        // Sync navbar dropdown with main widget
                        $('#nav-context-selector').val($(this).val());
                    });
                },

                switchContext: function(context) {
                    const self = this;
                    if (typeof top.restoreSession === 'function') {
                        top.restoreSession();
                    }

                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'set_active_context',
                            context: context,
                            csrf_token_form: this.config.csrfToken
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                self.config.currentContext = context;
                                self.loadContextWidgets(context);
                                // Sync main widget dropdown if present
                                $('#dashboard-context-selector').val(context);
                                self.showAlert(self.xl.contextSwitched, 'success');
                            } else {
                                self.showAlert(response.error || self.xl.switchFailed, 'danger');
                            }
                        }
                    });
                },

                loadContextWidgets: function(context) {
                    const self = this;
                    if (typeof top.restoreSession === 'function') {
                        top.restoreSession();
                    }

                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'get_context_widgets',
                            context: context,
                            csrf_token_form: this.config.csrfToken
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Merge received widgets with manageableWidgets
                                const fullWidgets = {};
                                for (const widgetId in self.config.manageableWidgets) {
                                    if (self.config.manageableWidgets.hasOwnProperty(widgetId)) {
                                        fullWidgets[widgetId] = response.widgets[widgetId] === true;
                                    }
                                }
                                self.config.widgets = fullWidgets;
                                self.config.widgetOrder = response.widget_order || [];
                                self.config.widgetLabels = response.widget_labels || {};
                                self.applyWidgetVisibility();
                            }
                        }
                    });
                },

                findWidgetCard: function(widgetId) {
                    let $widget = null;

                    // Strategy 1: Direct ID match
                    $widget = $('#' + widgetId);
                    if ($widget.length) {
                        if ($widget.hasClass('card')) {
                            return $widget;
                        }
                        let $card = $widget.closest('.card');
                        if ($card.length) {
                            return $card;
                        }
                    }

                    // Strategy 2: data-target
                    $widget = $('[data-target="#' + widgetId + '"]').closest('.card');
                    if ($widget.length) {
                        return $widget;
                    }

                    // Strategy 3: href
                    $widget = $('[href="#' + widgetId + '"]').closest('.card');
                    if ($widget.length) {
                        return $widget;
                    }

                    // Strategy 4: card_ prefix
                    if (widgetId.startsWith('card_')) {
                        $widget = $('[data-card-id="' + widgetId + '"]');
                        if ($widget.length) {
                            return $widget.hasClass('card') ? $widget : $widget.closest('.card');
                        }
                    }

                    // Strategy 5: aria attributes
                    $widget = $('[aria-labelledby="' + widgetId + '"], [aria-controls="' + widgetId + '"]').closest('.card');
                    if ($widget.length) {
                        return $widget;
                    }

                    // Strategy 6: Search within cards
                    $widget = $('.card').filter(function() {
                        return $(this).find('#' + widgetId).length > 0 ||
                               $(this).find('[id^="' + widgetId + '"]').length > 0;
                    });
                    if ($widget.length) {
                        return $widget.first();
                    }

                    return null;
                },

                applyWidgetVisibility: function() {
                    const self = this;
                    for (const widgetId in this.config.manageableWidgets) {
                        if (this.config.manageableWidgets.hasOwnProperty(widgetId)) {
                            const visible = this.config.widgets[widgetId] === true;
                            const $widget = this.findWidgetCard(widgetId);

                            if ($widget && $widget.length) {
                                if (visible) {
                                    $widget.show().removeClass('context-hidden');
                                } else {
                                    $widget.hide().addClass('context-hidden');
                                }
                            }
                        }
                    }
                    this.applyWidgetOrder();
                    $(document).trigger('dashboardContextChanged', [this.config.currentContext, this.config.widgets]);
                },

                applyWidgetOrder: function() {
                    const order = this.config.widgetOrder;
                    if (!order || !order.length) return;

                    // Collect all widget cards and their parent container
                    let $parentContainer = null;
                    const cardMap = {};

                    for (let i = 0; i < order.length; i++) {
                        const $card = this.findWidgetCard(order[i]);
                        if ($card && $card.length) {
                            if (!$parentContainer) {
                                $parentContainer = $card.parent();
                            }
                            cardMap[order[i]] = $card;
                        }
                    }

                    if (!$parentContainer || !$parentContainer.length) return;

                    // Reorder by appending in order
                    for (let i = 0; i < order.length; i++) {
                        if (cardMap[order[i]]) {
                            $parentContainer.append(cardMap[order[i]]);
                        }
                    }
                },

                showSettingsDialog: function() {
                    const self = this;
                    const contextLabel = this.config.contexts[this.config.currentContext] || this.config.currentContext;
                    const customLabels = this.config.widgetLabels || {};

                    // Build widget toggles with custom labels
                    let widgetToggles = '';
                    for (const [widgetId, label] of Object.entries(this.config.manageableWidgets)) {
                        const displayLabel = customLabels[widgetId] || label;
                        const checked = this.config.widgets[widgetId] === true ? 'checked' : '';
                        const safeId = widgetId.replace(/[^a-zA-Z0-9_-]/g, '_');
                        widgetToggles += '<div class="form-check">' +
                            '<input class="form-check-input widget-toggle" type="checkbox" ' +
                            'id="nav_toggle_' + safeId + '" data-widget-id="' + self.escapeAttr(widgetId) + '" ' + checked + '>' +
                            '<label class="form-check-label" for="nav_toggle_' + safeId + '">' + self.escapeHtml(displayLabel) + '</label>' +
                            '</div>';
                    }

                    // Build dialog
                    var $overlay = $('<div>', { id: 'navContextSettingsDialog', class: 'context-dialog-overlay' });
                    var $dialog = $('<div>', { class: 'context-dialog-content' });

                    // Header
                    var $header = $('<div>', { class: 'context-dialog-header' });
                    $header.append($('<h5>', { class: 'm-0' }).append(
                        $('<i>', { class: 'fa fa-cog mr-2' }),
                        document.createTextNode(self.xl.widgetSettings)
                    ));
                    $header.append($('<button>', { type: 'button', class: 'btn btn-sm btn-secondary close-dialog' }).html('&times;'));

                    // Body
                    var $body = $('<div>', { class: 'context-dialog-body' });
                    var $row = $('<div>', { class: 'row' });

                    // Left column - widget toggles
                    var $leftCol = $('<div>', { class: 'col-md-7' });
                    $leftCol.append($('<h6>', { class: 'border-bottom pb-2 mb-3' }).append(
                        document.createTextNode(self.xl.currentContext + ': '),
                        $('<span>', { class: 'text-primary font-weight-bold' }).text(contextLabel)
                    ));
                    $leftCol.append($('<p>', { class: 'text-muted small' }).text(self.xl.selectWidgets));
                    $leftCol.append($('<div>', { class: 'widget-grid', style: 'display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;' }).html(widgetToggles));

                    // Right column - create custom
                    var $rightCol = $('<div>', { class: 'col-md-5 border-left' });
                    $rightCol.append($('<h6>', { class: 'border-bottom pb-2 mb-3' }).text(self.xl.createCustom));
                    $rightCol.append(
                        $('<div>', { class: 'form-group' }).append(
                            $('<label>', { class: 'font-weight-bold' }).text(self.xl.contextName),
                            $('<input>', { type: 'text', class: 'form-control form-control-sm', id: 'navCustomContextName' })
                        ),
                        $('<div>', { class: 'form-group' }).append(
                            $('<label>').text(self.xl.description),
                            $('<textarea>', { class: 'form-control form-control-sm', id: 'navCustomContextDesc', rows: 2 })
                        ),
                        $('<button>', { type: 'button', class: 'btn btn-sm btn-success', id: 'navCreateCustomBtn' }).append(
                            $('<i>', { class: 'fa fa-plus mr-1' }),
                            document.createTextNode(self.xl.createContext)
                        )
                    );

                    $row.append($leftCol, $rightCol);
                    $body.append($row);
                    $body.append($('<hr>'));
                    $body.append(
                        $('<button>', { type: 'button', class: 'btn btn-warning', id: 'navResetDefaultsBtn' }).append(
                            $('<i>', { class: 'fa fa-undo mr-1' }),
                            document.createTextNode(self.xl.resetDefaults)
                        )
                    );

                    // Footer
                    var $footer = $('<div>', { class: 'context-dialog-footer' });
                    $footer.append(
                        $('<button>', { type: 'button', class: 'btn btn-secondary mr-2 close-dialog' }).text(self.xl.cancel),
                        $('<button>', { type: 'button', class: 'btn btn-primary', id: 'navSaveSettingsBtn' }).append(
                            $('<i>', { class: 'fa fa-save mr-1' }),
                            document.createTextNode(self.xl.saveSettings)
                        )
                    );

                    $dialog.append($header, $body, $footer);
                    $overlay.append($dialog);
                    $('body').append($overlay);

                    // Add dialog styles if not present
                    if (!$('#navContextDialogStyles').length) {
                        $('head').append(`<style id="navContextDialogStyles">
                            .context-dialog-overlay {
                                position: fixed;
                                top: 0; left: 0; right: 0; bottom: 0;
                                background: rgba(0,0,0,0.5);
                                z-index: 10000;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                            .context-dialog-content {
                                background: var(--white, #fff);
                                border-radius: 4px;
                                box-shadow: 0 5px 15px rgba(0,0,0,0.5);
                                width: 90%;
                                max-width: 800px;
                                max-height: 90vh;
                                overflow-y: auto;
                            }
                            .context-dialog-header, .context-dialog-footer {
                                padding: 1rem;
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            }
                            .context-dialog-header { border-bottom: 1px solid #dee2e6; }
                            .context-dialog-footer { border-top: 1px solid #dee2e6; }
                            .context-dialog-body { padding: 1rem; }
                        </style>`);
                    }

                    // Bind dialog events
                    $overlay.find('.close-dialog').on('click', function() {
                        $overlay.remove();
                    });

                    $overlay.on('click', function(e) {
                        if (e.target === this) {
                            $(this).remove();
                        }
                    });

                    $(document).on('keydown.navContextDialog', function(e) {
                        if (e.key === 'Escape') {
                            $overlay.remove();
                            $(document).off('keydown.navContextDialog');
                        }
                    });

                    $('#navSaveSettingsBtn').on('click', function() {
                        self.saveSettings();
                        $overlay.remove();
                    });

                    $('#navResetDefaultsBtn').on('click', function() {
                        self.resetToDefaults();
                    });

                    $('#navCreateCustomBtn').on('click', function() {
                        self.createCustomContext();
                    });

                    $('.widget-toggle').on('change', function() {
                        self.config.widgets[$(this).data('widget-id')] = $(this).is(':checked');
                    });
                },

                saveSettings: function() {
                    const self = this;
                    const widgets = {};

                    $('#navContextSettingsDialog .widget-toggle').each(function() {
                        widgets[$(this).data('widget-id')] = $(this).is(':checked');
                    });

                    if (typeof top.restoreSession === 'function') {
                        top.restoreSession();
                    }

                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'save_context_widgets',
                            context: this.config.currentContext,
                            widgets: JSON.stringify(widgets),
                            csrf_token_form: this.config.csrfToken
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                self.config.widgets = widgets;
                                self.applyWidgetVisibility();
                                self.showAlert(self.xl.settingsSaved, 'success');
                            } else {
                                self.showAlert(self.xl.saveFailed, 'danger');
                            }
                        }
                    });
                },

                resetToDefaults: function() {
                    const self = this;
                    if (!confirm(this.xl.resetConfirm)) {
                        return;
                    }

                    if (typeof top.restoreSession === 'function') {
                        top.restoreSession();
                    }

                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'reset_to_defaults',
                            context: this.config.currentContext,
                            csrf_token_form: this.config.csrfToken
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                self.loadContextWidgets(self.config.currentContext);
                                $('#navContextSettingsDialog').remove();
                                self.showAlert(self.xl.resetDone, 'success');
                            }
                        }
                    });
                },

                createCustomContext: function() {
                    const self = this;
                    const name = $('#navCustomContextName').val();
                    const description = $('#navCustomContextDesc').val();

                    if (!name) {
                        self.showAlert(self.xl.enterName, 'warning');
                        return;
                    }

                    const widgets = {};
                    $('#navContextSettingsDialog .widget-toggle').each(function() {
                        widgets[$(this).data('widget-id')] = $(this).is(':checked');
                    });

                    if (typeof top.restoreSession === 'function') {
                        top.restoreSession();
                    }

                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'create_custom_context',
                            context_name: name,
                            description: description,
                            widgets: JSON.stringify(widgets),
                            csrf_token_form: this.config.csrfToken
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                self.showAlert(self.xl.contextCreated, 'success');
                                location.reload();
                            } else {
                                self.showAlert(response.error || self.xl.createFailed, 'danger');
                            }
                        }
                    });
                },

                showAlert: function(message, type) {
                    if (typeof alertMsg === 'function') {
                        alertMsg(message, type === 'danger' ? 5000 : 3000, type);
                        return;
                    }

                    const alertClass = 'alert-' + (type || 'info');
                    const $alert = $('<div>', {
                        class: 'alert ' + alertClass + ' alert-dismissible',
                        style: 'position:fixed;top:10px;right:10px;z-index:10001;min-width:200px;'
                    }).text(message).append(
                        $('<button>', { type: 'button', class: 'close ml-2' }).html('&times;').on('click', function() {
                            $(this).parent().remove();
                        })
                    );
                    $('body').append($alert);
                    setTimeout(function() {
                        $alert.fadeOut(function() { $(this).remove(); });
                    }, 3000);
                },

                escapeHtml: function(text) {
                    if (!text) return '';
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                },

                escapeAttr: function(text) {
                    if (!text) return '';
                    return text.replace(/[&<>"']/g, function(m) {
                        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
                    });
                }
            };

            $(function() {
                NavContextController.init();
            });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the context manager widget (main dashboard card widget)
     *
     * @return string
     */
    public function renderWidget(): string
    {
        if ($this->userId <= 0) {
            return '';
        }
        // Check if widget should be shown
        if (!(OEGlobalsBag::getInstance()->get('dashboard_context_show_widget') ?? true)) {
            return '';
        }

        $activeContext = $this->contextService->getActiveContext($this->userId);
        $contexts = $this->contextService->getAvailableContexts();
        $customContexts = $this->contextService->getUserCustomContexts($this->userId);
        $manageableWidgets = $this->contextService->getManageableWidgets();
        $currentWidgets = $this->contextService->getFullWidgetConfig($this->userId);
        $isLocked = $this->contextService->isUserContextLocked($this->userId);
        $canSwitch = (OEGlobalsBag::getInstance()->get('dashboard_context_user_can_switch') ?? true) && !$isLocked;
        $widgetOrder = $this->contextService->getWidgetOrder($this->userId, $activeContext);
        $widgetLabels = $this->contextService->getWidgetLabels($activeContext);

        $csrfToken = CsrfUtils::collectCsrfToken();
        $webRoot = OEGlobalsBag::getInstance()->get('webroot') ?? '';
        $moduleUrl = $webRoot . '/interface/modules/custom_modules/oe-module-dashboard-context';
        ob_start();
        ?>
        <style>
          .context-dialog-overlay {
            position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
          }

          .context-dialog-content {
            background: var(--light);
            box-shadow: var(--boxshadow, 0 5px 15px rgba(0, 0, 0, 0.5));
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
          }

          .context-dialog-header,
          .context-dialog-footer {
            flex-shrink: 0;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
          }

          .context-dialog-footer {
            border-top: 1px solid #dee2e6;
            border-bottom: none;
            justify-content: flex-end;
          }

          .context-dialog-body {
            padding: 1rem;
            overflow-y: auto;
          }

          .widget-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
          }

          /* Optional: compact selector spacing */
          #dashboard-context-widget .input-group-sm > .form-control,
          #dashboard-context-widget .input-group-sm > .input-group-append > .btn {
            height: 32px;
          }

          #dashboard-context-widget .btn-setting {
            display: flex;
            align-items: center;
            justify-content: center;
          }
        </style>
        <div id="dashboard-context-widget" class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-5 p-1">
                    <?php if ($isLocked) : ?>
                        <div class="alert alert-warning py-1 px-2 mb-2 small d-flex align-items-center">
                            <i class="fa fa-lock mr-1"></i>
                            <?php echo xlt('Context locked by administrator'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group input-group-md align-items-center">
                        <select id="dashboard-context-selector"
                            class="form-control form-control-md"
                            <?php echo $canSwitch ? '' : 'disabled'; ?>>
                            <?php foreach ($contexts as $key => $label) : ?>
                                <option value="<?php echo attr($key); ?>" <?php echo $key === $activeContext ? 'selected' : ''; ?>>
                                    <?php echo text($label); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (!empty($customContexts)) : ?>
                                <option disabled>──────────</option>
                                <?php foreach ($customContexts as $ctx) : ?>
                                    <option value="<?php echo attr($ctx['context_key']); ?>"
                                        <?php echo $ctx['context_key'] === $activeContext ? 'selected' : ''; ?>>
                                        <?php echo text($ctx['context_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if ($canSwitch) : ?>
                            <div class="input-group-append">
                                <button type="button"
                                    class="btn btn-lg btn-setting py-0 px-2"
                                    id="open-context-settings"
                                    title="<?php echo xla('Configure Widgets'); ?>">
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            (function ($) {
                'use strict';
                const DashboardContext = {
                    config: {
                        ajaxUrl: <?php echo js_escape($moduleUrl . '/public/ajax.php'); ?>,
                        csrfToken: <?php echo js_escape($csrfToken); ?>,
                        currentContext: <?php echo js_escape($activeContext); ?>,
                        contexts: <?php echo js_escape($contexts); ?>,
                        widgets: <?php echo js_escape($currentWidgets); ?>,
                        manageableWidgets: <?php echo js_escape($manageableWidgets); ?>,
                        customContexts: <?php echo js_escape($customContexts); ?>,
                        canSwitch: <?php echo js_escape($canSwitch); ?>,
                        widgetOrder: <?php echo js_escape($widgetOrder); ?>,
                        widgetLabels: <?php echo js_escape($widgetLabels); ?>
                    },

                    xl: {
                        widgetSettings: <?php echo xlj('Context Settings'); ?>,
                        currentContext: <?php echo xlj('Current Context'); ?>,
                        selectWidgets: <?php echo xlj('Select which widgets to display for this context.'); ?>,
                        createCustom: <?php echo xlj('Create Custom Context'); ?>,
                        contextName: <?php echo xlj('Context Name'); ?>,
                        description: <?php echo xlj('Description'); ?>,
                        createContext: <?php echo xlj('Create Context'); ?>,
                        resetDefaults: <?php echo xlj('Reset to Defaults'); ?>,
                        cancel: <?php echo xlj('Cancel'); ?>,
                        saveSettings: <?php echo xlj('Save Settings'); ?>,
                        contextSwitched: <?php echo xlj('Context switched'); ?>,
                        switchFailed: <?php echo xlj('Failed to switch context'); ?>,
                        settingsSaved: <?php echo xlj('Settings saved'); ?>,
                        saveFailed: <?php echo xlj('Failed to save settings'); ?>,
                        resetConfirm: <?php echo xlj('Reset to default settings?'); ?>,
                        resetDone: <?php echo xlj('Reset to defaults'); ?>,
                        enterName: <?php echo xlj('Please enter a context name'); ?>,
                        contextCreated: <?php echo xlj('Custom context created'); ?>,
                        createFailed: <?php echo xlj('Failed to create context'); ?>
                    },

                    init: function () {
                        this.bindEvents();
                        this.applyWidgetVisibility();
                    },

                    bindEvents: function () {
                        const self = this;

                        $('#dashboard-context-selector').on('change', function () {
                            if (self.config.canSwitch) {
                                self.switchContext($(this).val());
                            }
                        });

                        $('#open-context-settings').on('click', function () {
                            self.showSettingsDialog();
                        });

                        // Sync with navbar dropdown if present
                        $(document).on('change', '#nav-context-selector', function() {
                            $('#dashboard-context-selector').val($(this).val());
                        });
                    },

                    showSettingsDialog: function () {
                        const self = this;
                        const contextLabel = this.config.contexts[this.config.currentContext] || this.config.currentContext;
                        const customLabels = this.config.widgetLabels || {};

                        // Build widget toggles with custom labels
                        let widgetToggles = '';
                        for (const [widgetId, label] of Object.entries(this.config.manageableWidgets)) {
                            const displayLabel = customLabels[widgetId] || label;
                            const checked = this.config.widgets[widgetId] === true ? 'checked' : '';
                            const safeId = widgetId.replace(/[^a-zA-Z0-9_-]/g, '_');
                            widgetToggles += '<div class="form-check">' +
                                '<input class="form-check-input widget-toggle" type="checkbox" ' +
                                'id="toggle_' + safeId + '" data-widget-id="' + self.escapeAttr(widgetId) + '" ' + checked + '>' +
                                '<label class="form-check-label" for="toggle_' + safeId + '">' + self.escapeHtml(displayLabel) + '</label>' +
                                '</div>';
                        }

                        // Build dialog
                        var $overlay = $('<div>', {id: 'contextSettingsDialog', class: 'context-dialog-overlay'});
                        var $dialog = $('<div>', {class: 'context-dialog-content'});

                        // Header
                        var $header = $('<div>', {class: 'context-dialog-header'});
                        $header.append($('<h5>', {class: 'm-0'}).append(
                            $('<i>', {class: 'fa fa-cog mr-2'}),
                            document.createTextNode(self.xl.widgetSettings)
                        ));
                        $header.append($('<button>', {type: 'button', class: 'btn btn- btn-cancel close-dialog'}));

                        // Body
                        var $body = $('<div>', {class: 'context-dialog-body'});
                        var $row = $('<div>', {class: 'row'});

                        // Left column - widget toggles
                        var $leftCol = $('<div>', {class: 'col-md-7'});
                        $leftCol.append($('<h6>', {class: 'border-bottom pb-2 mb-3'}).append(
                            document.createTextNode(self.xl.currentContext + ': '),
                            $('<span>', {class: 'text-primary font-weight-bold', id: 'currentContextLabel'}).text(contextLabel)
                        ));
                        $leftCol.append($('<p>', {class: 'text-muted small'}).text(self.xl.selectWidgets));
                        $leftCol.append($('<div>', {class: 'widget-grid'}).html(widgetToggles));

                        // Right column - create custom
                        var $rightCol = $('<div>', {class: 'col-md-5 border-left'});
                        $rightCol.append($('<h6>', {class: 'border-bottom pb-2 mb-3'}).text(self.xl.createCustom));
                        $rightCol.append(
                            $('<div>', {class: 'form-group'}).append(
                                $('<label>', {class: 'font-weight-bold'}).text(self.xl.contextName),
                                $('<input>', {type: 'text', class: 'form-control form-control-sm', id: 'customContextName'})
                            ),
                            $('<div>', {class: 'form-group'}).append(
                                $('<label>').text(self.xl.description),
                                $('<textarea>', {class: 'form-control form-control-sm', id: 'customContextDesc', rows: 2})
                            ),
                            $('<button>', {type: 'button', class: 'btn btn-sm btn-success', id: 'createCustomBtn'}).append(
                                $('<i>', {class: 'fa fa-plus mr-1'}),
                                document.createTextNode(self.xl.createContext)
                            )
                        );

                        $row.append($leftCol, $rightCol);
                        $body.append($row);
                        $body.append($('<hr>'));
                        $body.append(
                            $('<button>', {type: 'button', class: 'btn btn-warning', id: 'resetDefaultsBtn'}).append(
                                $('<i>', {class: 'fa fa-undo mr-1'}),
                                document.createTextNode(self.xl.resetDefaults)
                            )
                        );

                        // Footer
                        var $footer = $('<div>', {class: 'context-dialog-footer'});
                        $footer.append(
                            $('<button>', {type: 'button', class: 'btn btn-secondary mr-2 close-dialog'}).text(self.xl.cancel),
                            $('<button>', {type: 'button', class: 'btn btn-primary', id: 'saveSettingsBtn'}).append(
                                $('<i>', {class: 'fa fa-save mr-1'}),
                                document.createTextNode(self.xl.saveSettings)
                            )
                        );

                        $dialog.append($header, $body, $footer);
                        $overlay.append($dialog);
                        $('body').append($overlay);

                        // Bind dialog events
                        $overlay.find('.close-dialog').on('click', function () {
                            $overlay.remove();
                        });

                        $overlay.on('click', function (e) {
                            if (e.target === this) {
                                $(this).remove();
                            }
                        });

                        $(document).on('keydown.contextDialog', function (e) {
                            if (e.key === 'Escape') {
                                $overlay.remove();
                                $(document).off('keydown.contextDialog');
                            }
                        });

                        $('#saveSettingsBtn').on('click', function () {
                            self.saveSettings();
                            $overlay.remove();
                        });

                        $('#resetDefaultsBtn').on('click', function () {
                            self.resetToDefaults();
                        });

                        $('#createCustomBtn').on('click', function () {
                            self.createCustomContext();
                        });

                        $('.widget-toggle').on('change', function () {
                            self.config.widgets[$(this).data('widget-id')] = $(this).is(':checked');
                        });
                    },

                    switchContext: function (context) {
                        const self = this;
                        if (typeof top.restoreSession === 'function') {
                            top.restoreSession();
                        }

                        $.ajax({
                            url: this.config.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'set_active_context',
                                context: context,
                                csrf_token_form: this.config.csrfToken
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    self.config.currentContext = context;
                                    self.loadContextWidgets(context);
                                    // Sync navbar dropdown if present
                                    $('#nav-context-selector').val(context);
                                    self.showAlert(self.xl.contextSwitched, 'success');
                                } else {
                                    self.showAlert(response.error || self.xl.switchFailed, 'danger');
                                }
                            }
                        });
                    },

                    loadContextWidgets: function (context) {
                        const self = this;
                        if (typeof top.restoreSession === 'function') {
                            top.restoreSession();
                        }

                        $.ajax({
                            url: this.config.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'get_context_widgets',
                                context: context,
                                csrf_token_form: this.config.csrfToken
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    // Merge received widgets with manageableWidgets to ensure all are covered
                                    // Any widget not in response.widgets defaults to false (hidden)
                                    const fullWidgets = {};
                                    for (const widgetId in self.config.manageableWidgets) {
                                        if (self.config.manageableWidgets.hasOwnProperty(widgetId)) {
                                            fullWidgets[widgetId] = response.widgets[widgetId] === true;
                                        }
                                    }
                                    self.config.widgets = fullWidgets;
                                    self.config.widgetOrder = response.widget_order || [];
                                    self.config.widgetLabels = response.widget_labels || {};
                                    self.applyWidgetVisibility();
                                }
                            }
                        });
                    },

                    /**
                     * Find a widget card element by its ID
                     * Tries multiple strategies to locate the card container
                     */
                    findWidgetCard: function (widgetId) {
                        let $widget = null;

                        // Strategy 1: Direct ID match - element has id attribute
                        $widget = $('#' + widgetId);
                        if ($widget.length) {
                            // If the element itself is a card, return it
                            if ($widget.hasClass('card')) {
                                return $widget;
                            }
                            // Otherwise find the closest card parent
                            let $card = $widget.closest('.card');
                            if ($card.length) {
                                return $card;
                            }
                        }

                        // Strategy 2: Button/header with data-target pointing to this ID
                        $widget = $('[data-target="#' + widgetId + '"]').closest('.card');
                        if ($widget.length) {
                            return $widget;
                        }

                        // Strategy 3: Collapse button with href pointing to this ID
                        $widget = $('[href="#' + widgetId + '"]').closest('.card');
                        if ($widget.length) {
                            return $widget;
                        }

                        // Strategy 4: For card_ prefixed IDs, try finding card with matching data attribute
                        if (widgetId.startsWith('card_')) {
                            $widget = $('[data-card-id="' + widgetId + '"]');
                            if ($widget.length) {
                                return $widget.hasClass('card') ? $widget : $widget.closest('.card');
                            }
                        }

                        // Strategy 5: Find by aria-labelledby or aria-controls
                        $widget = $('[aria-labelledby="' + widgetId + '"], [aria-controls="' + widgetId + '"]').closest('.card');
                        if ($widget.length) {
                            return $widget;
                        }

                        // Strategy 6: Search within card headers for matching text or ID references
                        // This catches cards where the ID is on a child element
                        $widget = $('.card').filter(function () {
                            return $(this).find('#' + widgetId).length > 0 ||
                                $(this).find('[id^="' + widgetId + '"]').length > 0;
                        });
                        if ($widget.length) {
                            return $widget.first();
                        }

                        return null;
                    },

                    /**
                     * Apply widget visibility based on current configuration
                     */
                    applyWidgetVisibility: function () {
                        const self = this;
                        // Iterate over ALL manageable widgets to ensure complete coverage
                        // This fixes the bug where widgets weren't being hidden on context switch
                        for (const widgetId in this.config.manageableWidgets) {
                            if (this.config.manageableWidgets.hasOwnProperty(widgetId)) {
                                // Default to hidden (false) if not explicitly set to true
                                const visible = this.config.widgets[widgetId] === true;
                                const $widget = this.findWidgetCard(widgetId);

                                if ($widget && $widget.length) {
                                    if (visible) {
                                        $widget.show().removeClass('context-hidden');
                                    } else {
                                        $widget.hide().addClass('context-hidden');
                                    }
                                } else {
                                    // Debug: log widgets that couldn't be found. By design
                                    // console.log('DashboardContext: Could not find widget:', widgetId);
                                }
                            }
                        }

                        this.applyWidgetOrder();
                        $(document).trigger('dashboardContextChanged', [this.config.currentContext, this.config.widgets]);
                    },

                    /**
                     * Apply widget display order based on current configuration
                     */
                    applyWidgetOrder: function () {
                        const order = this.config.widgetOrder;
                        if (!order || !order.length) return;

                        // Collect all widget cards and their parent container
                        let $parentContainer = null;
                        const cardMap = {};

                        for (let i = 0; i < order.length; i++) {
                            const $card = this.findWidgetCard(order[i]);
                            if ($card && $card.length) {
                                if (!$parentContainer) {
                                    $parentContainer = $card.parent();
                                }
                                cardMap[order[i]] = $card;
                            }
                        }

                        if (!$parentContainer || !$parentContainer.length) return;

                        // Reorder by appending in order
                        for (let i = 0; i < order.length; i++) {
                            if (cardMap[order[i]]) {
                                $parentContainer.append(cardMap[order[i]]);
                            }
                        }
                    },

                    saveSettings: function () {
                        const self = this;
                        const widgets = {};

                        $('#contextSettingsDialog .widget-toggle').each(function () {
                            widgets[$(this).data('widget-id')] = $(this).is(':checked');
                        });

                        if (typeof top.restoreSession === 'function') {
                            top.restoreSession();
                        }

                        $.ajax({
                            url: this.config.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'save_context_widgets',
                                context: this.config.currentContext,
                                widgets: JSON.stringify(widgets),
                                csrf_token_form: this.config.csrfToken
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    self.config.widgets = widgets;
                                    self.applyWidgetVisibility();
                                    self.showAlert(self.xl.settingsSaved, 'success');
                                } else {
                                    self.showAlert(self.xl.saveFailed, 'danger');
                                }
                            }
                        });
                    },

                    resetToDefaults: function () {
                        const self = this;
                        if (!confirm(this.xl.resetConfirm)) {
                            return;
                        }

                        if (typeof top.restoreSession === 'function') {
                            top.restoreSession();
                        }

                        $.ajax({
                            url: this.config.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'reset_to_defaults',
                                context: this.config.currentContext,
                                csrf_token_form: this.config.csrfToken
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    self.loadContextWidgets(self.config.currentContext);
                                    $('#contextSettingsDialog').remove();
                                    self.showAlert(self.xl.resetDone, 'success');
                                }
                            }
                        });
                    },

                    createCustomContext: function () {
                        const self = this;
                        const name = $('#customContextName').val();
                        const description = $('#customContextDesc').val();

                        if (!name) {
                            self.showAlert(self.xl.enterName, 'warning');
                            return;
                        }

                        const widgets = {};
                        $('#contextSettingsDialog .widget-toggle').each(function () {
                            widgets[$(this).data('widget-id')] = $(this).is(':checked');
                        });

                        if (typeof top.restoreSession === 'function') {
                            top.restoreSession();
                        }

                        $.ajax({
                            url: this.config.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'create_custom_context',
                                context_name: name,
                                description: description,
                                widgets: JSON.stringify(widgets),
                                csrf_token_form: this.config.csrfToken
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    self.showAlert(self.xl.contextCreated, 'success');
                                    location.reload();
                                } else {
                                    self.showAlert(response.error || self.xl.createFailed, 'danger');
                                }
                            }
                        });
                    },

                    showAlert: function (message, type) {
                        if (typeof alertMsg === 'function') {
                            alertMsg(message, type === 'danger' ? 5000 : 3000, type);
                            return;
                        }

                        const alertClass = 'alert-' + (type || 'info');
                        const $alert = $('<div>', {
                            class: 'alert ' + alertClass + ' alert-dismissible',
                            style: 'position:fixed;top:10px;right:10px;z-index:10000;min-width:200px;'
                        }).text(message).append(
                            $('<button>', {type: 'button', class: 'close ml-2'}).html('&times;').on('click', function () {
                                $(this).parent().remove();
                            })
                        );
                        $('body').append($alert);
                        setTimeout(function () {
                            $alert.fadeOut(function () {
                                $(this).remove();
                            });
                        }, 4000);
                    },

                    escapeHtml: function (text) {
                        if (!text) return '';
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    },

                    escapeAttr: function (text) {
                        if (!text) return '';
                        return text.replace(/[&<>"']/g, function (m) {
                            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[m];
                        });
                    }
                };

                $(function () {
                    DashboardContext.init();
                });
            })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }
}
