<?php

// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Jacob T Paul <jacob@zhservices.com>
//           Jerry Padgett <sjpadgett@gmail.com> 2019-2021
//
// +------------------------------------------------------------------------------+

require_once("../../interface/globals.php");
require_once("$srcdir/lists.inc.php");
require_once("$srcdir/user.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Common\Csrf\CsrfUtils;

// mdsupport : li code
function listitemCode($strDisp, $strInsert, $ref = '')
{
    if ($strInsert) {
        if (!empty($ref)) {
            $id = text($ref);
            $ref = " {|$id|}";
        }
        echo '<li><a href="#" class="btn-template-insert" data-template-text="'
            . attr($strInsert . $ref) . '">' . text($strDisp) . '</a></li>';
    }
}

$allowTemplateWarning = checkUserSetting('disable_template_warning', '1') === true ? 0 : 1;
$contextName = !empty($_GET['contextName']) ? $_GET['contextName'] : '';
$type = $_GET['type'] ?? '';
$cc_flag = $_GET['ccFlag'] ?? '';

$isNN = empty($cc_flag) ? 1 : 0;
if (empty($isNN)) {
    $contextName = empty($contextName) ? "Encounters" : $contextName;
}
// either NN context from layout or text template default.
$rowContext = sqlQuery("SELECT * FROM customlists WHERE cl_list_type = 2 AND cl_list_item_long = ?", array($contextName));
if (empty($isNN) && empty($rowContext)) {
    $contextName .= " <small><em>(" . xlt("Add Missing Context Template.") . ")</em></small>";
}
?>
<html>
<head>
<style>
    .draggable {
        cursor: pointer !important;
    }
    .is-dragging {
        cursor: move !important;
    }
    .ck-editor__editable {
        resize: vertical !important;
        min-height: 200px;
        max-height: 500px;
        overflow: auto;
    }
</style>
<?php
$ckeditorConfig = "ckeditor-limited";
if ($isNN) {
    $ckeditorConfig = "ckeditor-nation-notes";
}
    Header::setupHeader(['common', 'opener', 'select2', 'ckeditor', $ckeditorConfig]);
?>
<script src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajax_functions_writer.js"></script>

<script>
    // note these variables are set on backend server side, leaving comment for server side readers
    const isNationNotes = <?php echo $isNN ? "true" : "false"; ?>;
    const dataAsPlainText = !isNationNotes;
    const csrfToken = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
    const allowTemplateWarning = <?php echo $allowTemplateWarning ? "true" : "false"; ?>;
    function refreshme() {
        top.restoreSession();
        document.location.reload();
    }
    $(function () {
        if (!isNationNotes) {
            $('#contextSearch').select2({
                placeholder: <?php echo xlj('Select Template Context'); ?>,
                width: 'resolve',
                theme: 'bootstrap4',
                ajax: {
                    url: top.webroot_url + '/library/ajax/template_context_search.php',
                    data: function (params) {
                        let query = {
                            search: params.term,
                            csrf_token_form: csrfToken
                        };
                        return query;
                    },
                    dataType: 'json',
                },
                <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
            });

            $('#contextSearch').on('select2:select', function (e) {
                let data = e.params.data;
                top.restoreSession();
                $("#contextName").val(data.text);
                $("#mainForm").submit();
            });
        }
        const {
            ClassicEditor
        } = CKEDITOR;
        // ajax_function_writer.js::getCallingDocumentEditorContent
        let initialData = getCallingDocumentEditorContent(<?php echo js_escape($type); ?>, <?php echo js_escape($cc_flag); ?>);

        const config = Object.assign({}, window.oeCKEditorConfigs.defaultConfig, {initialData: initialData});
        // window.oeCKEditorConfigs.defaultConfig comes from the ckeditor-limited or ckeditor-nation-notes config file
        ClassicEditor
            .create( document.querySelector( '#textarea1' ), config)
            .then( editor => {
                // ajax_function_writer.js::initAjaxFunctionWritersWithEditor
                initAjaxFunctionWritersWithEditor(editor);
                // Focus the editor on page load
                editor.editing.view.focus();
                let btnTemplateInserts = document.querySelectorAll('.btn-template-insert');
                btnTemplateInserts.forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        let targetButton = e.currentTarget;
                        e.preventDefault();
                        e.stopPropagation();
                        top.restoreSession();
                        let txt = targetButton.getAttribute('data-template-text');
                        console.log("text is ", txt);
                        console.log("button is ", targetButton);
                        editor.model.change( (writer) => {
                            // make sure we are only insert text content
                            const textNode = writer.createText(txt);
                            editor.model.insertContent(textNode);
                        } );
                    });
                });

                let btnInsertForms = document.querySelectorAll(".btn-transmit");
                btnInsertForms.forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        top.restoreSession();
                        let insertValue = "";
                        if (dataAsPlainText) {
                            insertValue = editor.editing.view.getDomRoot().innerText
                        } else {
                            insertValue = editor.getData();
                        }
                        // TODO: seems like it'd be better to just inline this function here in this file, but for expediency sake, going to bring this in.
                        SelectToSave(<?php echo js_escape($type); ?>, <?php echo js_escape($cc_flag); ?>, insertValue);
                    });
                });
            })
            .catch(error => console.error(error));
        tabbify();

        $(".iframe_small").on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dlgopen('', '', 330, 120, '', '', {
                buttons: [
                    {text: '<?php echo xla('Close'); ?>', close: true, style: 'secondary btn-sm'}
                ],
                onClosed: 'refreshme',
                type: 'iframe',
                url: $(this).attr('href')
            });
        });

        $(".iframe_medium").on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dlgopen('', '', 725, 575, '', '', {
                buttons: [
                    {text: '<?php echo xla('Close'); ?>', close: true, style: 'secondary btn-sm'}
                ],
                onClosed: 'refreshme',
                type: 'iframe',
                url: $(this).attr('href')
            });
        });

        $(".iframe_abvmedium").on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dlgopen('', '', 700, 500, '', '', {
                buttons: [
                    {text: '<?php echo xla('Close'); ?>', close: true, style: 'secondary btn-sm'}
                ],
                onClosed: 'refreshme',
                type: 'iframe',
                url: $(this).attr('href')
            });
        });

        $("#menu5 > li > a.expanded + ul").slideToggle("medium");
        $("#menu5 > li > a").click(function () {
            $("#menu5 > li > a.expanded").not(this).toggleClass("expanded").toggleClass("collapsed").parent().find('> ul').slideToggle("medium");
            $(this).toggleClass("expanded").toggleClass("collapsed").parent().find('> ul').slideToggle("medium");
        });

        function sortableCallback(elem){
            let clorder  = [];
            for (let i=0; i< elem.length; i++) {
                let ele = elem[i];
                if(ele.tagName == "DIV"){
                    clorder.push("clorder[]="+ele.firstElementChild.id.split("_")[1]);
                }
            }
            $.post("updateDB.php", clorder.join('&')+"&action=updateRecordsListings");
        }
        oeSortable(sortableCallback);

        // let's popup a warning dialog if we're in a context that is text only templates
        if (allowTemplateWarning && !isNationNotes) {
            // teeheehee
            let msg = xl("These templates are text only and will not render any other formatting other than pure text.") + " ";
            msg += xl("You may still use formatting if template is also used in Nation Notes however, pure text will still render here.") +
                "<br /><br />";
            msg += xl("Click Got it icon to dismiss this alert forever.");
            alertMsg(msg, 10000, 'danger', 'lg', 'disable_template_warning');
        }
    });
