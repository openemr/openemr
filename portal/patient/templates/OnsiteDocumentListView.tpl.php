<?php

/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

$pid = $this->cpid;
$recid = $this->recid;
$docid = $this->docid;
$help_id = $this->help_id;
$is_module = $this->is_module;
$is_portal = $this->is_portal;
$is_dashboard = (empty($is_module) && empty($is_portal));
$category = $this->save_catid;
$new_filename = $this->new_filename;
$webroot = $GLOBALS['web_root'];
$encounter = '';
$include_auth = true;
// for location assign
$referer = $GLOBALS['web_root'] . "/controller.php?document&upload&patient_id=" . attr_url($pid) . "&parent_id=" . attr_url($category) . "&";

if (empty($is_module)) {
    $this->assign('title', xlt("Patient Portal") . " | " . xlt("Documents"));
} else {
    $this->assign('title', xlt("Document Templates"));
}
$this->assign('nav', 'onsitedocuments');

$catname = '';
if ($category) {
    $result = sqlQuery("SELECT name FROM categories WHERE id = ?", array($category));
    $catname = $result['name'] ?: '';
}
$catname = $catname ?: xlt("Onsite Portal Reviewed");

if (!$docid) {
    $docid = 'Privacy Document';
}

$isnew = false;
$ptName = $_SESSION['ptName'] ?? $pid;
$cuser = $_SESSION['sessionUser'] ?? $_SESSION['authUserID'];

$templateService = new DocumentTemplateService();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php
    if ($is_dashboard) {
        echo xlt("Portal Document Review");
    } elseif (empty($is_module)) {
        echo xlt("Patient Portal Documents");
    } else {
        echo xlt("Patient Document Templates");
    }
    ?>
    </title>
    <meta name="description" content="Developed By sjpadgett@gmail.com">
    <?php
    // some necessary js globals
    echo "<script>var cpid=" . js_escape($pid) . ";var cuser=" . js_escape($cuser) . ";var ptName=" . js_escape($ptName) .
        ";var catid=" . js_escape($category) . ";var catname=" . js_escape($catname) . ";</script>";
    echo "<script>var recid=" . js_escape($recid) . ";var docid=" . js_escape($docid) . ";var isNewDoc=" . js_escape($isnew) . ";var newFilename=" . js_escape($new_filename) . ";var help_id=" . js_escape($help_id) . ";</script>";
    echo "<script>var isPortal=" . js_escape($is_portal) . ";var isModule=" . js_escape($is_module) . ";var webRoot=" . js_escape($webroot) . ";var webroot_url = webRoot;</script>";
    echo "<script>var csrfTokenDoclib=" . js_escape(CsrfUtils::collectCsrfToken('doc-lib')) . ";</script>";
    // translations
    echo "<script>var alertMsg1='" . xlt("Saved to Patient Documents") . '->' . xlt("Category") . ": " . attr($catname) . "';</script>";
    echo "<script>var msgSuccess='" . xlt("Updates Successful") . "';</script>";
    echo "<script>var msgDelete='" . xlt("Delete Successful") . "';</script>";
    // list of encounter form directories/names (that are patient portal compliant) that use for whitelisting (security)
    echo "<script>var formNamesWhitelist=" . json_encode(CoreFormToPortalUtility::getListPortalCompliantEncounterForms()) . ";</script>";

    Header::setupHeader(['no_main-theme', 'patientportal-style', 'datetime-picker', 'jspdf']);

    ?>
    <link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet">
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signature_pad.umd.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer_api.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/libs/LAB.min.js"></script>
    <script>
        $LAB.setGlobalDefaults({
            BasePath: "<?php $this->eprint($this->ROOT_URL); ?>"
        });
        $LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/underscore/underscore-min.js").script("<?php echo $GLOBALS['assets_static_relative']; ?>/moment/moment.js").script(
            "<?php echo $GLOBALS['assets_static_relative']; ?>/backbone/backbone-min.js").script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").script(
            "<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait().script(
            "<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/view.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
    </script>
    <style>
      @media print {
        #templatecontent {
          width: 1220px;
        }
      }
      .nav-pills-ovr > li > a {
        border: 1px solid !important;
        border-radius: .25rem !important;
      }
    </style>
</head>

