/**
 * Dashboard Context Manager - User JavaScript
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function(window, $) {
    'use strict';

    window.DashboardContextManager = {
        config: {
            ajaxUrl: '',
            csrfToken: '',
            currentContext: 'primary_care',
            contexts: {},
            widgets: {},
            manageableWidgets: {},
            customContexts: [],
            canSwitch: true,
            widgetOrder: [],
            widgetLabels: {}
        },

        init: function (options) {
            this.config = $.extend({}, this.config, options);
            this.bindEvents();
            this.applyWidgetVisibility();
        },

        bindEvents: function () {
            const self = this;

            $(document).on('change', '#dashboard-context-selector', function () {
                if (self.config.canSwitch) {
                    self.switchContext($(this).val());
                }
            });

            $(document).on('change', '.widget-visibility-toggle', function () {
                const widgetId = $(this).data('widget-id');
                const visible = $(this).is(':checked');
                self.toggleWidget(widgetId, visible);
            });

            $(document).on('click', '#save-context-settings', function () {
                self.saveCurrentSettings();
            });

            $(document).on('click', '#reset-context-defaults', function () {
                self.resetToDefaults();
            });

            $(document).on('click', '#open-context-settings', function () {
                self.openSettingsModal();
            });

            $(document).on('click', '#create-custom-context', function () {
                self.createCustomContext();
            });
        },

        switchContext: function (context) {
            const self = this;

            top.restoreSession();

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
                        self.showNotification('Context switched', 'success', '', '', true);
                    } else {
                        self.showNotification(response.error || 'Failed to switch context', 'error', '', '', true);
                    }
                },
                error: function () {
                    self.showNotification('Failed to switch context', 'error', '', '', true);
                }
            });
        },

        loadContextWidgets: function (context) {
            const self = this;

            top.restoreSession();

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
                        self.config.widgets = response.widgets;
                        self.config.widgetOrder = response.widget_order || [];
                        self.config.widgetLabels = response.widget_labels || {};
                        self.applyWidgetVisibility();
                        self.updateSettingsModal();
                    }
                }
            });
        },

        toggleWidget: function (widgetId, visible) {
            this.config.widgets[widgetId] = visible;
            this.applyWidgetVisibility();
        },

        applyWidgetVisibility: function () {
            const widgets = this.config.widgets;

            for (const widgetId in widgets) {
                if (Object.prototype.hasOwnProperty.call(widgets, widgetId)) {
                    const visible = widgets[widgetId];
                    const $widget = $('#' + widgetId).closest('.card');

                    if ($widget.length) {
                        if (visible) {
                            $widget.removeClass('context-hidden').show();
                        } else {
                            $widget.addClass('context-hidden').hide();
                        }
                    }
                }
            }

            this.applyWidgetOrder();
            $(document).trigger('dashboardContextChanged', [this.config.currentContext, widgets]);
        },

        applyWidgetOrder: function () {
            const order = this.config.widgetOrder;
            if (!order || !order.length) return;

            let $parentContainer = null;
            const cardMap = {};

            for (let i = 0; i < order.length; i++) {
                const $card = $('#' + order[i]).closest('.card');
                if ($card.length) {
                    if (!$parentContainer) {
                        $parentContainer = $card.parent();
                    }
                    cardMap[order[i]] = $card;
                }
            }

            if (!$parentContainer || !$parentContainer.length) return;

            for (let i = 0; i < order.length; i++) {
                if (cardMap[order[i]]) {
                    $parentContainer.append(cardMap[order[i]]);
                }
            }
        },

        saveCurrentSettings: function () {
            const self = this;
            const widgets = {};

            top.restoreSession();

            $('.widget-visibility-toggle').each(function () {
                widgets[$(this).data('widget-id')] = $(this).is(':checked');
            });

            top.restoreSession();
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
                        self.showNotification('Settings saved', 'success', '', '', true);
                        $('#contextSettingsModal').modal('hide');
                    } else {
                        self.showNotification('Failed to save settings', 'error', '', '', true);
                    }
                }
            });
        },

        resetToDefaults: function () {
            const self = this;

            if (!confirm('Reset this context to default settings?')) {
                return;
            }

            top.restoreSession();

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
                        self.showNotification('Reset to defaults', 'success', '', '', true);
                    }
                }
            });
        },

        openSettingsModal: function () {
            this.updateSettingsModal();
            $('#contextSettingsModal').modal('show');
        },

        updateSettingsModal: function () {
            const $container = $('#widget-toggles-container');
            if (!$container.length) return;

            $container.empty();

            const widgets = this.config.manageableWidgets;
            const currentSettings = this.config.widgets;
            const customLabels = this.config.widgetLabels || {};

            for (const widgetId in widgets) {
                if (Object.prototype.hasOwnProperty.call(widgets, widgetId)) {
                    const label = customLabels[widgetId] || widgets[widgetId];
                    const isVisible = currentSettings[widgetId] !== false;

                    const $item = $(`
                        <div class="form-check mb-2">
                            <input class="form-check-input widget-visibility-toggle"
                                   type="checkbox"
                                   id="toggle-${widgetId}"
                                   data-widget-id="${widgetId}"
                                   ${isVisible ? 'checked' : ''}>
                            <label class="form-check-label" for="toggle-${widgetId}">
                                ${this.escapeHtml(label)}
                            </label>
                        </div>
                    `);

                    $container.append($item);
                }
            }

            $('#current-context-label').text(this.getContextLabel(this.config.currentContext));
        },

        getContextLabel: function (contextKey) {
            if (this.config.contexts[contextKey]) {
                return this.config.contexts[contextKey];
            }

            const custom = this.config.customContexts?.find(c => c.context_key === contextKey);
            if (custom) {
                return custom.context_name;
            }

            return contextKey;
        },

        createCustomContext: function () {
            const self = this;
            const name = $('#custom-context-name').val();
            const description = $('#custom-context-description').val();

            if (!name) {
                this.showNotification('Please enter a context name', 'warning', '', '', true);
                return;
            }

            const widgets = {};
            $('.widget-visibility-toggle').each(function () {
                widgets[$(this).data('widget-id')] = $(this).is(':checked');
            });

            top.restoreSession();

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
                        self.showNotification('Custom context created', 'success', '', '', true);
                        location.reload();
                    } else {
                        self.showNotification(response.error || 'Failed to create context', 'error', '', '', true);
                    }
                }
            });
        },

        showNotification: function (message, type) {
            if (typeof alertMsg === 'function') {
                alertMsg(message, type === 'error' ? 3000 : 2000, '', true);
                return;
            }

            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            }[type] || 'alert-info';

            const $alert = $(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert"
                     style="position:fixed;top:10px;right:10px;z-index:9999;">
                    ${this.escapeHtml(message)}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            `);

            $('body').append($alert);
            setTimeout(function () {
                $alert.alert('close');
            }, 3000);
        },

        escapeHtml: function (text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        isWidgetVisible: function (widgetId) {
            return this.config.widgets[widgetId] !== false;
        },

        getCurrentContext: function () {
            return this.config.currentContext;
        }
    };

})(window, jQuery);