</script>
<script>
    <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script>
</head>
<body class="body_top">
<div class="container-fluid">
  <input type="hidden" name="list_id" id="list_id" value="<?php echo $rowContext['cl_list_id'] ?? ''; ?>" />
  <?php if (($rowContext['cl_list_item_long'] ?? null) || !$isNN) { ?>
  <!-- don't escape $contextName it's html -->
  <h3 class="text-center"><?php echo (text($rowContext['cl_list_item_long'] ?? ''))  ?: $contextName; ?></h3>
    <div id="tab1" class="tabset_content tabset_content_active">
        <form id="mainForm">
            <input type="hidden" name="type" id="type" value="<?php echo  attr($type); ?>" />
            <input type="hidden" name="ccFlag" id="type" value="<?php echo  attr($cc_flag); ?>" />
            <input type="hidden" name="contextName" id="contextName" value="<?php echo attr($contextName); ?>" />
            <div class="row">
              <div class="col-md-12">
                <?php if (!$isNN) { ?>
                <div id="searchCriteria">
                    <div class="select-box form-inline mb-1">
                        <label for="contextId"><?php echo xlt('Context') . ':'; ?></label>
                        <select id="contextSearch" name="contextId" class="form-control form-control-sm w-50">
                            <option value=""></option>
                        </select>
                    </div>
                </div>
                <?php } ?>
              </div>
              <div class="col-md-4 text mb-2" id="templateDD">
                <select class="form-control form-control-sm" name="template" id="template" onchange="TemplateSentence(this.value)">
                    <option value=""><?php echo htmlspecialchars(xl('Select category'), ENT_QUOTES); ?></option>
                    <?php
                    $resTemplates = sqlStatement("SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno WHERE tu.tu_user_id=? AND c.cl_list_type=3 AND cl_list_id=? AND cl_deleted=0 ORDER BY c.cl_list_item_long", array($_SESSION['authUserID'], ($rowContext['cl_list_id'] ?? null)));
                    while ($rowTemplates = sqlFetchArray($resTemplates)) {
                        echo "<option value='" . htmlspecialchars($rowTemplates['cl_list_slno'], ENT_QUOTES) . "'>" . htmlspecialchars(xl($rowTemplates['cl_list_item_long']), ENT_QUOTES) . "</option>";
                    }
                    ?>
                </select>
              </div>
              <div class="col-md-8 text mb-1">
                <div id="share" style="display:none"></div>
                <?php
                $res = sqlStatement("SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS cl ON cl.cl_list_slno = tu.tu_template_id WHERE tu.tu_user_id = ? AND cl.cl_list_type = 6 AND cl.cl_deleted = 0 ORDER BY cl.cl_order", array($_SESSION['authUserID']));
                while ($row = sqlFetchArray($res)) { ?>
                    <a href="#" class="btn btn-primary btn-template-insert" data-template-text="<?php echo attr($row['cl_list_item_short']); ?>" title="<?php echo htmlspecialchars(xl($row['cl_list_item_long']), ENT_QUOTES); ?>"><?php echo ucfirst(htmlspecialchars(xl($row['cl_list_item_long']), ENT_QUOTES)); ?></a>
                <?php } ?>
                  <a class="btn btn-primary btn-sm btn-transmit float-right" href="#"><?php echo xlt('Insert in Form'); ?></a>
              </div>
              <div class="col-md-4">
                <div class="bg-light">
                    <div style="overflow-y: scroll; overflow-x: hidden; height: 400px">
                        <ul id="menu5" class="example_menu w-100">
                            <li>
                                <a class="expanded"><?php echo htmlspecialchars(xl('Components'), ENT_QUOTES); ?></a>
                                <ul>
                                    <div id="template_sentence"></div>
                                </ul>
                            </li>
                            <?php
                            if ($pid != '') {
                                $row = sqlQuery("SELECT p.*, IF(ISNULL(p.providerID), NULL, CONCAT(u.lname,',',u.fname)) pcp " .
                                    "FROM patient_data p LEFT OUTER JOIN users u " .
                                    "ON u.id=p.providerID WHERE pid=?", array($pid));
                                ?>
                                <li>
                                    <a class="collapsed"><?php echo htmlspecialchars(xl('Patient Details'), ENT_QUOTES); ?></a>
                                    <ul>
                                        <?php
                                        listitemCode(xl('First name'), $row['fname']);
                                        listitemCode(xl('Last name'), $row['lname']);
                                        listitemCode(xl('Phone'), $row['phone_home']);
                                        listitemCode(xl('SSN'), $row['ss']);
                                        listitemCode(xl('Date Of Birth'), $row['DOB']);
                                        listitemCode(xl('PCP'), $row['pcp']);
                                        ?>
                                    </ul>
                                </li>
                                <?php
                                foreach ($ISSUE_TYPES as $issType => $issTypeDesc) {
                                    $res = sqlStatement('SELECT title, id, IF(diagnosis="","",CONCAT(" [",diagnosis,"]")) codes FROM lists WHERE pid=? AND type=? AND enddate IS NULL ORDER BY title', array($pid, $issType));
                                    if (sqlNumRows($res)) { ?>
                                    <li>
                                        <a class="collapsed"><?php echo htmlspecialchars(xl($issTypeDesc[0]), ENT_QUOTES); ?></a>
                                        <ul>
                                            <?php
                                            while ($row = sqlFetchArray($res)) {
                                                if (!empty($isNN)) {
                                                    $row['id'] = "";
                                                }
                                                listitemCode((strlen($row['title']) > 20) ? (substr($row['title'], 0, 18) . '..') : $row['title'], ($row['title'] . $row['codes']), $row['id']);
                                            }
                                            ?>
                                        </ul>
                                    </li>
                                    <?php }
                                }
                            } ?>
                        </ul>
                    </div>
                </div>
                <a href="personalize.php?list_id=<?php echo $rowContext['cl_list_id'] ?? ''; ?>" id="personalize_link" class="iframe_medium btn btn-primary btn-sm"><?php echo htmlspecialchars(xl('Personalize'), ENT_QUOTES); ?></a>
                <a href="add_custombutton.php" id="custombutton" class="iframe_medium btn btn-primary btn-sm" title="<?php echo htmlspecialchars(xl('Add Buttons for Special Chars,Texts to be Displayed on Top of the Editor for inclusion to the text on a Click'), ENT_QUOTES); ?>"><?php echo htmlspecialchars(xl('Add Buttons'), ENT_QUOTES); ?></a>
              </div>
              <div class="col-md-8">
                <textarea class="ckeditor mb-5" cols="100" rows="180"
                          id="textarea1" name="textarea1"></textarea>
                <span class="float-right my-1"><a href="#" class="btn btn-primary btn-transmit btn-sm btn-save float-right"><?php echo xlt('Insert in Form'); ?></a></span>
              </div>
            </div>
        </form>
    </div>
        <?php
  } else {
      echo htmlspecialchars(xl('NO SUCH CONTEXT NAME') . $contextName, ENT_QUOTES);
      exit();
  }
    ?>
</div>
</body>
</html>