<body class="p-0 m-0">
    <script>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4-alternate.js.php'); ?>
        $LAB.script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsitedocuments.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait().script(
            "<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsiteportalactivities.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").
        wait(function () {
            page.init();
            pageAudit.init();
            if (isPortal) {
                $('#Help').on('click', function (e) {
                    e.preventDefault();
                    $(".helpHide").addClass("d-none");
                });
                $("#Help").click();
                $(".helpHide").addClass("d-none");

                $('#showNav').on('click', () => {
                    parent.document.getElementById('topNav').classList.toggle('collapse');
                });
            }
            console.log('init done template');

            setTimeout(function () {
                if (!page.isInitialized) {
                    page.init();
                    if (!pageAudit.isInitialized) {
                        pageAudit.init();
                    }
                }
            }, 2000);
        });

        function printaDoc(divName) {
            flattenDocument();
            divName = 'templatediv';
            let printContents = document.getElementById(divName).innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        function templateText(el) {
            $(el).data('textvalue', $(el).val());
            $(el).attr("data-textvalue", $(el).val())
            return false;
        }

        function templateCheckMark(el) {
            if ($(el).data('value') === 'Yes') {
                $(el).data('value', 'No');
                $(el).attr('data-value', 'No');
            } else {
                $(el).data('value', 'Yes');
                $(el).attr('data-value', 'Yes');
            }
            return false;
        }

        function templateRadio(el) {
            var rid = $(el).data('id')
            $('#rgrp' + rid).data('value', $(el).val());
            $('#rgrp' + rid).attr('data-value', $(el).val());
            $(el).prop('checked', true)
            return false;
        }

        function tfTemplateRadio(el) {
            var rid = $(el).data('id')
            $('#tfrgrp' + rid).data('value', $(el).val());
            $('#tfrgrp' + rid).attr('data-value', $(el).val());
            $(el).prop('checked', true);
            return false;
        }

        function replaceTextInputs() {
            $('.templateInput').each(function () {
                var rv = $(this).data('textvalue');
                $(this).replaceWith(rv);
            });
        }

        function replaceRadioValues() {
            $('.ynuGroup').each(function () {
                var gid = $(this).data('id');
                var grpid = $(this).prop('id');
                var rv = $('input:radio[name="ynradio' + gid + '"]:checked').val();
                $(this).replaceWith(rv);
            });

            $('.tfuGroup').each(function () {
                var gid = $(this).data('id');
                var grpid = $(this).prop('id');
                var rv = $('input:radio[name="tfradio' + gid + '"]:checked').val();
                $(this).replaceWith(rv);
            });
        }

        function replaceCheckMarks() {
            $('.checkMark').each(function () {
                var ckid = $(this).data('id');
                var v = $('#' + ckid).data('value');
                if (v === 'Yes')
                    $(this).replaceWith('[\u2713]')
                else {
                    $(this).replaceWith("[ ]")
                }
            });
        }

        function restoreTextInputs() {
            $('.templateInput').each(function () {
                var rv = $(this).data('textvalue');
                $(this).val(rv)
            });
        }

        function restoreRadioValues() {
            $('.ynuGroup').each(function () {
                var gid = $(this).data('id');
                var grpid = $(this).prop('id');
                var value = $(this).data('value');
                $("input[name=ynradio" + gid + "][value='" + value + "']").prop('checked', true);
            });

            $('.tfuGroup').each(function () {
                var gid = $(this).data('id');
                var grpid = $(this).prop('id');
                var value = $(this).data('value');
                $("input[name=tfradio" + gid + "][value='" + value + "']").prop('checked', true);
            });
        }

        function restoreCheckMarks() {
            $('.checkMark').each(function () {
                var ckid = $(this).data('id');
                if ($('#' + ckid).data('value') === 'Yes')
                    $('#' + ckid).prop('checked', true);
                else
                    $('#' + ckid).prop('checked', false);
            });
        }

        function replaceSignatures() {
            $('.signature').each(function () {
                let type = $(this).data('type');
                if ($(this).attr('src') !== signhere && $(this).attr('src')) {
                    $(this).removeAttr('data-action');
                }
                if (!isPortal) {
                    $(this).attr('data-user', cuser);
                }
            });
        }

        function flattenDocument() {
            replaceCheckMarks();
            replaceRadioValues();
            replaceTextInputs();
            replaceSignatures();
        }

        function restoreDocumentEdits() {
            restoreCheckMarks();
            restoreRadioValues();
            restoreTextInputs();
        }
    </script>
    <div class="container-xl px-1">
        <nav id="verytop" class="navbar navbar-expand-lg navbar-light bg-light px-1 pt-3 pb-1 m-0 sticky-top" style="z-index:1030;">
            <a class="navbar-brand mt-1 mr-1"><h3><?php echo xlt("My Documents") ?></h3></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#topmenu" aria-controls="topmenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="topmenu" class="collapse navbar-collapse">
                <ul class="navbar-nav navCollapse mr-auto">
                    <!-- Sticky actions toolbar -->
                    <div class='helpHide d-none'>
                        <ul class="navbar-nav">
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="signTemplate" href="#openSignModal" data-toggle="modal" data-backdrop="true" data-target="#openSignModal" data-type="patient-signature"><?php echo xlt('Edit Signature'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="saveTemplate" href="#"><?php echo xlt('Save'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="printTemplate" href="javascript:;" onclick="printaDoc('templatecontent');"><?php echo xlt('Print'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="submitTemplate" href="#"><?php echo xlt('Download'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="sendTemplate" href="#"><?php echo xlt('Submit Document'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="chartTemplate" href="#"><?php echo xlt('Chart to') . ' ' . text($catname); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="downloadTemplate" href="#"><?php echo xlt('Download'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="chartHistory" href="#"><?php echo xlt('Chart History'); ?></a></li>
                        </ul>
                    </div>
                    <?php if (!empty($is_module) || !empty($is_portal)) { ?>
                        <div class="dropdown mb-1">
                            <a class="dropdown-toggle nav-link btn btn-outline-success text-success" href="#" role="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo xlt('Select Documents') ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenu">
                                <?php echo $templateService->renderPortalTemplateMenu($pid, $cuser, true); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <li class='nav-item mb-1'>
                        <a class='nav-link text-success btn btn-outline-success' onclick="page.handleHistoryView()">
                            <?php echo xlt('History') ?>
                            <i class="history-direction ml-1 fa fa-arrow-down"></i>
                        </a>
                    </li>
                    <?php if (empty($is_module)) { ?>
                        <li class="nav-item mb-1">
                            <a id="Help" class="nav-link text-primary btn btn-outline-primary d-none" onclick='page.newDocument(cpid, cuser, "Help", help_id);'><?php echo xlt('Help'); ?></a>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-danger btn btn-secondary" id="a_docReturn" href="#" onclick='window.location.replace("<?php echo $referer ?>")'><?php echo xlt('Return'); ?></a>
                        </li>
                    <?php } ?>
                    <li class='nav-item mb-1'>
                        <a class='nav-link btn btn-secondary' data-toggle='tooltip' title='Refresh' id='refreshPage' href='javascript:' onclick='window.location.reload()'> <span class='fa fa-sync fa-lg'></span></a>
                    </li>
                    <li class='nav-item mb-1'>
                        <a id='showNav' class='nav-link btn btn-secondary'><span class='navbar-toggler-icon mr-1'></span><?php echo xlt('Menu'); ?></a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="d-flex flex-row justify-content-center">
            <!-- Pending documents left menu Deprecated and removed 01/13/22 -->
            <div class="clearfix" id="topnav">
                <div id="collectionAlert"></div>
            </div>
            <!-- Right editor container -->
            <div id="editorContainer" class="d-flex flex-column w-100 h-auto">
                <!-- document editor and action toolbar template -->
                <script type="text/template" id="onsiteDocumentModelTemplate">
                    <div class="card m-0 p-0" id="docpanel">
                        <!-- Document edit container -->
                        <header class="card-header bg-dark text-light helpHide" id='docPanelHeader'><?php echo xlt('Editing'); ?></header>
                        <!-- editor form -->
                        <form class="container-xl p-0" id='template' name='template' role="form" action="./../lib/doc_lib.php" method="POST">
                            <div id="templatediv" class="card-body border overflow-auto">
                                <div id="templatecontent" class="template-body">
                                    <div class="text-center overflow-hidden"><i class="fa fa-circle-notch fa-spin fa-2x ml-auto"></i></div>
                                </div>
                            </div>
                            <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('doc-lib')); ?>" />
                            <input type="hidden" name="content" id="content" value="" />
                            <input type="hidden" name="cpid" id="cpid" value="" />
                            <input type="hidden" name="docid" id="docid" value="" />
                            <input type='hidden' name='template_id' id='template_id' value='' />
                            <input type="hidden" name="handler" id="handler" value="download" />
                            <input type="hidden" name="status" id="status" value="Open" />
                        </form>
                        <div class="clearfix">
                            <span>
                                <button id="dismissOnsiteDocumentButton" class="btn btn-secondary float-right" onclick="window.location.reload()"><?php echo xlt('Dismiss Form'); ?></button>
                            </span>
                            <!-- delete button is a separate form to prevent enter key from triggering a delete-->
                            <form id="deleteOnsiteDocumentButtonContainer" class="form-inline" onsubmit="return false;">
                                <fieldset>
                                    <div class="form-group">
                                        <label class="col-form-label"></label>
                                        <div class="controls">
                                            <button id="deleteOnsiteDocumentButton" class="btn btn-sm btn-danger"><i class="icon-trash icon-white"></i><?php echo xlt('Delete Document'); ?></button>
                                            <span id="confirmDeleteOnsiteDocumentContainer">
                                                <button id="cancelDeleteOnsiteDocumentButton" class="btn btn-link btn-sm"><?php echo xlt('Cancel'); ?></button>
                                                <button id="confirmDeleteOnsiteDocumentButton" class="btn btn-sm btn-danger"><?php echo xlt('Confirm'); ?></button>
                                          </span>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                </script>
                <div id="onsiteDocumentModelContainer" class="modelContainer">
                    <!-- rendered edit document and action toolbar template -->
                </div>
            </div><!-- close flex right-->
        </div><!-- close flex row -->

        <!-- Now history table container template -->
        <script type="text/template" id="onsiteDocumentCollectionTemplate">
            <div class="table-responsive pt-3">
                <h4 class="text-sm-center"><?php echo xlt('Your Document History') ?><small> (Click on label to sort.)</small></h4>
                <table class="collection table table-sm table-hover">
                    <thead class='thead-dark'>
                    <tr class='cursor-pointer'>
                        <th scope="col" id="header_Id"><?php echo xlt('Id'); ?><% if (page.orderBy == 'Id') { %> <i class='icon-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_DocType"><?php echo xlt('Document'); ?><% if (page.orderBy == 'DocType') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_CreateDate"><?php echo xlt('Create Date'); ?><% if (page.orderBy == 'CreateDate') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_ReviewDate"><?php echo xlt('Reviewed Date'); ?><% if (page.orderBy == 'ReviewDate') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_DenialReason"><?php echo xlt('Review Status'); ?><% if (page.orderBy == 'DenialReason') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_PatientSignedStatus"><?php echo xlt('Signed'); ?><% if (page.orderBy == 'PatientSignedStatus') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_PatientSignedTime"><?php echo xlt('Signed Date'); ?><% if (page.orderBy == 'PatientSignedTime') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                    </tr>
                    </thead>
                    <tbody>
                    <% items.each(function(item) {  %>
                    <tr id="<%= _.escape(item.get('id')) %>">
                        <th scope="row"><%= _.escape(item.get('id') || '') %></th>
                        <td>
                            <button class='btn btn-sm btn-outline-success history-btn'><%= _.escape(item.get('docType') || '') %></button>
                        </td>
                        <td><%if (item.get('createDate')) { %><%= item.get('createDate') %><% } else { %>NULL<% } %></td>
                        <td><%if (item.get('reviewDate') > '1969-12-31 24') { %><%= item.get('reviewDate') %><% } else { %>Pending<% } %></td>
                        <td><%= _.escape(item.get('denialReason') || 'Pending') %></td>
                        <td><%if (item.get('patientSignedStatus')=='1') { %><%= 'Yes' %><% } else { %>No<% } %></td>
                        <td><%if (item.get('patientSignedTime') > '1969-12-31 24') { %><%= item.get('patientSignedTime') %><% } else { %>Pending<% } %></td>
                    </tr>
                    <% }); %>
                    </tbody>
                </table>
                <%= view.getPaginationHtml(page) %>
            </div>
            </div>
        </script>
        <div class="container-lg px-3 pt-3 historyHide d-none" id="historyTable">
            <div id="onsiteDocumentCollectionContainer" class="collectionContainer"><!-- rendered history template --></div>
        </div>
    </div>
    <?php
    // footer close body html
    //$this->display('_Footer.tpl.php');
    ?>
