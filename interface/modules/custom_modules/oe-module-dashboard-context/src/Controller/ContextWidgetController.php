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

class ContextWidgetController
{
    private DashboardContextService $contextService;
    private int $userId;

    public function __construct()
    {
        $this->contextService = new DashboardContextService();
        $this->userId = (int)($_SESSION['authUserID'] ?? 0);
    }

    /**
     * Render the context manager widget
     *
     * @return string
     */
    public function renderWidget(): string
    {
        if ($this->userId <= 0) {
            return '';
        }

        // Check if widget should be shown
        if (!($GLOBALS['dashboard_context_show_widget'] ?? true)) {
            return '';
        }

        $activeContext = $this->contextService->getActiveContext($this->userId);
        $contexts = $this->contextService->getAvailableContexts();
        $customContexts = $this->contextService->getUserCustomContexts($this->userId);
        $manageableWidgets = $this->contextService->getManageableWidgets();
        $currentWidgets = $this->contextService->getFullWidgetConfig($this->userId);
        $isLocked = $this->contextService->isUserContextLocked($this->userId);
        $canSwitch = ($GLOBALS['dashboard_context_user_can_switch'] ?? true) && !$isLocked;

        $csrfToken = CsrfUtils::collectCsrfToken();
        $webRoot = $GLOBALS['webroot'] ?? '';
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
                background: var(--light);
                z-index: 1050;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .context-dialog-content {
                background: var(--light);
                border-radius: var(--borderRadius, 4px);
                box-shadow: var(--boxshadow, 0 5px 15px rgba(0,0,0,0.5));
                width: 90%;
                max-width: 800px;
                max-height: 90vh;
                overflow-y: auto;
            }
            .context-dialog-header {
                padding: 1rem;
                border-bottom: 1px solid #dee2e6;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .context-dialog-body {
                padding: 1rem;
            }
            .context-dialog-footer {
                padding: 1rem;
                border-top: 1px solid #dee2e6;
                text-align: right;
            }
            .widget-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 8px;
            }
        </style>

        <div class="" id="dashboard-context-widget">
            <div class="col-6 offset-md-3 p-1">
                <h6 class="card-title text-bold mb-0 d-flex p-1 justify-content-between">
                    <i class="fa fa-sliders-h mx-1 mt-1"></i>
                    <?php echo xlt('Select and Change Care Context'); ?>
                    <?php if ($canSwitch) : ?>
                    <span>
                        <button type="button" class="btn btn-sm btn-edit py-0 pr-0" id="open-context-settings" title="<?php echo xla('Configure Widgets'); ?>">
                        </button>
                    </span>
                    <?php endif; ?>
                </h6>
                <?php if ($isLocked) : ?>
                <div class="alert alert-warning py-1 px-2 mb-2 small">
                    <i class="fa fa-lock mr-1"></i>
                    <?php echo xlt('Context locked by administrator'); ?>
                </div>
                <?php endif; ?>

                <select id="dashboard-context-selector" class="form-control form-control-sm" <?php echo $canSwitch ? '' : 'disabled'; ?>>
                    <?php foreach ($contexts as $key => $label) : ?>
                    <option value="<?php echo attr($key); ?>" <?php echo $key === $activeContext ? 'selected' : ''; ?>>
                        <?php echo text($label); ?>
                    </option>
                    <?php endforeach; ?>

