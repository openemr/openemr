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
        echo '<li><a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText(' .
             "'" . text($strInsert) . $ref . "'" . ');">' . text($strDisp) . '</a></li>';
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
</style>
<?php Header::setupHeader(['common', 'opener', 'select2', 'ckeditor', 'ajax-functions-writer']); ?>
<script>
    let allowTemplateWarning = <?php echo $allowTemplateWarning; ?>;
    <?php if (!$isNN) { ?>
        $(function () {
            $('#contextSearch').select2({
                placeholder: <?php echo xlj('Select Template Context'); ?>,
                width: 'resolve',
                theme: 'bootstrap4',
                ajax: {
                    url: top.webroot_url + '/library/ajax/template_context_search.php',
                    data: function (params) {
                        let query = {
                            search: params.term,
                            csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
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
        });
    <?php } ?>

    function refreshme() {
        top.restoreSession();
        document.location.reload();
    }

    CKEDITOR.config.customConfig = top.webroot_url + '/library/js/nncustom_config.js';

    $(function () {
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
            dlgopen('', '', 725, 500, '', '', {
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
    });
</script>
<script>
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
    $(function () {
        oeSortable(sortableCallback);
    });
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
                    <div class="btn-group" role="group">
                        <a href="#" class="btn btn-secondary btn-sm" id="enter" onclick="top.restoreSession();ascii_write('13','textarea1');" title="<?php echo xla("Enter Key"); ?>"><i class="fas fa-sign-in-alt">&nbsp;</i></a>
                        <a href="#" class="btn btn-secondary btn-sm" id="quest" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('? ');" title="<?php echo xla("Question Mark"); ?>"><i class="fas fa-question-circle"></i></a>
                        <a href="#" class="btn btn-secondary btn-sm" id="para" onclick="top.restoreSession();ascii_write('para','textarea1');" title="<?php echo xla("New Paragraph"); ?>"><i class="fas fa-paragraph"></i></a>
                        <a href="#" class="btn btn-secondary btn-sm" id="space" onclick="top.restoreSession();ascii_write('32','textarea1');" title="<?php echo xla("Space"); ?>"><?php echo xlt("Space"); ?></a>
                    </div>
                    <?php
                    $res = sqlStatement("SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS cl ON cl.cl_list_slno = tu.tu_template_id WHERE tu.tu_user_id = ? AND cl.cl_list_type = 6 AND cl.cl_deleted = 0 ORDER BY cl.cl_order", array($_SESSION['authUserID']));
                    while ($row = sqlFetchArray($res)) { ?>
                        <a href="#" onclick="top.restoreSession();CKEDITOR.instances.textarea1.insertText('<?php echo $row['cl_list_item_short']; ?>');" class="btn btn-primary" title="<?php echo htmlspecialchars(xl($row['cl_list_item_long']), ENT_QUOTES); ?>"><?php echo ucfirst(htmlspecialchars(xl($row['cl_list_item_long']), ENT_QUOTES)); ?></a>
                    <?php } ?>
                    <a class="btn btn-primary btn-sm btn-transmit float-right" href="#" onclick="return SelectToSave(<?php echo attr_js($type); ?>, <?php echo attr_js($cc_flag); ?>)"><?php echo xlt('Insert in Form'); ?></a>
                </div>
              <div class="col-md-4">
                <div class=shadow-lgt">
                    <div style="overflow-y: scroll; overflow-x: hidden; height: 500px">
                        <ul id="menu5" class="example_menu w-100">
                            <li>
                                <a class="expanded"><?php echo htmlspecialchars(xl('Components'), ENT_QUOTES); ?></a>
                                <ul id="template_sentence"></ul>
                            </li>
                            <?php
                            if ($pid != '') :
                                $row = sqlQuery("SELECT p.*, IF(ISNULL(p.providerID), NULL, CONCAT(u.lname,',',u.fname)) pcp " .
                                    "FROM patient_data p LEFT OUTER JOIN users u " .
                                    "ON u.id=p.providerID WHERE pid=?", array($pid));
                                ?>
                                <li>
                                    <a class="collapsed"><?php echo htmlspecialchars(xl('Patient Details'), ENT_QUOTES); ?></a>
                                    <ul>
                                        <?php
                                        $mname = ($row['mname'] != '') ? ' ' . $row['mname'] . ' ' : ' ';
                                        $fullName = $row['fname'] . $mname . $row['lname'];
                                        listitemCode(xl('Full name'), $fullName);
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
                                foreach ($ISSUE_TYPES as $issType => $issTypeDesc) :
                                    $res = sqlStatement('SELECT title, id, IF(diagnosis="","",CONCAT(" [",diagnosis,"]")) codes FROM lists WHERE pid=? AND type=? AND enddate IS NULL ORDER BY title', array($pid, $issType));
                                    if (sqlNumRows($res)) : ?>
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
                                    <?php endif;
                                endforeach;
                            endif; ?>
                        </ul>
                    </div>
                </div>
                <a href="personalize.php?list_id=<?php echo $rowContext['cl_list_id'] ?? ''; ?>" id="personalize_link" class="iframe_medium btn btn-secondary btn-sm"><?php echo xlt("Personalize"); ?></a>
                <a href="add_custombutton.php" id="custombutton" class="iframe_medium btn btn-secondary btn-sm" title="<?php echo htmlspecialchars(xl('Add Buttons for Special Chars,Texts to be Displayed on Top of the Editor for inclusion to the text on a Click'), ENT_QUOTES); ?>"><?php echo htmlspecialchars(xl('Add Buttons'), ENT_QUOTES); ?></a>
              </div>
              <div class="col-md-8">
                <textarea class="ckeditor" cols="130" rows="360" id="textarea1" name="textarea1"></textarea>
                <span class="float-right my-1"><a href="#" onclick="return SelectToSave(<?php echo attr_js($type); ?>, <?php echo attr_js($cc_flag); ?>)" class="btn btn-primary btn-sm btn-save float-right"><?php echo xlt('Insert in Form'); ?></a></span>
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
  <table>
      <script>
          <?php if (!$isNN) { ?>
              CKEDITOR.on('instanceReady', function(){$("#cke_1_toolbar_collapser").click();});
          <?php } ?>
          $(function () {
              edit(<?php echo js_escape($type); ?>, <?php echo js_escape($cc_flag); ?>);
          });
          <?php if ($allowTemplateWarning && !$isNN) { ?>
          // teeheehee
          let msg = xl("These templates are text only and will not render any other formatting other than pure text.") + " ";
          msg += xl("You may still use formatting if template is also used in Nation Notes however, pure text will still render here.") +
              "<br /><br />";
          msg += xl("Click Got it icon to dismiss this alert forever.");
          alertMsg(msg, 10000, 'danger', 'lg', 'disable_template_warning');
          <?php } ?>
      </script>
  </table>
</div>
<template id="componentRow">
    <div class="droppable">
        <li class="draggable">
            <span class="d-flex py-1">
                <a href="#" id="btnEdit" class="p-2 btn btn-sm btn-text d-none"><i class="fa fa-pencil"></i><span class="sr-only"><?php echo xlt("Edit Component"); ?></span></a>
                <div class="flex-fill">
                    <a href="#" class="p-2 d-block"></a>
                </div>
                <a href="#" id="btnDelete" class="p-2 btn btn-sm btn-text d-none"><i class="fa fa-trash"></i><span class="sr-only"><?php echo xlt("Delete Component"); ?></span></a>
            </span>
        </li>
    </div>
</template>
<dialog id="componentEditorDialog" style="resize: both;" class="w-75 border-0 shadow-lg">
    <div class="w-100 d-flex justify-content-between align-items-center">
        <h2 class="flex-fill p-1 m-0 title">
            <?php echo xlt("Add New Component"); ?>
        </h2>
        <a href="#" class="btn btn-secondary p-1 m-0" data-close><i class="fa fa-times"></i></a>
    </div>
    <div class="w-100">
        <div class="form-group">
            <label for="shortName" class="sr-only"><?php echo xlt("Short Name"); ?></label>
            <input type="text" autofocus class="form-control" required placeholder="<?php echo xlt("Short Name"); ?>" name="shortName" id="shortName">
        </div>
        <div class="form-group">
            <label for="componentText" class="sr-only"><?php echo xlt("Component Text"); ?></label>
            <textarea name="componentText" class="form-control" required id="componentText" placeholder="<?php echo xlt("Component Text"); ?>" cols="30" rows="10"></textarea>
        </div>
    </div>
    <div class="w-100 text-right">
        <button type="submit" formmethod="dialog" class="btn btn-primary"><?php echo xlt("Save"); ?></button>
    </div>
</dialog>
<template id="addComponent">
    <li><button data-target='newComponent' class='btn btn-block btn-text'><?php echo xlt("Add New Component"); ?></button></li>
</template>
<script>
let componentEditorState = 0; // Default to insert, 1 for edit
let componentEditorID = null;
const componentMap = new Map();
const componentEditorDialog = document.getElementById("componentEditorDialog");

componentEditorDialog.querySelector("button[type='submit']").addEventListener('click', function (e) {
    event.preventDefault();
    const title = componentEditorDialog.querySelector("input");
    const text = componentEditorDialog.querySelector("textarea");

    if (title.value == "" || text.value == "") {
        title.classList.add("is-invalid");
        text.classList.add("is-invalid");
        return;
    }

    saveComponent();
    componentEditorDialog.close();
});
componentEditorDialog.querySelector("input").addEventListener("input", validateComponentInputs);
componentEditorDialog.querySelector("textarea").addEventListener("input", validateComponentInputs);
componentEditorDialog.querySelector("input").addEventListener("blur", validateComponentInputs);
componentEditorDialog.querySelector("textarea").addEventListener("blur", validateComponentInputs);

function validateComponentInputs(e)
{
    if (e.target.value == "") {
        e.target.classList.add("is-invalid");
    } else {
        e.target.classList.remove("is-invalid");
    }
}

componentEditorDialog.querySelector("a[data-close]").addEventListener("click", function (e) {
    e.preventDefault();
    componentEditorDialog.close();
});

function resetDialog()
{
    componentEditorState = 0;
    componentEditorID = null;
    componentEditorDialog.querySelector(".title").innerText = `<?php echo xlt("Add New Component"); ?>`;
    componentEditorDialog.querySelector("input").value = "";
    componentEditorDialog.querySelector("textarea").value = "";
}

async function saveComponent()
{
    const result = (componentEditorState == 0) ? await save_item() : await update_item(componentEditorID);
    if (result) {
        const r = await TemplateSentence(document.getElementById("template").value);
        processComponents(r);
        resetDialog();
    }
}

async function deleteComponent(id)
{
    const r = await delete_item(id);
    if (r) {
        componentMap.delete(id);
        processComponents(componentMap);
    }
}

function processComponents(components)
{
    document.getElementById("template_sentence").innerHTML = "";
    document.getElementById("template_sentence").append(document.getElementById("addComponent").content.cloneNode(true));
    document.querySelector("button[data-target='newComponent']").addEventListener("click", function (e) {
        e.preventDefault();
        componentEditorDialog.showModal();
    });

    if (components.length > 0) {
        components.map(function (c) {
            let row = document.getElementById("componentRow").content.cloneNode(true);
            row.querySelector("li").id = `clorder_${c.id_attr}`;
            row.querySelector(".flex-fill a").innerText = c.short_text;
            row.querySelector(".flex-fill a").setAttribute("data-component_id", c.id);
            row.querySelector(".flex-fill a").addEventListener("click", function (e) {
                e.preventDefault();
                insertComponentToEditor(c.id);
            });

            if (c.can_configure) {
                // Hook up the edit button
                let btnEdit = row.getElementById("btnEdit");
                btnEdit.id = `edit_${c.id_attr}`;
                btnEdit.classList.remove("d-none");
                btnEdit.addEventListener("click", function (e) {
                    let elm = (e.target.tagName == "I") ? e.target.parentElement : e.target;
                    let cid = elm.id.split("_")[1];
                    componentEditorState = 1; // This is an edit
                    componentEditorID = cid;
                    componentEditorDialog.querySelector(".title").innerText = `<?php echo xlt("Edit"); ?> ${c.short_text}`;
                    componentEditorDialog.querySelector("input").value = c.short_text;
                    componentEditorDialog.querySelector("textarea").value = c.text;
                    componentEditorDialog.showModal();
                });

                // Hook up the delete button
                let btnDelete = row.getElementById("btnDelete");
                btnDelete.id = `delete_${c.id_attr}`;
                btnDelete.classList.remove("d-none");
                btnDelete.addEventListener("click", function (e) {
                    let elm = (e.target.tagName == "I") ? e.target.parentElement : e.target;
                    let cid = elm.id.split("_")[1];
                    deleteComponent(cid);
                });
            }
            document.getElementById("template_sentence").appendChild(row);
            componentMap.set(c.id, c);
        });
        oeSortable(sortableCallback);
    }
}

function insertComponentToEditor(component_id)
{
    const listElement = document.querySelector(`[data-component_id="${component_id}"]`);
    listElement.classList.add("text-info");
    listElement.style.fontStyle = "italic";
    let str = componentMap.get(component_id).text;
    if (window.frames[0].document.body.innerHTML == '<br />')
        window.frames[0].document.body.innerHTML = "";
    var patt = /\?\?/;
    var result = patt.test(str);
    if (result) {
        url = 'quest_popup.php?content=' + str;
        window.open(url, 'quest_pop', 'width=640,height=190,menubar=no,toolbar=0,location=0, directories=0, status=0,left=400,top=375');
        //dlgopen(url,'quest_pop', '', 640, 190);
    } else {
        val = str;
        CKEDITOR.instances.textarea1.insertText(val);
    }
}

const btnInsert = document.querySelectorAll("[data-target='insert']");
btnInsert.forEach(function (btn) {
    btn.addEventListener("click", function (e) {
        const text = btn.getAttribute("data-text");
        CKEDITOR.instances.textarea1.insertText(text);
    });
});

async function processCategoryChange(e)
{
    const r = await TemplateSentence(e.target.value);
    processComponents(r);
}

function quickCategory()
{
    const template = document.getElementById("template");
    if (template.options.length == 2) {
        template.selectedIndex = 1;
        template.dispatchEvent(new Event("change"));
    }
}

window.addEventListener('DOMContentLoaded', function() {
    document.getElementById("componentEditorDialog").addEventListener("close", function(e) {
        resetDialog();
    });

    document.getElementById("template").addEventListener("change", function (e) {
        processCategoryChange(e);
    });

    quickCategory();
});
</script>
</body>
</html>
