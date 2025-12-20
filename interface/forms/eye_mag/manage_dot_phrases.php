<?php

/**
 * forms/eye_mag/manage_dot_phrases.php
 *
 * Manage and Import Dot Phrases
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@MedFetch.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Core\Header;

// Fetch all active users for the import dropdown who actually have dot phrases
$users_query = "SELECT u.id, u.fname, u.lname, u.username 
                FROM users u 
                WHERE u.active = 1 
                AND u.authorized = 1
                AND u.username != ''
                AND u.id != ? 
                AND EXISTS (
                    SELECT 1 
                    FROM list_options lo 
                    WHERE lo.list_id = CONCAT('dot_phrases_', u.id)
                )
                ORDER BY u.lname, u.fname";
$users_res = sqlStatement($users_query, [$_SESSION['authUserID']]);
$users = [];
while ($row = sqlFetchArray($users_res)) {
    $users[] = $row;
}

?>
<html>
<head>
    <title><?php echo xlt("Manage Dot Phrases"); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        .phrase-content {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .scrollable-table {
            max-height: 400px;
            overflow-y: auto;
        }
        .multi-field-list {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 0;
            font-size: 0.9em;
        }
        .multi-field-list li {
            margin-bottom: 2px;
        }
        .multi-field-list strong {
            color: #555;
        }
    </style>
    <script>
        var myPhrases = {};
        var importPhrasesData = {};

        $(document).ready(function() {
            loadMyPhrases();

            $('#import_user_select').change(function() {
                var userId = $(this).val();
                if (userId) {
                    loadImportPhrases(userId);
                } else {
                    $('#import_phrases_container').hide();
                }
            });
        });

        function formatPhraseContent(value) {
            if (typeof value === 'string') {
                return $('<div>').text(value).html();
            } else if (typeof value === 'object' && value !== null) {
                var html = '<ul class="multi-field-list">';
                $.each(value, function(k, v) {
                    html += '<li><strong>' + $('<div>').text(k).html() + ':</strong> ' + $('<div>').text(v).html() + '</li>';
                });
                html += '</ul>';
                return html;
            }
            return '';
        }

        function loadMyPhrases() {
            $.ajax({
                url: 'save.php',
                type: 'GET',
                data: {
                    action: 'get_dot_phrases',
                    user_id: '<?php echo $_SESSION['authUserID']; ?>'
                },
                dataType: 'json',
                success: function(data) {
                    myPhrases = data;
                    renderMyPhrases();
                }
            });
        }

        function renderMyPhrases() {
            var tbody = $('#my_phrases_table tbody');
            tbody.empty();
            
            if (Object.keys(myPhrases).length === 0) {
                tbody.append('<tr><td colspan="3" class="text-center"><?php echo xla("No dot phrases found."); ?></td></tr>');
                return;
            }

            $.each(myPhrases, function(key, value) {
                var displayHtml = formatPhraseContent(value);
                var row = '<tr>' +
                    '<td>' + key + '</td>' +
                    '<td class="phrase-content">' + displayHtml + '</td>' +
                    '<td>' +
                        '<button class="btn btn-sm btn-primary mr-1" onclick="editPhrase(\'' + key + '\')"><?php echo xla("Edit"); ?></button>' +
                        '<button class="btn btn-sm btn-danger" onclick="deletePhrase(\'' + key + '\')"><?php echo xla("Delete"); ?></button>' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
        }

        function loadImportPhrases(userId) {
            $.ajax({
                url: 'save.php',
                type: 'GET',
                data: {
                    action: 'get_dot_phrases',
                    user_id: userId
                },
                dataType: 'json',
                success: function(data) {
                    importPhrasesData = data;
                    renderImportPhrases();
                    $('#import_phrases_container').show();
                }
            });
        }

        function renderImportPhrases() {
            var tbody = $('#import_phrases_table tbody');
            tbody.empty();

            if (Object.keys(importPhrasesData).length === 0) {
                tbody.append('<tr><td colspan="3" class="text-center"><?php echo xla("No phrases found for this user."); ?></td></tr>');
                return;
            }

            $.each(importPhrasesData, function(key, value) {
                var displayHtml = formatPhraseContent(value);
                var row = '<tr>' +
                    '<td><input type="checkbox" class="import-check" value="' + key + '"></td>' +
                    '<td>' + key + '</td>' +
                    '<td class="phrase-content">' + displayHtml + '</td>' +
                    '</tr>';
                tbody.append(row);
            });
        }

        function selectAllImport(checked) {
            $('.import-check').prop('checked', checked);
        }

        function importSelected() {
            var selected = $('.import-check:checked');
            if (selected.length === 0) {
                alert('<?php echo xla("Please select phrases to import."); ?>');
                return;
            }

            var conflicts = [];
            var toImport = {};

            selected.each(function() {
                var key = $(this).val();
                var value = importPhrasesData[key];
                
                if (myPhrases.hasOwnProperty(key)) {
                    conflicts.push(key);
                }
                toImport[key] = value;
            });

            if (conflicts.length > 0) {
                var confirmMsg = '<?php echo xla("The following phrases already exist in your list:"); ?>\n' + 
                                 conflicts.join(', ') + '\n\n' + 
                                 '<?php echo xla("Do you want to overwrite them?"); ?>';
                if (!confirm(confirmMsg)) {
                    // If user says no, we could offer to skip duplicates, but for now let's just cancel or maybe filter?
                    // The requirement says "offer to ignore or overwrite".
                    // Let's ask specifically.
                    if (confirm('<?php echo xla("Click OK to Overwrite, Cancel to Ignore duplicates and import others."); ?>')) {
                        // Overwrite: do nothing special, just proceed
                    } else {
                        // Ignore: remove conflicts from toImport
                        $.each(conflicts, function(index, key) {
                            delete toImport[key];
                        });
                    }
                }
            }

            // Merge toImport into myPhrases
            $.extend(myPhrases, toImport);
            renderMyPhrases();
            alert('<?php echo xla("Phrases added to your list. Click Save Changes to make them permanent."); ?>');
            
            // Uncheck everything
            $('.import-check').prop('checked', false);
            $('#select_all_import').prop('checked', false);
        }

        function deletePhrase(key) {
            if (confirm('<?php echo xla("Are you sure you want to delete this phrase?"); ?>')) {
                delete myPhrases[key];
                renderMyPhrases();
            }
        }

        function editPhrase(key) {
            var value = myPhrases[key];
            $('#editKey').val(key);
            var container = $('#editContainer');
            container.empty();

            if (typeof value === 'string') {
                // Single field
                container.append('<div class="form-group"><label><?php echo xla("Content"); ?>:</label><textarea id="editValue" class="form-control" rows="4">' + value + '</textarea></div>');
            } else {
                // Multi field
                $.each(value, function(fieldId, fieldVal) {
                    container.append('<div class="form-group"><label>' + fieldId + '</label><input type="text" class="form-control edit-multi-field" data-id="' + fieldId + '" value="' + fieldVal + '"></div>');
                });
            }
            $('#editModal').show();
        }

        function saveEdit() {
            var key = $('#editKey').val();
            var newValue;
            
            if ($('#editValue').length) {
                newValue = $('#editValue').val();
            } else {
                newValue = {};
                $('.edit-multi-field').each(function() {
                    newValue[$(this).data('id')] = $(this).val();
                });
            }
            
            myPhrases[key] = newValue;
            renderMyPhrases();
            $('#editModal').hide();
        }

        function saveChanges() {
            $.ajax({
                url: 'save.php',
                type: 'POST',
                data: {
                    action: 'save_dot_phrases',
                    phrases: JSON.stringify(myPhrases)
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        alert('<?php echo xla("Changes saved successfully."); ?>');
                    } else {
                        alert('<?php echo xla("Error saving changes."); ?>');
                    }
                },
                error: function() {
                    alert('<?php echo xla("Error saving changes."); ?>');
                }
            });
        }
    </script>
</head>
<body>
    <div class="container-fluid mt-3">
        <h3><?php echo xlt("Manage Dot Phrases"); ?></h3>
        
        <div class="row">
            <!-- My Phrases Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><?php echo xlt("My Dot Phrases"); ?></span>
                        <button class="btn btn-light btn-sm" onclick="saveChanges()"><?php echo xlt("Save Changes"); ?></button>
                    </div>
                    <div class="card-body scrollable-table">
                        <table class="table table-striped table-hover" id="my_phrases_table">
                            <thead>
                                <tr>
                                    <th><?php echo xlt("Shortcut"); ?></th>
                                    <th><?php echo xlt("Content"); ?></th>
                                    <th><?php echo xlt("Action"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Import Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <?php echo xlt("Import from User"); ?>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="import_user_select"><?php echo xlt("Select User:"); ?></label>
                            <select class="form-control" id="import_user_select">
                                <option value=""><?php echo xlt("-- Select User --"); ?></option>
                                <?php foreach ($users as $user) : ?>
                                    <option value="<?php echo attr($user['id']); ?>">
                                        <?php echo text($user['lname'] . ', ' . $user['fname'] . ' (' . $user['username'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="import_phrases_container" style="display:none;">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5><?php echo xlt("Available Phrases"); ?></h5>
                                <button class="btn btn-success btn-sm" onclick="importSelected()"><?php echo xlt("Import Selected"); ?></button>
                            </div>
                            
                            <div class="scrollable-table">
                                <table class="table table-sm table-bordered" id="import_phrases_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;"><input type="checkbox" id="select_all_import" onclick="selectAllImport(this.checked)"></th>
                                            <th><?php echo xlt("Shortcut"); ?></th>
                                            <th><?php echo xlt("Content"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" style="display:none; position:fixed; z-index:10001; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
      <div class="modal-content" style="background-color:#fefefe; margin:10% auto; padding:20px; border:1px solid #888; width:500px; border-radius:8px; position: relative;">
        <span class="closeButton fa fa-times" onclick="document.getElementById('editModal').style.display='none'" style="position: absolute; top: 15px; right: 15px; cursor:pointer; font-size: 1.2em;"></span>
        <h2 style="margin-top: 0;"><?php echo xlt('Edit Phrase'); ?></h2>
        <input type="hidden" id="editKey">
        <div id="editContainer" style="max-height: 400px; overflow-y: auto; margin-bottom: 15px;">
            <!-- Content injected by JS -->
        </div>
        <div style="text-align: right;">
            <button type="button" class="btn btn-primary" onclick="saveEdit()"><?php echo xlt('Update'); ?></button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'"><?php echo xlt('Cancel'); ?></button>
        </div>
      </div>
    </div>
</body>
</html>
