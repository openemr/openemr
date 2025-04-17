<?php

/*
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2014 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Ensure this script is not called separately
if ($langModuleFlag !== true) {
    die(function_exists('xlt') ? xlt('Authentication Error') : 'Authentication Error');
}

// gacl control
$thisauth = AclMain::aclCheckCore('admin', 'language');
if (!$thisauth) {
    echo "<html>\n<body>\n";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

require_once("translation_utilities.php");

if (!isset($_FILES['language_file'])) {
    die(xlt('No file specified'));
}

if (!isset($_REQUEST['language_id'])) {
    die(xlt('No Language ID specified'));
}

$lang_id = $_REQUEST['language_id'];
$resLanguage = sqlStatement("select * from lang_languages where  lang_id = ?", array($lang_id));
$rowLanguage = sqlFetchArray($resLanguage);
$lang_description = $rowLanguage['lang_description'];

$handle = utf8_fopen_read($_FILES["language_file"]["tmp_name"]);
$file_contents = array();
if ($handle) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $file_contents[] = $data;
    }
}

if (count($file_contents) === 0) {
    die(xlt('Unable to Parse file! Verify File encoding'));
}

?>

<div id="status"></div>
<div id="information"></div>
<div id="file-display" data-bind="template:{name: 'file-info', data: filedata}" ></div>

<script type="text/html" id="file-info">

    <div id="verify" data-bind="visible: mode()=='verify' ">
        <h1>
            <?php echo xlt('Verify Contents to apply to') . ' ' . text($lang_description); ?>
        </h1>
        <span><?php echo xlt('Choose constant column'); ?></span> <select data-bind="options: header,optionsText: 'text', value: constant_choice" ></select><br>

        <span><?php echo xlt('Choose definition column'); ?></span> <select data-bind="options: header,optionsText: 'text', value: definition_choice" ></select><br>

        <input type="button" id="preview" value="<?php echo xla('Preview Changes'); ?>" data-bind="click: previewChanges"></input>

        <table>
            <thead>
                <tr data-bind="foreach: header">
                        <th data-bind="text:$data.text, attr: { index: $index}"></th>                
                </tr>
            </thead>
            <tbody>
                <?php
                for ($idx = 1; $idx < count($file_contents); $idx++) {
                    $row = $file_contents[$idx];
                    echo "<tr>";
                    foreach ($row as $cell) {
                        echo "<td>" . text($cell) . "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- ko if: mode()=='preview' -->
    <div id="preview" data-bind="with: preview_data">
        <h1><?php echo xlt('Preview Changes'); ?></h1>
        <input type="button" id="preview" value="<?php echo xla('Commit Changes'); ?>" data-bind="click: commitChanges"></input>        
        <div>
            <span><?php echo xlt('Unchanged Entries verified'); ?>:</span><span data-bind="text:unchanged()"></span>
        </div>
        <div>
            <span><?php echo xlt('Empty Definitions'); ?>:</span><span data-bind="text:empty()"></span>
        </div>
        <div>
            <div><?php echo xlt('Created Definitions'); ?>:</span><span data-bind="text:created().length"></div>
            <div><?php echo xlt('Updated Definitions'); ?>:</span><span data-bind="text:updated().length"></div>
            <h3><?php echo xlt('Created Definitions List'); ?></h3>
            <div data-bind="foreach: created">
                <div data-bind="text:$data"></div>
            </div>
            <h3><?php echo xlt('Updated Definitions List'); ?></h3>
            <div data-bind="foreach: updated">
                <div data-bind="text:$data"></div>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <!-- ko if: mode()=='committed' -->
    <div id="committed" data-bind="with: review_data">
        <h1><?php echo xlt('Committed Changes'); ?></h1>
        <div>
            <span><?php echo xlt('Unchanged Entries verified'); ?>:</span><span data-bind="text:unchanged()"></span>
        </div>
        <div>
            <span><?php echo xlt('Empty Definitions'); ?>:</span><span data-bind="text:empty()"></span>
        </div>
        <div>
            <div><?php echo xlt('Created Definitions'); ?>:</span><span data-bind="text:created().length"></div>
            <div><?php echo xlt('Updated Definitions'); ?>:</span><span data-bind="text:updated().length"></div>
            <h3><?php echo xlt('Created Definitions List'); ?></h3>
            <div data-bind="foreach: created">
                <div data-bind="text:$data"></div>
            </div>
            <h3><?php echo xlt('Updated Definitions List'); ?></h3>
            <div data-bind="foreach: updated">
                <div data-bind="text:$data"></div>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <!-- ko if:loading() -->
        <span data-bind="text:processingStatus"></span><i class="fa fa-spinner fa-spin"></i>
    <!-- /ko -->
</script>

<script>
    var file_contents = <?php echo json_encode($file_contents); ?>;
    var header_data = ko.observableArray();
    for(var h_index = 0; h_index < file_contents[0].length; h_index++)
    {
        var entry = {
            idx: h_index,
            text: file_contents[0][h_index]
        }
        header_data.push(entry);
    }

    var vm_file_display = {
        filedata: {
            start: ko.observable(1),
            end: ko.observable(20),
            total:ko.observable(file_contents.length),
            header: header_data,            
            constant_choice: ko.observable(),
            definition_choice: ko.observable(),            
            mode: ko.observable("verify"),
            preview_data: {
                changed: ko.observableArray(),
                unchanged: ko.observable(0),
                empty: ko.observable(0),
                changed_html:ko.observable(),
                updated: ko.observableArray(),
                created: ko.observableArray()
            },
            loading: ko.observable(false),
            display_contents: ko.observableArray(),
            review_data: {
                changed: ko.observableArray(),
                unchanged: ko.observable(0),
                empty: ko.observable(0),
                changed_html:ko.observable(),
                updated: ko.observableArray(),
                created: ko.observableArray()
            },
            processingStatus: ko.observable(<?php echo xlj('Please wait'); ?>)
        },
        select_display_contents: function()
        {
            this.filedata.display_contents.removeAll();
            if (this.filedata.end() >= file_contents.length)
            {
                this.filedata.end(file_contents.length);
            }
            for (var idx=this.filedata.start(); (idx <= this.filedata.end()); idx++)
            {
                this.filedata.display_contents.push(file_contents[idx]);
            }
        }
    };

    vm_file_display.filedata.constant_choice(vm_file_display.filedata.header()[1]);
    vm_file_display.filedata.definition_choice(vm_file_display.filedata.header()[3]);
    vm_file_display.select_display_contents(1,20);
    ko.applyBindings(vm_file_display);
    var translations = [];    
    function previewChanges()
    {
        translations = [];
        var constant_index = vm_file_display.filedata.constant_choice().idx;
        var definition_index = vm_file_display.filedata.definition_choice().idx;

        for (var idx=1; idx < file_contents.length; idx++)
        {
            var entry = file_contents[idx];
            var translation = [];
            translation[0] = entry[constant_index];
            translation[1] = entry[definition_index];
            translations[idx-1] = translation;
        }
        vm_file_display.filedata.mode("processing");
        vm_file_display.filedata.processingStatus(<?php echo xlj('Generating preview data. Please Wait'); ?>);
        vm_file_display.filedata.loading(true);
        
        $.post("csv/commit_csv.php",
            {
                lang_id: <?php echo json_encode($lang_id);?>
                ,translations:JSON.stringify(translations)
                ,preview: true
            },
            function(data)
            {
                vm_file_display.filedata.mode("preview");
                vm_file_display.filedata.loading(false);
                vm_file_display.filedata.preview_data.unchanged(data.unchanged);
                vm_file_display.filedata.preview_data.empty(data.empty);
                vm_file_display.filedata.preview_data.changed.removeAll();
                vm_file_display.filedata.preview_data.changed(data.changed);
                vm_file_display.filedata.preview_data.changed_html(data.html_changes);   
                vm_file_display.filedata.preview_data.updated.removeAll();
                vm_file_display.filedata.preview_data.updated(data.updated);
                vm_file_display.filedata.preview_data.created.removeAll();
                vm_file_display.filedata.preview_data.created(data.created);
            }
            ,"json"
        );
    }

    function commitChanges()
    {
        vm_file_display.filedata.mode("processing");
        vm_file_display.filedata.processingStatus(<?php echo xlj('Committing changes. Please Wait'); ?>);
        vm_file_display.filedata.loading(true);    
        $.post("csv/commit_csv.php",
            {
                lang_id: <?php echo json_encode($lang_id);?>
                ,translations:JSON.stringify(translations)
                ,preview: false
            },
            function(data)
            {
                vm_file_display.filedata.mode("committed");
                vm_file_display.filedata.loading(false);
                vm_file_display.filedata.review_data.unchanged(data.unchanged);
                vm_file_display.filedata.review_data.empty(data.empty);
                vm_file_display.filedata.review_data.changed.removeAll();
                vm_file_display.filedata.review_data.changed(data.changed);                
                vm_file_display.filedata.review_data.changed_html(data.html_changes);                
                vm_file_display.filedata.review_data.updated.removeAll();
                vm_file_display.filedata.review_data.updated(data.updated);
                vm_file_display.filedata.review_data.created.removeAll();
                vm_file_display.filedata.review_data.created(data.created);
                
            }
            ,"json"
        );
}    
</script>


<?php echo activate_lang_tab('csv-link'); ?>
