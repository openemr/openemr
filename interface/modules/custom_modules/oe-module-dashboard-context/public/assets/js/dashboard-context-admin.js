/**
 * Dashboard Context Manager - Admin JavaScript
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const ContextAdmin = {
    config: {
        ajaxUrl: '',
        csrfToken: '',
        contexts: [],
        widgets: {},
        userTypes: {},
        facilities: [],
        widgetOrders: {},
        widgetLabels: {}
    },

    init: function(options) {
        this.config = $.extend({}, this.config, options);
        this.loadAdminConfig();
        this.bindEvents();
    },

    bindEvents: function() {
        const self = this;

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('href');
            if (target === '#users') self.loadUsers();
            else if (target === '#roles') self.loadRoleDefaults();
            else if (target === '#stats') self.loadStats();
            else if (target === '#audit') self.loadAuditLog();
        });

        $('#btnCreateContext').on('click', () => self.showContextModal());
        $('#btnSaveContext').on('click', () => self.saveContext());
        $('#filterFacility, #filterUserType').on('change', () => self.loadUsers());

        let searchTimeout;
        $('#filterSearch').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => self.loadUsers(), 300);
        });

        $('#selectAllUsers').on('change', function() {
            $('.user-select').prop('checked', $(this).is(':checked'));
        });

        $('#btnBulkAssign').on('click', () => self.showBulkAssignModal());
        $('#btnConfirmBulkAssign').on('click', () => self.bulkAssign());
        $('#btnConfirmAssign').on('click', () => self.assignContext());
        $('#btnExportContexts').on('click', () => self.exportContexts());
        $('#btnImportContexts').on('click', () => self.importContexts());
        $('#btnRefreshAudit').on('click', () => self.loadAuditLog());
    },

    loadAdminConfig: function() {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: { action: 'get_admin_config', csrf_token_form: this.config.csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    self.config.contexts = response.contexts;
                    self.config.widgets = response.widgets;
                    self.config.userTypes = response.user_types;
                    self.config.facilities = response.facilities;
                    self.config.widgetOrders = response.widget_orders || {};
                    self.config.widgetLabels = response.widget_labels || {};
                    self.renderContexts();
                    self.populateFilters();
                }
            }
        });
    },

    renderContexts: function() {
        const $container = $('#contextsContainer').empty();

        this.config.contexts.forEach(ctx => {
            const isSystem = ctx.is_system;
            const cardClass = isSystem ? 'system-context' : 'custom-context';
            const badgeClass = isSystem ? 'badge-system' : 'badge-custom';
            const badgeText = isSystem ? 'System' : 'Custom';

            const $card = $(`
                <div class="col-md-4 mb-3">
                    <div class="card context-card ${cardClass}">
                        <div class="card-body">
                            <h6 class="card-title">
                                ${this.escapeHtml(ctx.context_name)}
                                <span class="badge ${badgeClass} float-right">${badgeText}</span>
                            </h6>
                            <p class="card-text small text-muted">${this.escapeHtml(ctx.description || '')}</p>
                            <p class="card-text small"><strong>Key:</strong> ${this.escapeHtml(ctx.context_key)}</p>
                            ${!isSystem ? `
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-edit-context" data-id="${ctx.id}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-outline-danger btn-delete-context" data-id="${ctx.id}"><i class="fa fa-trash"></i></button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `);
            $container.append($card);
        });

        $('.btn-edit-context').on('click', (e) => this.editContext($(e.currentTarget).data('id')));
        $('.btn-delete-context').on('click', (e) => this.deleteContext($(e.currentTarget).data('id')));
    },

    showContextModal: function(contextData = null) {
        const isEdit = contextData !== null;

        $('#contextModalTitle').text(isEdit ? 'Edit Context' : 'Create Context');
        $('#contextId').val(isEdit ? contextData.id : '');
        $('#contextName').val(isEdit ? contextData.context_name : '');
        $('#contextKey').val(isEdit ? contextData.context_key : '').prop('readonly', isEdit);
        $('#contextDescription').val(isEdit ? contextData.description : '');
        $('#contextGlobal').prop('checked', isEdit ? contextData.is_global : true);

        const $grid = $('#widgetConfigGrid').empty();
        const widgetConfig = isEdit && contextData.widget_config
            ? (typeof contextData.widget_config === 'string' ? JSON.parse(contextData.widget_config) : contextData.widget_config)
            : {};

        // Get existing order and labels for this context
        const contextKey = isEdit ? contextData.context_key : '';
        const existingOrder = (contextKey && this.config.widgetOrders[contextKey]) ? this.config.widgetOrders[contextKey] : [];
        const existingLabels = (contextKey && this.config.widgetLabels[contextKey]) ? this.config.widgetLabels[contextKey] : {};

        // Build ordered widget list
        const widgetEntries = Object.entries(this.config.widgets);
        if (existingOrder.length > 0) {
            widgetEntries.sort(function(a, b) {
                const idxA = existingOrder.indexOf(a[0]);
                const idxB = existingOrder.indexOf(b[0]);
                const posA = idxA >= 0 ? idxA : 9999;
                const posB = idxB >= 0 ? idxB : 9999;
                return posA - posB;
            });
        }

        for (const [widgetId, widgetLabel] of widgetEntries) {
            const isActive = widgetConfig[widgetId] !== false;
            const customLabel = existingLabels[widgetId] || '';
            $grid.append(`
                <div class="widget-item ${isActive ? 'active' : ''}" data-widget-id="${widgetId}" draggable="true">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-grip-vertical text-muted mr-2" style="cursor:grab;" title="Drag to reorder"></i>
                        <div class="form-check mb-0 flex-grow-1">
                            <input type="checkbox" class="form-check-input widget-toggle" id="widget-${widgetId}" data-widget="${widgetId}" ${isActive ? 'checked' : ''}>
                            <label class="form-check-label" for="widget-${widgetId}">${this.escapeHtml(widgetLabel)}</label>
                        </div>
                        <input type="text" class="form-control form-control-sm widget-label-input ml-2" data-widget="${widgetId}" placeholder="Custom label" value="${this.escapeHtml(customLabel)}" style="max-width:150px;font-size:0.8rem;">
                    </div>
                </div>
            `);
        }

        // Widget toggle active class
        $('.widget-toggle').on('change', function() {
            $(this).closest('.widget-item').toggleClass('active', $(this).is(':checked'));
        });

        // Drag-and-drop reordering
        this.initDragReorder($grid);

        $('#contextModal').modal('show');
    },

    initDragReorder: function($container) {
        let draggedEl = null;

        $container.on('dragstart', '.widget-item', function(e) {
            draggedEl = this;
            $(this).css('opacity', '0.4');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        });

        $container.on('dragend', '.widget-item', function() {
            $(this).css('opacity', '');
            $container.find('.widget-item').removeClass('drag-over');
        });

        $container.on('dragover', '.widget-item', function(e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            $(this).addClass('drag-over');
        });

        $container.on('dragleave', '.widget-item', function() {
            $(this).removeClass('drag-over');
        });

        $container.on('drop', '.widget-item', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            if (draggedEl !== this) {
                const $dragged = $(draggedEl);
                const $target = $(this);
                // Insert before or after based on position
                const targetRect = this.getBoundingClientRect();
                const midY = targetRect.top + targetRect.height / 2;
                if (e.originalEvent.clientY < midY) {
                    $dragged.insertBefore($target);
                } else {
                    $dragged.insertAfter($target);
                }
            }
        });
    },

    saveContext: function() {
        const self = this;
        const contextId = $('#contextId').val();
        const isEdit = contextId !== '';

        const widgetConfig = {};
        $('.widget-toggle').each(function() {
            widgetConfig[$(this).data('widget')] = $(this).is(':checked');
        });

        // Collect widget order from DOM
        const widgetOrder = [];
        $('#widgetConfigGrid .widget-item').each(function() {
            widgetOrder.push($(this).data('widget-id'));
        });

        // Collect custom labels
        const widgetLabels = {};
        $('.widget-label-input').each(function() {
            const val = $(this).val().trim();
            if (val) {
                widgetLabels[$(this).data('widget')] = val;
            }
        });

        const data = {
            action: isEdit ? 'update_context' : 'create_context',
            csrf_token_form: this.config.csrfToken,
            context_name: $('#contextName').val(),
            description: $('#contextDescription').val(),
            widget_config: JSON.stringify(widgetConfig),
            is_global: $('#contextGlobal').is(':checked') ? '1' : '0'
        };

        if (isEdit) data.context_id = contextId;
        else data.context_key = $('#contextKey').val();

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Determine context key for saving order/labels
                    const ctxKey = isEdit ? ($('#contextKey').val() || '') : (response.context_key || $('#contextKey').val() || '');
                    // Save widget order
                    if (ctxKey && widgetOrder.length > 0) {
                        self.saveWidgetOrder(ctxKey, widgetOrder);
                    }
                    // Save widget labels
                    if (ctxKey) {
                        self.saveWidgetLabels(ctxKey, widgetLabels);
                    }

                    $('#contextModal').modal('hide');
                    self.loadAdminConfig();
                    self.showAlert(isEdit ? 'Context updated' : 'Context created', 'success');
                } else {
                    self.showAlert(response.error || 'Error saving context', 'danger');
                }
            }
        });
    },

    saveWidgetOrder: function(contextKey, order) {
        top.restoreSession();
        $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_widget_order',
                context_key: contextKey,
                widget_order: JSON.stringify(order),
                csrf_token_form: this.config.csrfToken
            },
            dataType: 'json'
        });
    },

    saveWidgetLabels: function(contextKey, labels) {
        const self = this;
        const existingLabels = this.config.widgetLabels[contextKey] || {};

        // Save new/updated labels
        for (const [widgetId, label] of Object.entries(labels)) {
            if (existingLabels[widgetId] !== label) {
                top.restoreSession();
                $.ajax({
                    url: self.config.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'save_widget_label',
                        context_key: contextKey,
                        widget_id: widgetId,
                        label: label,
                        csrf_token_form: self.config.csrfToken
                    },
                    dataType: 'json'
                });
            }
        }

        // Delete removed labels
        for (const widgetId of Object.keys(existingLabels)) {
            if (!labels[widgetId]) {
                top.restoreSession();
                $.ajax({
                    url: self.config.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'delete_widget_label',
                        context_key: contextKey,
                        widget_id: widgetId,
                        csrf_token_form: self.config.csrfToken
                    },
                    dataType: 'json'
                });
            }
        }
    },

    editContext: function(contextId) {
        const context = this.config.contexts.find(c => c.id == contextId);
        if (context) this.showContextModal(context);
    },

    deleteContext: function(contextId) {
        if (!confirm('Are you sure you want to delete this context?')) return;

        const self = this;
        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: { action: 'delete_context', context_id: contextId, csrf_token_form: this.config.csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    self.loadAdminConfig();
                    self.showAlert('Context deleted', 'success');
                }
            }
        });
    },

    populateFilters: function() {
        const $facilitySelect = $('#filterFacility');
        this.config.facilities.forEach(f => {
            $facilitySelect.append(`<option value="${f.id}">${this.escapeHtml(f.name)}</option>`);
        });

        const $typeSelect = $('#filterUserType');
        for (const [key, label] of Object.entries(this.config.userTypes)) {
            $typeSelect.append(`<option value="${key}">${this.escapeHtml(label)}</option>`);
        }

        this.populateContextSelect($('#assignContext'));
        this.populateContextSelect($('#bulkContext'));
    },

    populateContextSelect: function($select) {
        $select.empty();
        this.config.contexts.forEach(ctx => {
            $select.append(`<option value="${ctx.context_key}">${this.escapeHtml(ctx.context_name)}</option>`);
        });
    },

    loadUsers: function() {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_users',
                facility_id: $('#filterFacility').val(),
                user_type: $('#filterUserType').val(),
                search: $('#filterSearch').val(),
                csrf_token_form: this.config.csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) self.renderUsers(response.users);
            }
        });
    },

    renderUsers: function(users) {
        const $tbody = $('#usersTableBody').empty();

        users.forEach(user => {
            const context = this.config.contexts.find(c => c.context_key === user.active_context);
            const contextLabel = context ? context.context_name : user.active_context;
            const isLocked = user.is_locked;

            $tbody.append(`
                <tr class="${isLocked ? 'user-row locked' : 'user-row'}">
                    <td><input type="checkbox" class="user-select" value="${user.id}"></td>
                    <td>${this.escapeHtml(user.name)} <small class="text-muted">(${this.escapeHtml(user.username)})</small></td>
                    <td>${this.escapeHtml(user.facility_name || '-')}</td>
                    <td>${this.escapeHtml(this.config.userTypes[user.user_type] || user.user_type || '-')}</td>
                    <td>${this.escapeHtml(contextLabel)}</td>
                    <td>
                        ${isLocked ? '<i class="fa fa-lock text-warning" title="Locked"></i>' : ''}
                        ${user.has_custom_config ? '<i class="fa fa-cog text-info" title="Custom settings"></i>' : ''}
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-assign-user" data-user='${JSON.stringify(user)}'><i class="fa fa-user-cog"></i></button>
                        ${isLocked ? `<button class="btn btn-sm btn-outline-warning btn-unlock-user" data-user-id="${user.id}"><i class="fa fa-unlock"></i></button>` : ''}
                    </td>
                </tr>
            `);
        });

        $('.btn-assign-user').on('click', (e) => this.showAssignModal($(e.currentTarget).data('user')));
        $('.btn-unlock-user').on('click', (e) => this.unlockUser($(e.currentTarget).data('user-id')));
    },

    showAssignModal: function(user) {
        $('#assignUserId').val(user.id);
        $('#assignUserName').text(user.name);
        $('#assignContext').val(user.active_context);
        $('#assignLock').prop('checked', false);
        $('#assignModal').modal('show');
    },

    assignContext: function() {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'assign_context_to_user',
                user_id: $('#assignUserId').val(),
                context_key: $('#assignContext').val(),
                lock_context: $('#assignLock').is(':checked') ? '1' : '0',
                csrf_token_form: this.config.csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#assignModal').modal('hide');
                    self.loadUsers();
                    self.showAlert('Context assigned', 'success');
                }
            }
        });
    },

    showBulkAssignModal: function() {
        const selected = $('.user-select:checked');
        if (selected.length === 0) {
            this.showAlert('Please select at least one user', 'warning');
            return;
        }
        $('#bulkSelectedCount').text(selected.length);
        $('#bulkAssignModal').modal('show');
    },

    bulkAssign: function() {
        const self = this;
        const userIds = [];
        $('.user-select:checked').each(function() { userIds.push($(this).val()); });

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'bulk_assign_context',
                user_ids: JSON.stringify(userIds),
                context_key: $('#bulkContext').val(),
                lock_context: $('#bulkLock').is(':checked') ? '1' : '0',
                csrf_token_form: this.config.csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#bulkAssignModal').modal('hide');
                    self.loadUsers();
                    self.showAlert(response.success_count + ' of ' + response.total_count + ' users updated', 'success');
                }
            }
        });
    },

    unlockUser: function(userId) {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: { action: 'remove_user_assignment', user_id: userId, csrf_token_form: this.config.csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    self.loadUsers();
                    self.showAlert('User unlocked', 'success');
                }
            }
        });
    },

    loadRoleDefaults: function() {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: { action: 'get_role_defaults', csrf_token_form: this.config.csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) self.renderRoleDefaults(response.defaults, response.user_types);
            }
        });
    },

    renderRoleDefaults: function(defaults, userTypes) {
        const self = this;
        const $tbody = $('#roleDefaultsBody').empty();
        const defaultMap = {};
        defaults.forEach(d => { defaultMap[d.role_type] = d.context_key; });

        for (const [roleType, roleLabel] of Object.entries(userTypes)) {
            const currentContext = defaultMap[roleType] || '';
            let contextOptions = '<option value="">-- No Default --</option>';
            this.config.contexts.forEach(ctx => {
                contextOptions += `<option value="${ctx.context_key}" ${ctx.context_key === currentContext ? 'selected' : ''}>${this.escapeHtml(ctx.context_name)}</option>`;
            });

            $tbody.append(`
                <tr>
                    <td>${this.escapeHtml(roleLabel)}</td>
                    <td><select class="form-control form-control-sm role-default-select" data-role="${roleType}">${contextOptions}</select></td>
                    <td><button class="btn btn-sm btn-primary btn-save-role-default" data-role="${roleType}"><i class="fa fa-save"></i></button></td>
                </tr>
            `);
        }

        $('.btn-save-role-default').on('click', function() {
            const roleType = $(this).data('role');
            const contextKey = $(`.role-default-select[data-role="${roleType}"]`).val();
            self.saveRoleDefault(roleType, contextKey);
        });
    },

    saveRoleDefault: function(roleType, contextKey) {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: { action: 'set_role_default', role_type: roleType, context_key: contextKey, csrf_token_form: this.config.csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) self.showAlert('Role default saved', 'success');
            }
        });
    },

    loadStats: function() {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: { action: 'get_usage_stats', csrf_token_form: this.config.csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) self.renderStats(response.stats);
            }
        });
    },

    renderStats: function(stats) {
        const $container = $('#statsContainer').empty();

        stats.forEach(stat => {
            $container.append(`
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="stat-number">${stat.user_count}</div>
                        <div class="stat-label">${this.escapeHtml(stat.context_label)}</div>
                    </div>
                </div>
            `);
        });
    },

    loadAuditLog: function() {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_audit_log',
                date_from: $('#auditDateFrom').val(),
                date_to: $('#auditDateTo').val(),
                audit_action: $('#auditAction').val(),
                csrf_token_form: this.config.csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) self.renderAuditLog(response.logs);
            }
        });
    },

    renderAuditLog: function(logs) {
        const $tbody = $('#auditLogBody').empty();

        logs.forEach(log => {
            $tbody.append(`
                <tr>
                    <td>${this.escapeHtml(log.created_at)}</td>
                    <td>${this.escapeHtml(log.user_fname + ' ' + log.user_lname)}</td>
                    <td><span class="badge badge-secondary">${this.escapeHtml(log.action)}</span></td>
                    <td>${this.escapeHtml(log.old_context || '-')}</td>
                    <td>${this.escapeHtml(log.new_context || '-')}</td>
                    <td>${this.escapeHtml(log.performer_username || '-')}</td>
                </tr>
            `);
        });
    },

    exportContexts: function() {
        const self = this;

        top.restoreSession();
                $.ajax({
            url: this.config.ajaxUrl,
            type: 'POST',
            data: { action: 'export_contexts', csrf_token_form: this.config.csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const blob = new Blob([JSON.stringify(response.config, null, 2)], {type: 'application/json'});
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'dashboard_contexts_export.json';
                    a.click();
                }
            }
        });
    },

    importContexts: function() {
        const self = this;
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json';

        input.onchange = function(e) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const config = JSON.parse(e.target.result);
                    top.restoreSession();
                $.ajax({
                        url: self.config.ajaxUrl,
                        type: 'POST',
                        data: { action: 'import_contexts', config: JSON.stringify(config), csrf_token_form: self.config.csrfToken },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                self.loadAdminConfig();
                                self.showAlert(response.results.success.length + ' contexts imported', 'success');
                            }
                        }
                    });
                } catch (err) {
                    self.showAlert('Invalid JSON file', 'danger');
                }
            };
            reader.readAsText(e.target.files[0]);
        };
        input.click();
    },

    showAlert: function(message, type) {
        const $alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show" style="position:fixed;top:10px;right:10px;z-index:9999;">
                ${jsText(message)}<button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        `);
        $('body').append($alert);
        setTimeout(() => $alert.alert('close'), 3000);
    },

    escapeHtml: function(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};