                    <?php if (!empty($customContexts)) : ?>
                    <option disabled>──────────</option>
                    <?php foreach ($customContexts as $ctx) : ?>
                    <option value="<?php echo attr($ctx['context_key']); ?>" <?php echo $ctx['context_key'] === $activeContext ? 'selected' : ''; ?>>
                        <?php echo text($ctx['context_name']); ?>
                    </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <script>
        (function($) {
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
                    canSwitch: <?php echo js_escape($canSwitch); ?>
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
                    this.applyWidgetVisibility();
                },

                bindEvents: function() {
                    const self = this;

                    $('#dashboard-context-selector').on('change', function() {
                        if (self.config.canSwitch) {
                            self.switchContext($(this).val());
                        }
                    });

                    $('#open-context-settings').on('click', function() {
                        self.showSettingsDialog();
                    });
                },

                showSettingsDialog: function() {
                    const self = this;
                    const contextLabel = this.config.contexts[this.config.currentContext] || this.config.currentContext;
                    // Build widget toggles
                    let widgetToggles = '';
                    for (const [widgetId, label] of Object.entries(this.config.manageableWidgets)) {
                        const checked = this.config.widgets[widgetId] === true ? 'checked' : '';
                        const safeId = widgetId.replace(/[^a-zA-Z0-9_-]/g, '_');
                        widgetToggles += '<div class="form-check">' +
                            '<input class="form-check-input widget-toggle" type="checkbox" ' +
                            'id="toggle_' + safeId + '" data-widget-id="' + self.escapeAttr(widgetId) + '" ' + checked + '>' +
                            '<label class="form-check-label" for="toggle_' + safeId + '">' + self.escapeHtml(label) + '</label>' +
                            '</div>';
                    }
                    // Build dialog
                    const $overlay = $('<div>', {id: 'contextSettingsDialog', class: 'context-dialog-overlay'});
                    const $dialog = $('<div>', {class: 'context-dialog-content'});
                    // Header
                    var $header = $('<div>', { class: 'context-dialog-header' });
                    $header.append($('<h5>', { class: 'm-0' }).append(
                        $('<i>', { class: 'fa fa-cog mr-2' }),
                        document.createTextNode(self.xl.widgetSettings)
                    ));
                    $header.append($('<button>', { type: 'button', class: 'btn btn- btn-cancel close-dialog' }));
                    // Body
                    var $body = $('<div>', { class: 'context-dialog-body' });
                    var $row = $('<div>', { class: 'row' });
                    // Left column - widget toggles
                    var $leftCol = $('<div>', { class: 'col-md-7' });
                    $leftCol.append($('<h6>', { class: 'border-bottom pb-2 mb-3' }).append(
                        document.createTextNode(self.xl.currentContext + ': '),
                        $('<span>', { class: 'text-primary font-weight-bold', id: 'currentContextLabel' }).text(contextLabel)
                    ));
                    $leftCol.append($('<p>', { class: 'text-muted small' }).text(self.xl.selectWidgets));
                    $leftCol.append($('<div>', { class: 'widget-grid' }).html(widgetToggles));
                    // Right column - create custom
                    var $rightCol = $('<div>', { class: 'col-md-5 border-left' });
                    $rightCol.append($('<h6>', { class: 'border-bottom pb-2 mb-3' }).text(self.xl.createCustom));
                    $rightCol.append(
                        $('<div>', { class: 'form-group' }).append(
                            $('<label>', { class: 'font-weight-bold' }).text(self.xl.contextName),
                            $('<input>', { type: 'text', class: 'form-control form-control-sm', id: 'customContextName' })
                        ),
                        $('<div>', { class: 'form-group' }).append(
                            $('<label>').text(self.xl.description),
                            $('<textarea>', { class: 'form-control form-control-sm', id: 'customContextDesc', rows: 2 })
                        ),
                        $('<button>', { type: 'button', class: 'btn btn-sm btn-success', id: 'createCustomBtn' }).append(
                            $('<i>', { class: 'fa fa-plus mr-1' }),
                            document.createTextNode(self.xl.createContext)
                        )
                    );

                    $row.append($leftCol, $rightCol);
                    $body.append($row);
                    $body.append($('<hr>'));
                    $body.append(
                        $('<button>', { type: 'button', class: 'btn btn-warning', id: 'resetDefaultsBtn' }).append(
                            $('<i>', { class: 'fa fa-undo mr-1' }),
                            document.createTextNode(self.xl.resetDefaults)
                        )
                    );

                    // Footer
                    var $footer = $('<div>', { class: 'context-dialog-footer' });
                    $footer.append(
                        $('<button>', { type: 'button', class: 'btn btn-secondary mr-2 close-dialog' }).text(self.xl.cancel),
                        $('<button>', { type: 'button', class: 'btn btn-primary', id: 'saveSettingsBtn' }).append(
                            $('<i>', { class: 'fa fa-save mr-1' }),
                            document.createTextNode(self.xl.saveSettings)
                        )
                    );

                    $dialog.append($header, $body, $footer);
                    $overlay.append($dialog);
                    $('body').append($overlay);
                    // Bind dialog events
                    $overlay.find('.close-dialog').on('click', function() {
                        $overlay.remove();
                    });

                    $overlay.on('click', function(e) {
                        if (e.target === this) {
                            $(this).remove();
                        }
                    });

                    $(document).on('keydown.contextDialog', function(e) {
                        if (e.key === 'Escape') {
                            $overlay.remove();
                            $(document).off('keydown.contextDialog');
                        }
                    });

                    $('#saveSettingsBtn').on('click', function() {
                        self.saveSettings();
                        $overlay.remove();
                    });

                    $('#resetDefaultsBtn').on('click', function() {
                        self.resetToDefaults();
                    });

                    $('#createCustomBtn').on('click', function() {
                        self.createCustomContext();
                    });

                    $('.widget-toggle').on('change', function() {
                        self.config.widgets[$(this).data('widget-id')] = $(this).is(':checked');
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
                                // Merge received widgets with manageableWidgets to ensure all are covered
                                // Any widget not in response.widgets defaults to false (hidden)
                                const fullWidgets = {};
                                for (const widgetId in self.config.manageableWidgets) {
                                    if (self.config.manageableWidgets.hasOwnProperty(widgetId)) {
                                        fullWidgets[widgetId] = response.widgets[widgetId] === true;
                                    }
                                }
                                self.config.widgets = fullWidgets;
                                self.applyWidgetVisibility();
                            }
                        }
                    });
                },

                /**
                 * Find a widget card element by its ID
                 * Tries multiple strategies to locate the card container
                 */
                findWidgetCard: function(widgetId) {
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
                    $widget = $('.card').filter(function() {
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
                 * 
                 * IMPORTANT: This iterates over ALL manageable widgets, not just
                 * what's in config.widgets, to ensure widgets are properly hidden
                 * when switching contexts.
                 */
                applyWidgetVisibility: function() {
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
                                // Debug: log widgets that couldn't be found
                                console.log('DashboardContext: Could not find widget:', widgetId);
                            }
                        }
                    }

                    $(document).trigger('dashboardContextChanged', [this.config.currentContext, this.config.widgets]);
                },

                saveSettings: function() {
                    const self = this;
                    const widgets = {};

                    $('#contextSettingsDialog .widget-toggle').each(function() {
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
                                $('#contextSettingsDialog').remove();
                                self.showAlert(self.xl.resetDone, 'success');
                            }
                        }
                    });
                },

                createCustomContext: function() {
                    const self = this;
                    const name = $('#customContextName').val();
                    const description = $('#customContextDesc').val();

                    if (!name) {
                        self.showAlert(self.xl.enterName, 'warning');
                        return;
                    }

                    const widgets = {};
                    $('#contextSettingsDialog .widget-toggle').each(function() {
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
                        style: 'position:fixed;top:10px;right:10px;z-index:10000;min-width:200px;'
                    }).text(message).append(
                        $('<button>', { type: 'button', class: 'close ml-2' }).html('&times;').on('click', function() {
                            $(this).parent().remove();
                        })
                    );
                    $('body').append($alert);
                    setTimeout(function() {
                        $alert.fadeOut(function() { $(this).remove(); });
                    }, 4000);
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
                DashboardContext.init();
            });

        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }
}
