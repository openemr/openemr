<?php

/**
 * Dashboard Context Manager - Administration Page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

// Check admin privileges
if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore('admin', 'users')) {
    echo xlt('Access denied');
    exit;
}

$csrfToken = CsrfUtils::collectCsrfToken();
$moduleUrl = OEGlobalsBag::getInstance()->get('webroot') . '/interface/modules/custom_modules/oe-module-dashboard-context';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Dashboard Context Manager'); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <style>
      .context-card {
        transition: all 0.2s ease;
        height: 100%;
      }

      .context-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
      }

      .context-card.system-context {
        border-left: 4px solid var(--primary, #007bff);
      }

      .context-card.custom-context {
        border-left: 4px solid var(--success, #28a745);
      }

      .widget-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 8px;
      }

      .widget-item {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: var(--light);
        transition: all 0.2s;
      }

      .widget-item:hover {
        border-color: var(--primary, #007bff);
      }

      .widget-item.active {
        background: var(--light);
        border-color: var(--success, #28a745);
      }

      .widget-item.drag-over {
        border-color: var(--primary, #007bff);
        border-style: dashed;
        background: rgba(0, 123, 255, 0.05);
      }

      .widget-item[draggable="true"] {
        cursor: default;
      }

      .stats-card {
        text-align: center;
        padding: 1.5rem;
        background: var(--primary, #007bff);
        color: var(--light);
        border-radius: 4px;
      }

      .stats-card .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
      }

      .user-row.locked {
        /*background-color: #fff3cd !important;*/
      }

      .badge-system {
        background-color: var(--primary, #007bff);
        color: var(--light)
      }

      .badge-custom {
        background-color: var(--success, #28a745);
        color: var(--light)
      }

      .nav-tabs .nav-link.active {
        font-weight: 600;
        background-color: var(--light)
      }

      .action-btns .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
      }

      .dialog-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .dialog-content {
        background: var(--light);
        border-radius: 4px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        max-height: 90vh;
        overflow-y: auto;
      }

      .dialog-header {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .dialog-body {
        padding: 1rem;
      }

      .dialog-footer {
        padding: 1rem;
        border-top: 1px solid #dee2e6;
        text-align: right;
      }
    </style>
</head>
<body class="body_top">
    <div class="container-fluid mt-3">
        <div class="row mb-3">
            <div class="col-12">
                <h4>
                    <i class="fa fa-sliders-h mr-2"></i>
                    <?php echo xlt('Dashboard Context Manager'); ?>
                </h4>
                <p class="text-muted"><?php echo xlt('Manage care contexts and control which dashboard widgets are available to users.'); ?></p>
            </div>
        </div>

        <ul class="nav nav-tabs" id="contextTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="contexts-tab" data-tab="contexts" href="#contexts" role="tab">
                    <i class="fa fa-layer-group mr-1"></i><?php echo xlt('Contexts'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="users-tab" data-tab="users" href="#users" role="tab">
                    <i class="fa fa-users mr-1"></i><?php echo xlt('User Assignments'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="roles-tab" data-tab="roles" href="#roles" role="tab">
                    <i class="fa fa-user-tag mr-1"></i><?php echo xlt('Role Defaults'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="stats-tab" data-tab="stats" href="#stats" role="tab">
                    <i class="fa fa-chart-bar mr-1"></i><?php echo xlt('Statistics'); ?>
                </a>
            </li>
        </ul>

        <div class="tab-content border border-top-0 p-3 bg-white" id="contextTabContent">
            <!-- Contexts Tab -->
            <div class="tab-pane fade show active" id="contexts" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" id="btnCreateContext">
                            <i class="fa fa-plus mr-1"></i><?php echo xlt('Create New Context'); ?>
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnExportContexts">
                            <i class="fa fa-download mr-1"></i><?php echo xlt('Export'); ?>
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnImportContexts">
                            <i class="fa fa-upload mr-1"></i><?php echo xlt('Import'); ?>
                        </button>
                    </div>
                </div>
                <div id="contextsContainer" class="row"></div>
            </div>

            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="font-weight-bold"><?php echo xlt('Facility'); ?></label>
                        <select id="filterFacility" class="form-control">
                            <option value=""><?php echo xlt('All Facilities'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold"><?php echo xlt('User Type'); ?></label>
                        <select id="filterUserType" class="form-control">
                            <option value=""><?php echo xlt('All User Types'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold"><?php echo xlt('Search'); ?></label>
                        <input type="text" id="filterSearch" class="form-control" placeholder="<?php echo xla('Search users...'); ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="btnBulkAssign">
                            <i class="fa fa-users-cog mr-1"></i><?php echo xlt('Bulk Assign'); ?>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-hover">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:30px;"><input type="checkbox" id="selectAllUsers"></th>
                            <th><?php echo xlt('User'); ?></th>
                            <th><?php echo xlt('Facility'); ?></th>
                            <th><?php echo xlt('Type'); ?></th>
                            <th><?php echo xlt('Current Context'); ?></th>
                            <th><?php echo xlt('Status'); ?></th>
                            <th style="width:120px;"><?php echo xlt('Actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody id="usersTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Roles Tab -->
            <div class="tab-pane fade" id="roles" role="tabpanel">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle mr-1"></i>
                    <?php echo xlt('Set default contexts for user roles. New users will automatically receive the context assigned to their role.'); ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                        <tr>
                            <th><?php echo xlt('Role Type'); ?></th>
                            <th><?php echo xlt('Default Context'); ?></th>
                            <th style="width:100px;"><?php echo xlt('Actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody id="roleDefaultsBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Statistics Tab -->
            <div class="tab-pane fade" id="stats" role="tabpanel">
                <div class="row" id="statsContainer"></div>
            </div>
        </div>
    </div>

    <script>
        (function ($) {
            'use strict';

            const ContextAdmin = {
                config: {
                    ajaxUrl: <?php echo js_escape($moduleUrl . '/public/admin_ajax.php'); ?>,
                    csrfToken: <?php echo js_escape($csrfToken); ?>,
                    contexts: [],
                    widgets: {},
                    userTypes: {},
                    facilities: [],
                    widgetOrders: {},
                    widgetLabels: {}
                },

                // Translation strings
                xl: {
                    system: <?php echo xlj('System'); ?>,
                    custom: <?php echo xlj('Custom'); ?>,
                    key: <?php echo xlj('Key'); ?>,
                    edit: <?php echo xlj('Edit'); ?>,
                    delete: <?php echo xlj('Delete'); ?>,
                    editContext: <?php echo xlj('Edit Context'); ?>,
                    createContext: <?php echo xlj('Create Context'); ?>,
                    contextName: <?php echo xlj('Context Name'); ?>,
                    contextKey: <?php echo xlj('Context Key'); ?>,
                    autoGenerated: <?php echo xlj('Auto-generated if empty'); ?>,
                    description: <?php echo xlj('Description'); ?>,
                    widgetConfig: <?php echo xlj('Widget Configuration'); ?>,
                    availableToAll: <?php echo xlj('Available to all users'); ?>,
                    cancel: <?php echo xlj('Cancel'); ?>,
                    save: <?php echo xlj('Save'); ?>,
                    contextUpdated: <?php echo xlj('Context updated'); ?>,
                    contextCreated: <?php echo xlj('Context created'); ?>,
                    errorSaving: <?php echo xlj('Error saving context'); ?>,
                    confirmDelete: <?php echo xlj('Are you sure you want to delete this context?'); ?>,
                    contextDeleted: <?php echo xlj('Context deleted'); ?>,
                    assignContext: <?php echo xlj('Assign Context'); ?>,
                    user: <?php echo xlj('User'); ?>,
                    context: <?php echo xlj('Context'); ?>,
                    lockContext: <?php echo xlj('Lock context'); ?>,
                    assign: <?php echo xlj('Assign'); ?>,
                    contextAssigned: <?php echo xlj('Context assigned'); ?>,
                    bulkAssign: <?php echo xlj('Bulk Assign Context'); ?>,
                    usersSelected: <?php echo xlj('users selected'); ?>,
                    contextToAssign: <?php echo xlj('Context to Assign'); ?>,
                    lockForAll: <?php echo xlj('Lock context for all'); ?>,
                    assignToAll: <?php echo xlj('Assign to All'); ?>,
                    selectUser: <?php echo xlj('Please select at least one user'); ?>,
                    usersUpdated: <?php echo xlj('users updated'); ?>,
                    userUnlocked: <?php echo xlj('User unlocked'); ?>,
                    noDefault: <?php echo xlj('No Default'); ?>,
                    roleDefaultSaved: <?php echo xlj('Role default saved'); ?>,
                    noData: <?php echo xlj('No usage data available yet.'); ?>,
                    contextsImported: <?php echo xlj('contexts imported'); ?>,
                    invalidJson: <?php echo xlj('Invalid JSON file'); ?>,
                    locked: <?php echo xlj('Locked'); ?>,
                    customSettings: <?php echo xlj('Has custom settings'); ?>,
                    widgetOrder: <?php echo xlj('Widget Order'); ?>,
                    customLabel: <?php echo xlj('Custom Label'); ?>,
                    dragToReorder: <?php echo xlj('Drag to reorder'); ?>
                },

                init: function () {
                    this.loadAdminConfig();
                    this.bindEvents();
                },

                bindEvents: function () {
                    const self = this;

                    // Tab switching
                    $('.nav-link[data-tab]').on('click', function (e) {
                        e.preventDefault();
                        const target = $(this).attr('href');

                        $('.nav-link').removeClass('active');
                        $(this).addClass('active');
                        $('.tab-pane').removeClass('show active');
                        $(target).addClass('show active');

                        if (target === '#users') self.loadUsers();
                        else if (target === '#roles') self.loadRoleDefaults();
                        else if (target === '#stats') self.loadStats();
                    });

                    $('#btnCreateContext').on('click', function () {
                        self.showContextDialog();
                    });
                    $('#filterFacility, #filterUserType').on('change', function () {
                        self.loadUsers();
                    });

                    let searchTimeout;
                    $('#filterSearch').on('input', function () {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(function () {
                            self.loadUsers();
                        }, 300);
                    });

                    $('#selectAllUsers').on('change', function () {
                        $('.user-select').prop('checked', $(this).is(':checked'));
                    });

                    $('#btnBulkAssign').on('click', function () {
                        self.showBulkAssignDialog();
                    });
                    $('#btnExportContexts').on('click', function () {
                        self.exportContexts();
                    });
                    $('#btnImportContexts').on('click', function () {
                        self.importContexts();
                    });
                },

                loadAdminConfig: function () {
                    const self = this;

                    top.restoreSession();
                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {action: 'get_admin_config', csrf_token_form: this.config.csrfToken},
                        dataType: 'json',
                        success: function (response) {
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

                renderContexts: function () {
                    const self = this;
                    const $container = $('#contextsContainer').empty();

                    this.config.contexts.forEach(function (ctx) {
                        const isSystem = ctx.is_system;
                        const cardClass = isSystem ? 'system-context' : 'custom-context';
                        const badgeClass = isSystem ? 'badge-system' : 'badge-custom';
                        const badgeText = isSystem ? self.xl.system : self.xl.custom;

                        const $card = $('<div>', {class: 'col-md-4 col-lg-3 mb-3'}).append(
                            $('<div>', {class: 'card context-card ' + cardClass + ' h-100'}).append(
                                $('<div>', {class: 'card-body'}).append(
                                    $('<h6>', {class: 'card-title d-flex justify-content-between align-items-start'}).append(
                                        $('<span>').text(ctx.context_name),
                                        $('<span>', {class: 'badge ' + badgeClass}).text(badgeText)
                                    ),
                                    $('<p>', {class: 'card-text small text-muted'}).text(ctx.description || ''),
                                    $('<p>', {class: 'card-text small'}).append(
                                        $('<strong>').text(self.xl.key + ': '),
                                        $('<code>').text(ctx.context_key)
                                    ),
                                    !isSystem ? $('<div>', {class: 'action-btns mt-2'}).append(
                                        $('<button>', {
                                            class: 'btn btn-sm btn-outline-primary btn-edit-context',
                                            'data-id': ctx.id,
                                            title: self.xl.edit
                                        }).append($('<i>', {class: 'fa fa-edit'})),
                                        $('<button>', {
                                            class: 'btn btn-sm btn-outline-danger btn-delete-context',
                                            'data-id': ctx.id,
                                            title: self.xl.delete
                                        }).append($('<i>', {class: 'fa fa-trash'}))
                                    ) : null
                                )
                            )
                        );
                        $container.append($card);
                    });

                    $('.btn-edit-context').on('click', function () {
                        self.editContext($(this).data('id'));
                    });
                    $('.btn-delete-context').on('click', function () {
                        self.deleteContext($(this).data('id'));
                    });
                },

                showContextDialog: function (contextData) {
                    const self = this;
                    const isEdit = contextData != null;

                    var $widgetGrid = $('<div>', {id: 'widgetConfigGrid', class: 'widget-grid'});
                    const widgetConfig = isEdit && contextData.widget_config
                        ? (typeof contextData.widget_config === 'string' ? JSON.parse(contextData.widget_config) : contextData.widget_config)
                        : {};

                    // Get existing order and labels for this context
                    const contextKey = isEdit ? contextData.context_key : '';
                    const existingOrder = (contextKey && self.config.widgetOrders[contextKey]) ? self.config.widgetOrders[contextKey] : [];
                    const existingLabels = (contextKey && self.config.widgetLabels[contextKey]) ? self.config.widgetLabels[contextKey] : {};

                    // Build ordered widget entries
                    var widgetEntries = [];
                    $.each(this.config.widgets, function (widgetId, widgetLabel) {
                        widgetEntries.push([widgetId, widgetLabel]);
                    });
                    if (existingOrder.length > 0) {
                        widgetEntries.sort(function (a, b) {
                            var idxA = existingOrder.indexOf(a[0]);
                            var idxB = existingOrder.indexOf(b[0]);
                            var posA = idxA >= 0 ? idxA : 9999;
                            var posB = idxB >= 0 ? idxB : 9999;
                            return posA - posB;
                        });
                    }

                    widgetEntries.forEach(function (entry) {
                        var widgetId = entry[0];
                        var widgetLabel = entry[1];
                        const isActive = widgetConfig[widgetId] !== false;
                        const customLabel = existingLabels[widgetId] || '';
                        $widgetGrid.append(
                            $('<div>', {class: 'widget-item ' + (isActive ? 'active' : ''), 'data-widget-id': widgetId, draggable: true}).append(
                                $('<div>', {class: 'd-flex align-items-center'}).append(
                                    $('<i>', {class: 'fa fa-grip-vertical text-muted mr-2', style: 'cursor:grab;', title: self.xl.dragToReorder}),
                                    $('<div>', {class: 'form-check mb-0 flex-grow-1'}).append(
                                        $('<input>', {
                                            type: 'checkbox',
                                            class: 'form-check-input widget-toggle',
                                            id: 'widget-' + widgetId,
                                            'data-widget': widgetId,
                                            checked: isActive
                                        }),
                                        $('<label>', {
                                            class: 'form-check-label',
                                            for: 'widget-' + widgetId
                                        }).text(widgetLabel)
                                    ),
                                    $('<input>', {
                                        type: 'text',
                                        class: 'form-control form-control-sm widget-label-input ml-2',
                                        'data-widget': widgetId,
                                        placeholder: self.xl.customLabel,
                                        value: customLabel,
                                        style: 'max-width:150px;font-size:0.8rem;'
                                    })
                                )
                            )
                        );
                    });

                    var $dialog = $('<div>', {id: 'contextDialog', class: 'dialog-overlay'}).append(
                        $('<div>', {class: 'dialog-content', style: 'width:90%;max-width:700px;'}).append(
                            $('<div>', {class: 'dialog-header bg-secondary text-light'}).append(
                                $('<h5>', {class: 'm-0'}).text(isEdit ? self.xl.editContext : self.xl.createContext),
                                $('<button>', {type: 'button', class: 'btn btn-sm btn-light dialog-close'}).html('&times;')
                            ),
                            $('<div>', {class: 'dialog-body'}).append(
                                $('<input>', {type: 'hidden', id: 'contextId', value: isEdit ? contextData.id : ''}),
                                $('<div>', {class: 'form-group'}).append(
                                    $('<label>', {class: 'font-weight-bold'}).text(self.xl.contextName + ' *'),
                                    $('<input>', {
                                        type: 'text',
                                        class: 'form-control',
                                        id: 'contextName',
                                        value: isEdit ? jsAttr(contextData.context_name) : ''
                                    })
                                ),
                                $('<div>', {class: 'form-group'}).append(
                                    $('<label>', {class: 'font-weight-bold'}).text(self.xl.contextKey),
                                    $('<input>', {
                                        type: 'text',
                                        class: 'form-control',
                                        id: 'contextKey',
                                        value: isEdit ? jsAttr(contextData.context_key) : '',
                                        readonly: isEdit,
                                        placeholder: jsAttr(self.xl.autoGenerated)
                                    })
                                ),
                                $('<div>', {class: 'form-group'}).append(
                                    $('<label>', {class: 'font-weight-bold'}).text(self.xl.description),
                                    $('<textarea>', {
                                        class: 'form-control',
                                        id: 'contextDescription',
                                        rows: 2
                                    }).text(isEdit ? (contextData.description || '') : '')
                                ),
                                $('<div>', {class: 'form-group'}).append(
                                    $('<label>', {class: 'font-weight-bold'}).text(self.xl.widgetConfig),
                                    $widgetGrid
                                ),
                                $('<div>', {class: 'form-check'}).append(
                                    $('<input>', {
                                        type: 'checkbox',
                                        class: 'form-check-input',
                                        id: 'contextGlobal',
                                        checked: !isEdit || contextData.is_global
                                    }),
                                    $('<label>', {class: 'form-check-label', for: 'contextGlobal'}).text(self.xl.availableToAll)
                                )
                            ),
                            $('<div>', {class: 'dialog-footer'}).append(
                                $('<button>', {type: 'button', class: 'btn btn-secondary dialog-close'}).text(self.xl.cancel),
                                $('<button>', {type: 'button', class: 'btn btn-primary', id: 'btnSaveContext'}).text(self.xl.save)
                            )
                        )
                    );

                    $('body').append($dialog);

                    $('.widget-toggle').on('change', function () {
                        $(this).closest('.widget-item').toggleClass('active', $(this).is(':checked'));
                    });

                    // Initialize drag-and-drop reordering
                    self.initDragReorder($widgetGrid);

                    $('.dialog-close').on('click', function () {
                        $('#contextDialog').remove();
                    });
                    $('#contextDialog').on('click', function (e) {
                        if (e.target === this) $(this).remove();
                    });

                    $('#btnSaveContext').on('click', function () {
                        self.saveContext();
                    });
                },

                saveContext: function () {
                    const self = this;
                    const contextId = $('#contextId').val();
                    const isEdit = contextId !== '';

                    const widgetConfig = {};
                    $('.widget-toggle').each(function () {
                        widgetConfig[$(this).data('widget')] = $(this).is(':checked');
                    });

                    // Collect widget order from DOM
                    const widgetOrder = [];
                    $('#widgetConfigGrid .widget-item').each(function () {
                        widgetOrder.push($(this).data('widget-id'));
                    });

                    // Collect custom labels
                    const widgetLabels = {};
                    $('.widget-label-input').each(function () {
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

                    if (isEdit) {
                        data.context_id = contextId;
                    } else {
                        data.context_key = $('#contextKey').val();
                    }

                    top.restoreSession();
                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                // Determine context key for saving order/labels
                                var ctxKey = isEdit ? ($('#contextKey').val() || '') : (response.context_key || $('#contextKey').val() || '');
                                // Save widget order
                                if (ctxKey && widgetOrder.length > 0) {
                                    self.saveWidgetOrder(ctxKey, widgetOrder);
                                }
                                // Save widget labels
                                if (ctxKey) {
                                    self.saveWidgetLabels(ctxKey, widgetLabels);
                                }

                                $('#contextDialog').remove();
                                self.loadAdminConfig();
                                self.showAlert(isEdit ? self.xl.contextUpdated : self.xl.contextCreated, 'success');
                            } else {
                                self.showAlert(response.error || self.xl.errorSaving, 'danger');
                            }
                        }
                    });
                },

                initDragReorder: function ($container) {
                    var draggedEl = null;

                    $container.on('dragstart', '.widget-item', function (e) {
                        draggedEl = this;
                        $(this).css('opacity', '0.4');
                        e.originalEvent.dataTransfer.effectAllowed = 'move';
                    });

                    $container.on('dragend', '.widget-item', function () {
                        $(this).css('opacity', '');
                        $container.find('.widget-item').removeClass('drag-over');
                    });

                    $container.on('dragover', '.widget-item', function (e) {
                        e.preventDefault();
                        e.originalEvent.dataTransfer.dropEffect = 'move';
                        $(this).addClass('drag-over');
                    });

                    $container.on('dragleave', '.widget-item', function () {
                        $(this).removeClass('drag-over');
                    });

                    $container.on('drop', '.widget-item', function (e) {
                        e.preventDefault();
                        $(this).removeClass('drag-over');
                        if (draggedEl !== this) {
                            var $dragged = $(draggedEl);
                            var $target = $(this);
                            var targetRect = this.getBoundingClientRect();
                            var midY = targetRect.top + targetRect.height / 2;
                            if (e.originalEvent.clientY < midY) {
                                $dragged.insertBefore($target);
                            } else {
                                $dragged.insertAfter($target);
                            }
                        }
                    });
                },

                saveWidgetOrder: function (contextKey, order) {
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

                saveWidgetLabels: function (contextKey, labels) {
                    var self = this;
                    var existingLabels = this.config.widgetLabels[contextKey] || {};

                    // Save new/updated labels
                    $.each(labels, function (widgetId, label) {
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
                    });

                    // Delete removed labels
                    $.each(existingLabels, function (widgetId) {
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
                    });
                },

                editContext: function (contextId) {
                    const self = this;
                    const context = this.config.contexts.find(function (c) {
                        return c.id == contextId;
                    });
                    if (context) {
                        this.showContextDialog(context);
                    }
                },

                deleteContext: function (contextId) {
                    const self = this;
                    if (!confirm(this.xl.confirmDelete)) return;

                    top.restoreSession();
                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {action: 'delete_context', context_id: contextId, csrf_token_form: this.config.csrfToken},
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                self.loadAdminConfig();
                                self.showAlert(self.xl.contextDeleted, 'success');
                            }
                        }
                    });
                },

                populateFilters: function () {
                    const self = this;
                    const $facilitySelect = $('#filterFacility');
                    this.config.facilities.forEach(function (f) {
                        $facilitySelect.append($('<option>', {value: f.id}).text(f.name));
                    });

                    const $typeSelect = $('#filterUserType');
                    $.each(this.config.userTypes, function (key, label) {
                        $typeSelect.append($('<option>', {value: key}).text(label));
                    });
                },

                loadUsers: function () {
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
                        success: function (response) {
                            if (response.success) {
                                self.renderUsers(response.users);
                            }
                        }
                    });
                },

                renderUsers: function (users) {
                    const self = this;
                    const $tbody = $('#usersTableBody').empty();

                    users.forEach(function (user) {
                        const context = self.config.contexts.find(function (c) {
                            return c.context_key === user.active_context;
                        });
                        const contextLabel = context ? context.context_name : user.active_context;
                        const isLocked = user.is_locked;

                        var $row = $('<tr>', {class: isLocked ? 'user-row locked' : 'user-row'}).append(
                            $('<td>').append($('<input>', {type: 'checkbox', class: 'user-select', value: user.id})),
                            $('<td>').append(
                                $('<strong>').text(user.name),
                                $('<br>'),
                                $('<small>', {class: 'text-muted'}).text(user.username)
                            ),
                            $('<td>').text(user.facility_name || '-'),
                            $('<td>').text(self.config.userTypes[user.user_type] || user.user_type || '-'),
                            $('<td>').append($('<span>', {class: 'badge badge-info'}).text(contextLabel)),
                            $('<td>').append(
                                isLocked ? $('<i>', {class: 'fa fa-lock text-warning', title: self.xl.locked}) : null,
                                user.has_custom_config ? $('<i>', {class: 'fa fa-cog text-info ml-1', title: self.xl.customSettings}) : null
                            ),
                            $('<td>', {class: 'action-btns'}).append(
                                $('<button>', {
                                    class: 'btn btn-sm btn-primary btn-assign-user',
                                    'data-user-id': user.id,
                                    'data-user-name': user.name,
                                    'data-context': user.active_context
                                }).append($('<i>', {class: 'fa fa-user-cog'})),
                                isLocked ? $('<button>', {
                                    class: 'btn btn-sm btn-warning btn-unlock-user',
                                    'data-user-id': user.id
                                }).append($('<i>', {class: 'fa fa-unlock'})) : null
                            )
                        );
                        $tbody.append($row);
                    });

                    $('.btn-assign-user').on('click', function () {
                        self.showAssignDialog($(this).data('user-id'), $(this).data('user-name'), $(this).data('context'));
                    });
                    $('.btn-unlock-user').on('click', function () {
                        self.unlockUser($(this).data('user-id'));
                    });
                },

                showAssignDialog: function (userId, userName, currentContext) {
                    const self = this;

                    var $contextSelect = $('<select>', {class: 'form-control', id: 'assignContext'});
                    this.config.contexts.forEach(function (ctx) {
                        $contextSelect.append($('<option>', {
                            value: ctx.context_key,
                            selected: ctx.context_key === currentContext
                        }).text(ctx.context_name));
                    });

                    var $dialog = $('<div>', {id: 'assignDialog', class: 'dialog-overlay'}).append(
                        $('<div>', {class: 'dialog-content', style: 'width:400px;'}).append(
                            $('<div>', {class: 'dialog-header bg-secondary text-light'}).append(
                                $('<h5>', {class: 'm-0'}).text(self.xl.assignContext),
                                $('<button>', {type: 'button', class: 'btn btn-sm btn-light dialog-close'}).html('&times;')
                            ),
                            $('<div>', {class: 'dialog-body'}).append(
                                $('<input>', {type: 'hidden', id: 'assignUserId', value: userId}),
                                $('<p>').append($('<strong>').text(self.xl.user + ': '), $('<span>').text(userName)),
                                $('<div>', {class: 'form-group'}).append(
                                    $('<label>', {class: 'font-weight-bold'}).text(self.xl.context),
                                    $contextSelect
                                ),
                                $('<div>', {class: 'form-check'}).append(
                                    $('<input>', {type: 'checkbox', class: 'form-check-input', id: 'assignLock'}),
                                    $('<label>', {class: 'form-check-label', for: 'assignLock'}).text(self.xl.lockContext)
                                )
                            ),
                            $('<div>', {class: 'dialog-footer'}).append(
                                $('<button>', {type: 'button', class: 'btn btn-secondary dialog-close'}).text(self.xl.cancel),
                                $('<button>', {type: 'button', class: 'btn btn-primary', id: 'btnConfirmAssign'}).text(self.xl.assign)
                            )
                        )
                    );

                    $('body').append($dialog);

                    $('.dialog-close').on('click', function () {
                        $('#assignDialog').remove();
                    });
                    $('#assignDialog').on('click', function (e) {
                        if (e.target === this) $(this).remove();
                    });

                    $('#btnConfirmAssign').on('click', function () {
                        self.assignContext();
                    });
                },

                assignContext: function () {
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
                        success: function (response) {
                            if (response.success) {
                                $('#assignDialog').remove();
                                self.loadUsers();
                                self.showAlert(self.xl.contextAssigned, 'success');
                            }
                        }
                    });
                },

                showBulkAssignDialog: function () {
                    const self = this;
                    const selected = $('.user-select:checked');

                    if (selected.length === 0) {
                        this.showAlert(this.xl.selectUser, 'warning');
                        return;
                    }

                    var $contextSelect = $('<select>', {class: 'form-control', id: 'bulkContext'});
                    this.config.contexts.forEach(function (ctx) {
                        $contextSelect.append($('<option>', {value: ctx.context_key}).text(ctx.context_name));
                    });

                    var $dialog = $('<div>', {id: 'bulkAssignDialog', class: 'dialog-overlay'}).append(
                        $('<div>', {class: 'dialog-content', style: 'width:400px;'}).append(
                            $('<div>', {class: 'dialog-header bg-secondary text-light'}).append(
                                $('<h5>', {class: 'm-0'}).text(self.xl.bulkAssign),
                                $('<button>', {type: 'button', class: 'btn btn-sm btn-light dialog-close'}).html('&times;')
                            ),
                            $('<div>', {class: 'dialog-body'}).append(
                                $('<div>', {class: 'alert alert-info'}).append(
                                    $('<i>', {class: 'fa fa-info-circle mr-1'}),
                                    $('<span>').text(selected.length + ' ' + self.xl.usersSelected)
                                ),
                                $('<div>', {class: 'form-group'}).append(
                                    $('<label>', {class: 'font-weight-bold'}).text(self.xl.contextToAssign),
                                    $contextSelect
                                ),
                                $('<div>', {class: 'form-check'}).append(
                                    $('<input>', {type: 'checkbox', class: 'form-check-input', id: 'bulkLock'}),
                                    $('<label>', {class: 'form-check-label', for: 'bulkLock'}).text(self.xl.lockForAll)
                                )
                            ),
                            $('<div>', {class: 'dialog-footer'}).append(
                                $('<button>', {type: 'button', class: 'btn btn-secondary dialog-close'}).text(self.xl.cancel),
                                $('<button>', {type: 'button', class: 'btn btn-primary', id: 'btnConfirmBulkAssign'}).text(self.xl.assignToAll)
                            )
                        )
                    );

                    $('body').append($dialog);

                    $('.dialog-close').on('click', function () {
                        $('#bulkAssignDialog').remove();
                    });
                    $('#bulkAssignDialog').on('click', function (e) {
                        if (e.target === this) $(this).remove();
                    });

                    $('#btnConfirmBulkAssign').on('click', function () {
                        self.bulkAssign();
                    });
                },

                bulkAssign: function () {
                    const self = this;
                    const userIds = [];
                    $('.user-select:checked').each(function () {
                        userIds.push($(this).val());
                    });

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
                        success: function (response) {
                            if (response.success) {
                                $('#bulkAssignDialog').remove();
                                self.loadUsers();
                                self.showAlert(response.success_count + ' ' + self.xl.usersUpdated, 'success');
                            }
                        }
                    });
                },

                unlockUser: function (userId) {
                    const self = this;

                    top.restoreSession();
                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {action: 'remove_user_assignment', user_id: userId, csrf_token_form: this.config.csrfToken},
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                self.loadUsers();
                                self.showAlert(self.xl.userUnlocked, 'success');
                            }
                        }
                    });
                },

                loadRoleDefaults: function () {
                    const self = this;

                    top.restoreSession();
                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {action: 'get_role_defaults', csrf_token_form: this.config.csrfToken},
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                self.renderRoleDefaults(response.defaults, response.user_types);
                            }
                        }
                    });
                },

                renderRoleDefaults: function (defaults, userTypes) {
                    const self = this;
                    const $tbody = $('#roleDefaultsBody').empty();
                    const defaultMap = {};
                    defaults.forEach(function (d) {
                        defaultMap[d.role_type] = d.context_key;
                    });

                    $.each(userTypes, function (roleType, roleLabel) {
                        const currentContext = defaultMap[roleType] || '';

                        var $contextSelect = $('<select>', {
                            class: 'form-control role-default-select',
                            'data-role': roleType
                        }).append($('<option>', {value: ''}).text('-- ' + self.xl.noDefault + ' --'));

                        self.config.contexts.forEach(function (ctx) {
                            $contextSelect.append($('<option>', {
                                value: ctx.context_key,
                                selected: ctx.context_key === currentContext
                            }).text(ctx.context_name));
                        });

                        $tbody.append(
                            $('<tr>').append(
                                $('<td>').append($('<strong>').text(roleLabel)),
                                $('<td>').append($contextSelect),
                                $('<td>', {class: 'action-btns'}).append(
                                    $('<button>', {
                                        class: 'btn btn-sm btn-primary btn-save-role-default',
                                        'data-role': roleType
                                    }).append($('<i>', {class: 'fa fa-save'}))
                                )
                            )
                        );
                    });

                    $('.btn-save-role-default').on('click', function () {
                        const roleType = $(this).data('role');
                        const contextKey = $('.role-default-select[data-role="' + roleType + '"]').val();
                        self.saveRoleDefault(roleType, contextKey);
                    });
                },

                saveRoleDefault: function (roleType, contextKey) {
                    const self = this;

                    top.restoreSession();
                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {action: 'set_role_default', role_type: roleType, context_key: contextKey, csrf_token_form: this.config.csrfToken},
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                self.showAlert(self.xl.roleDefaultSaved, 'success');
                            }
                        }
                    });
                },

                loadStats: function () {
                    const self = this;

                    top.restoreSession();
                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {action: 'get_usage_stats', csrf_token_form: this.config.csrfToken},
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                self.renderStats(response.stats);
                            }
                        }
                    });
                },

                renderStats: function (stats) {
                    const self = this;
                    const $container = $('#statsContainer').empty();

                    if (stats.length === 0) {
                        $container.append($('<div>', {class: 'col-12'}).append(
                            $('<p>', {class: 'text-muted'}).text(self.xl.noData)
                        ));
                        return;
                    }

                    stats.forEach(function (stat) {
                        $container.append(
                            $('<div>', {class: 'col-md-3 col-sm-6 mb-3'}).append(
                                $('<div>', {class: 'card stats-card h-100'}).append(
                                    $('<div>', {class: 'stat-number'}).text(stat.user_count),
                                    $('<div>', {class: 'stat-label'}).text(stat.context_label)
                                )
                            )
                        );
                    });
                },

                exportContexts: function () {
                    const self = this;

                    top.restoreSession();
                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {action: 'export_contexts', csrf_token_form: this.config.csrfToken},
                        dataType: 'json',
                        success: function (response) {
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

                importContexts: function () {
                    const self = this;
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = '.json';

                    input.onchange = function (e) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            try {
                                const config = JSON.parse(e.target.result);
                                top.restoreSession();
                                $.ajax({
                                    url: self.config.ajaxUrl,
                                    type: 'POST',
                                    data: {action: 'import_contexts', config: JSON.stringify(config), csrf_token_form: self.config.csrfToken},
                                    dataType: 'json',
                                    success: function (response) {
                                        if (response.success) {
                                            self.loadAdminConfig();
                                            self.showAlert(response.results.success.length + ' ' + self.xl.contextsImported, 'success');
                                        }
                                    }
                                });
                            } catch (err) {
                                self.showAlert(self.xl.invalidJson, 'danger');
                            }
                        };
                        reader.readAsText(e.target.files[0]);
                    };
                    input.click();
                },

                showAlert: function (message, type) {
                    if (typeof alertMsg === 'function') {
                        alertMsg(message, type === 'danger' ? 5000 : 3000, type);
                        return;
                    }

                    const alertClass = 'alert-' + (type || 'info');
                    const $alert = $('<div>', {
                        class: 'alert ' + alertClass,
                        style: 'position:fixed;top:10px;right:10px;z-index:10000;min-width:200px;'
                    }).text(message).append(
                        $('<button>', {
                            type: 'button',
                            class: 'close ml-2'
                        }).html('&times;').on('click', function () {
                            $(this).parent().remove();
                        })
                    );
                    $('body').append($alert);
                    setTimeout(function () {
                        $alert.fadeOut(function () {
                            $(this).remove();
                        });
                    }, 4000);
                }
            };

            $(function () {
                ContextAdmin.init();
            });

        })(jQuery);
    </script>
</body>
</html>
