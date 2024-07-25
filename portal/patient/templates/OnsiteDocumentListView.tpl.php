<?php

/**
 * Patient Portal Documents
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

$pid = $this->cpid;
$doc_edit = $this->doc_edit;
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
$auto_render = $this->auto_render ?? 0;
$audit_render = $this->audit_render ?? 0;
$auto_render_name = $this->auto_render_name ?? '';
// for location assign
$referer = $GLOBALS['web_root'] . "/controller.php?document&upload&patient_id=" . attr_url($pid) . "&parent_id=" . attr_url($category) . "&";
$referer_portal = "../home.php?site=" . (urlencode($_SESSION['site_id']) ?? null) ?: 'default';

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
    $csrf_php = js_escape(CsrfUtils::collectCsrfToken('doc-lib'));
    $urlAjax = $GLOBALS['web_root'] . '/library/ajax/upload.php?parent_id=Patient&patient_id=' . attr_url($pid);
    // some necessary js globals
    echo "<script>var cpid=" . js_escape($pid) . ";var cuser=" . js_escape($cuser) . ";var ptName=" . js_escape($ptName) .
        ";var autoRender=" . js_escape($auto_render) . ";var auditRender=" . js_escape($audit_render) . ";var renderDocumentName=" . js_escape($auto_render_name) .
        ";var catid=" . js_escape($category) . ";var catname=" . js_escape($catname) . ";</script>";
    echo "<script>var recid=" . js_escape($recid) . ";var docid=" . js_escape($docid) . ";var isNewDoc=" . js_escape($isnew) . ";var newFilename=" . js_escape($new_filename) . ";var help_id=" . js_escape($help_id) . ";</script>";
    echo "<script>var isPortal=" . js_escape($is_portal) . ";var isModule=" . js_escape($is_module) . ";var webRoot=" . js_escape($webroot) . ";var doc_edit=" . js_escape($doc_edit) . ";var webroot_url = webRoot;</script>";
    echo "<script>var csrfTokenDoclib=" . $csrf_php . ";</script>";
    // translations
    echo "<script>var alertMsg1='" . xlt("Saved to Patient Documents") . '->' . xlt("Category") . ": " . attr($catname) . "';</script>";
    echo "<script>var msgSuccess='" . xlt("Updates Successful") . "';</script>";
    echo "<script>var msgDelete='" . xlt("Delete Successful") . "';</script>";
    // list of encounter form directories/names (that are patient portal compliant) that use for whitelisting (security)
    echo "<script>var formNamesWhitelist=" . json_encode(CoreFormToPortalUtility::getListPortalCompliantEncounterForms()) . ";</script>";

    if ($is_portal) {
        Header::setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker']);
    } else {
        Header::setupHeader(['datetime-picker']);
    }
    ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $GLOBALS['v_js_includes']; ?>">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/dropzone/dist/dropzone.css?v=<?php echo $GLOBALS['v_js_includes']; ?>">
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/dropzone/dist/dropzone.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
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

      .dz-remove {
        font-size: 16px;
        color: var(--danger);
      }

      .dz-progress {
        opacity: 0.2 !important;
      }
    </style>
</head>

<body class="p-0 m-0 mt-1">
    <script>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4-alternate.js.php'); ?>
        $LAB.script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsitedocuments.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait().script(
            "<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsiteportalactivities.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").
        wait(function () {
            page.init();
            pageAudit.init();
            if (isPortal) {
                $(".template-body").addClass("bg-light");
                $(".template-body").addClass("text-dark");
                $('#Help').on('click', function (e) {
                    e.preventDefault();
                    $(".helpHide").addClass("d-none");
                });
                $(".helpHide").addClass("d-none");
                $(parent.document.getElementById('topNav')).addClass("d-none");
                if (autoRender < 1 && auditRender < 1) {
                    $("#Help").click();
                }
            }
            setTimeout(function () {
                if (!page.isInitialized) {
                    page.init();
                    if (!pageAudit.isInitialized) {
                        pageAudit.init();
                        console.log('secondary init done!');
                    }
                }
                if (isPortal) {
                    /* Render may start as a new document onetime request however for the sake
                    *  of allowing patient to stay in portal after doc edit or patient uses
                    *  same onetime that started as a new doc to come back and
                    *  continue an edit of a saved/submitted doc.
                    *  auditRender is a history doc.
                    *
                    *  CONFUSED! Welcome.
                    * */
                    if (autoRender > 0 && auditRender <= 0) {
                        // is it in menu?
                        if ($("#" + autoRender).data('history_id') > 0) {
                            // has it been submitted?
                            let historyId = $("#" + autoRender).data('history_id');
                            page.editHistoryDocument(historyId);
                            console.log('Onetime history template id ' + historyId);
                        } else {
                            page.newDocument(cpid, "-patient-", renderDocumentName, autoRender);
                            console.log('Onetime new template init');
                        }
                    } else if (auditRender > 0) {
                        page.editHistoryDocument(auditRender);
                        console.log('Onetime history template init');
                    }
                    if (!newFilename) { // autoload new on init. once only.
                        page.initFileDrop();
                    }
                }
                if (newFilename) {
                    console.log('Call template from module');
                    if (doc_edit == '0' && recid > 0) {
                        page.newDocument(cpid, cuser, newFilename, recid);
                        newFilename = '';
                    } else if (doc_edit == '1' && recid > 0) {
                        // For now will ignore editing documents from module.
                        // I don't feel like it's stable.
                        //page.editHistoryDocument(recid);
                        page.newDocument(cpid, cuser, newFilename, recid);
                        newFilename = '';
                    }
                }
            }, 1000);
        }).wait(function () {
            console.log('init 2 done template');
        });

        function printaDocHtml(divName) {
            page.updateModel();
            setTimeout("flattenDocument();", 3000);
            divName = 'templatediv';
            let printContents = document.getElementById(divName).innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        function printaDoc(divName) {
            // We'll return to the same editing state as before print
            // In dashboard document is already flatten to prevent
            // auditor from changing patient entries!
            if (page.isQuestionnaire && !isPortal) {
                url = webroot_url +
                    "/interface/forms/questionnaire_assessments/patient_portal.php" +
                    "?formid=" + encodeURIComponent(page.encounterFormId);
                fetch(url).then(response => {
                    if (!response.ok) {
                        throw new Error('Network Error.');
                    }
                    return response.json()
                }).then(content => {
                    if (content) {
                        let docid = document.getElementById('docid').value;
                        fetchPdf(divName, docid, content);
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert(error);
                });
            } else {
                let docid = document.getElementById('docid').value;
                fetchPdf(divName, docid);
            }
        }

        function fetchPdf(divName, docid, printContents = null) {
            let csrf_token_js = <?php echo js_escape(CsrfUtils::collectCsrfToken('doc-lib')); ?>;
            top.restoreSession();
            if (document.getElementById('tempFrame')) {
                let killFrame = document.getElementById('tempFrame');
                killFrame.parentNode.removeChild(killFrame);
            }
            if (!printContents) {
                printContents = document.getElementById(divName).innerHTML;
            }
            const request = new FormData;
            request.append("handler", "fetch_pdf");
            request.append("docid", docid);
            request.append("content", printContents);
            request.append("csrf_token_form", csrf_token_js);
            fetch(webroot_url + "/portal/lib/doc_lib.php", {
                method: 'POST',
                credentials: 'same-origin',
                body: request
            }).then((response) => {
                if (response.status !== 200) {
                    console.log('Background Service start failed. Status Code: ' + response.status);
                }
                return response.text();
            }).then((base64) => {
                const binary = atob(base64.replace(/\s/g, ''));
                const len = binary.length;
                const buffer = new ArrayBuffer(len);
                const view = new Uint8Array(buffer);
                for (let i = 0; i < len; i++) {
                    view[i] = binary.charCodeAt(i);
                }
                const blob = new Blob([view], {type: "application/pdf"});
                const url = URL.createObjectURL(blob);
                let iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.width = '0';
                iframe.height = '0';
                iframe.id = 'tempFrame';
                document.body.appendChild(iframe);
                iframe.onload = function () {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                }
                // write the content
                iframe.src = url;
            }).catch(function (error) {
                console.log('PHP PDF Background Service Request failed: ', error);
                return false;
            });
        }

        // Many of these functions are now deprecated and will stay for legacy.
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
            let rid = $(el).data('id')
            $('#rgrp' + rid).data('value', $(el).val());
            $('#rgrp' + rid).attr('data-value', $(el).val());
            $(el).prop('checked', true)
            return false;
        }

        function tfTemplateRadio(el) {
            let rid = $(el).data('id')
            $('#tfrgrp' + rid).data('value', $(el).val());
            $('#tfrgrp' + rid).attr('data-value', $(el).val());
            $(el).prop('checked', true);
            return false;
        }

        function replaceTextInputs() {
            $('.templateInput').each(function () {
                let rv = $(this).data('textvalue');
                $(this).replaceWith(jsText(rv));
            });
        }

        function replaceRadioValues() {
            $('.ynuGroup').each(function () {
                let gid = $(this).data('id');
                let grpid = $(this).prop('id');
                let rv = $('input:radio[name="ynradio' + jsAttr(gid) + '"]:checked').val();
                $(this).replaceWith(rv);
            });

            $('.tfuGroup').each(function () {
                let gid = $(this).data('id');
                let grpid = $(this).prop('id');
                let rv = $('input:radio[name="tfradio' + jsAttr(gid) + '"]:checked').val();
                $(this).replaceWith(rv);
            });
        }

        function replaceCheckMarks() {
            $('.checkMark').each(function () {
                let ckid = $(this).data('id');
                let v = $('#' + ckid).data('value');
                if (v === 'Yes')
                    $(this).replaceWith('[\u2713]')
                else {
                    $(this).replaceWith("[ ]")
                }
            });
        }

        function restoreTextInputs() {
            $('.templateInput').each(function () {
                let rv = $(this).data('textvalue');
                $(this).val(rv)
            });
        }

        function restoreRadioValues() {
            $('.ynuGroup').each(function () {
                let gid = $(this).data('id');
                let grpid = $(this).prop('id');
                let value = $(this).data('value');
                $("input[name=ynradio" + gid + "][value='" + value + "']").prop('checked', true);
            });

            $('.tfuGroup').each(function () {
                let gid = $(this).data('id');
                let grpid = $(this).prop('id');
                let value = $(this).data('value');
                $("input[name=tfradio" + gid + "][value='" + value + "']").prop('checked', true);
            });
        }

        function restoreCheckMarks() {
            $('.checkMark').each(function () {
                let ckid = $(this).data('id');
                if ($('#' + ckid).data('value') === 'Yes')
                    $('#' + ckid).prop('checked', true);
                else {
                    $('#' + ckid).prop('checked', false);
                }
            });
        }

        function replaceSignatures() {
            $('.signature').each(function () {
                if ($(this).attr('src') !== signhere && $(this).attr('src')) {
                    $(this).removeAttr('data-action');
                }
                if (!isPortal) {
                    $(this).attr('data-user', cuser);
                }
            });
        }

        function formReplaceCheckMarks() {
            $('.checkMark').each(function () {
                let v = $(this).is(':checked');
                if (v)
                    $(this).replaceWith(' [\u2713] ')
                else {
                    $(this).replaceWith(" [ ] ")
                }
            });
        }

        function formReplaceRadioValues() {
            $('.ynuGroup').each(function () {
                let name = $(this).prop('id');
                let rv = $('input:radio[name="' + jsAttr(name) + '"]:checked').val();
                $(this).replaceWith(rv);
            });

            $('.tfuGroup').each(function () {
                let name = $(this).prop('id');
                let rv = $('input:radio[name="' + jsAttr(name) + '"]:checked').val();
                $(this).replaceWith(rv);
            });
        }

        function formReplaceTextInputs() {
            $('.templateInput').each(function () {
                let rv = $(this).val();
                $(this).replaceWith(jsText(rv));
            });
        }

        // A simple (being facetious!) await!.
        const flattenDocumentAsync = async () => {
            if (page.version === 'Legacy') {
                replaceCheckMarks();
                replaceRadioValues();
                replaceTextInputs();
                replaceSignatures();
            } else {
                formReplaceTextInputs();
                formReplaceCheckMarks();
                formReplaceRadioValues();
                replaceSignatures()
            }
            page.isFlattened = true;
        }

        const flattenDocument = async () => {
            await flattenDocumentAsync();
            page.isFlattened = true;
        }

        function restoreDocumentEdits() {
            restoreCheckMarks();
            restoreRadioValues();
            restoreTextInputs();
            page.isFlatten = false;
            page.isSaved = false;
        }
    </script>
    <div class="container-xl px-1">
        <div class="text-center"> <span class="h3 mt-1 mr-1"><?php echo xlt("Documents and Forms") ?></span>
                        <a class="btn btn-outline-primary mb-1" id="a_docReturn" href="#" onclick='window.location.replace(<?php echo attr_js($referer_portal) ?>)'><?php echo xlt('Exit to Dashboard'); ?></a></div>
        <nav id="verytop" class="navbar navbar-expand-lg navbar-light bg-light px-1 pt-3 pb-1 m-0 sticky-top" style="z-index:1030;">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#topmenu" aria-controls="topmenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="topmenu" class="collapse navbar-collapse">
                <ul class="navbar-nav navCollapse mr-auto">
                    <!-- Sticky actions toolbar -->
                    <div class='helpHide d-none'>
                        <ul class="navbar-nav">
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="signTemplate" href="#openSignModal" data-toggle="modal" data-backdrop="true" data-target="#openSignModal" data-type="patient-signature"><?php echo xlt('Signature'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="printTemplate" href="javascript:" onclick="printaDoc('templatecontent');"><?php echo xlt('Print'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="submitTemplate" href="#"><?php echo xlt('Download'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="chartTemplate" href="#"><?php echo xlt('Chart to') . ' ' . text($catname); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="downloadTemplate" href="#"><?php echo xlt('Download'); ?></a></li>
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="chartHistory" href="#"><?php echo xlt('Chart History'); ?></a></li>
                        </ul>
                    </div>
                    <?php if (!empty($is_module) || !empty($is_portal)) { ?>
                        <div class="dropdown mb-1">
                            <a class="dropdown-toggle nav-link btn btn-outline-success" href="#" role="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo xlt('Select Form') ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenu">
                                <?php echo $templateService->renderPortalTemplateMenu($pid, $cuser, true); ?>
                            </div>
                        </div>
                        <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="saveTemplate" href="#"><?php echo xlt('Save as Draft'); ?></a></li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary" id="sendTemplate" href="#"><?php echo xlt('Submit Completed'); ?></a>
                    </li>
                    <li class='nav-item mb-1'>
                        <a class='nav-link btn btn-outline-success' onclick="page.handleHistoryView()">
                            <?php echo xlt('Activities') ?>
                        </a>
                    </li>
                    <?php if (empty($is_module)) { ?>
                        <li class="nav-item mb-1">
                            <a id="Help" class="nav-link text-primary btn btn-outline-primary d-none" onclick='page.newDocument(cpid, cuser, "Help", help_id);'><?php echo xlt('Help'); ?></a>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-danger btn btn-outline-secondary" id="a_docReturn" href="#" onclick='window.location.replace("<?php echo $referer ?>")'><?php echo xlt('Return'); ?></a>
                        </li>
                    <?php } ?>
                </ul>
                <a class='btn btn-outline-primary btn-refresh mr-0 mb-1' data-toggle='tooltip' title='Refresh' id='refreshPage' href='javascript:' onclick='window.location.reload()'><?php echo xlt('Reload'); ?></a>
                <?php if ($GLOBALS['allow_portal_uploads'] ?? 1) { ?>
                    <a id="idShow" class="btn btn-outline-primary float-right  mr-0 mb-1" href='javascript:' onclick="$('#hideUpload').toggle();"><i class='fa fa-upload mr-1' aria-hidden='true'></i><?php echo xlt('Upload') ?></a>
                <?php } ?>
                <?php if (!empty($is_portal) && empty($auto_render)) { ?>
                    <a class="btn btn-outline-primary mb-1" id="a_docReturn" href="#" onclick='window.location.replace(<?php echo attr_js($referer_portal) ?>)'><?php echo xlt('Exit to Dashboard'); ?></a>
                <?php } elseif (!$is_module && !$is_dashboard) {
                    $referer_portal = "../home.php?site=" . (urlencode($_SESSION['site_id']) ?? null) ?: 'default';
                    ?>
                    <a class="btn btn-outline-primary mb-1" id="a_docReturn" href="#" onclick='window.location.replace(<?php echo attr_js($referer_portal) ?>)'><?php echo xlt('Exit'); ?></a>
                <?php } ?>
            </div>
        </nav>
        <div class="d-flex flex-row justify-content-center">
            <!-- Pending documents left menu Deprecated and removed 01/13/22 -->
            <div class="clearfix" id="topNav">
                <div id="collectionAlert"></div>
            </div>
            <!-- Right editor container -->
            <div id="editorContainer" class="d-flex flex-column w-100 h-auto">
                <!-- document editor and action toolbar template -->
                <script type="text/template" id="onsiteDocumentModelTemplate">
                    <div class="card m-0 p-0" id="docpanel">
                        <!-- Document edit container -->
                        <header class="card-header font-weight-bold bg-dark text-light p-1 helpHide" id='docPanelHeader'><?php echo xlt('Editing'); ?>
                            <button id="dismissOnsiteDocumentButtonTop" class="dismissOnsiteDocumentButton btn btn-outline-danger btn-sm float-right" onclick="window.location.reload()"><?php echo xlt('Dismiss Form'); ?></button>
                        </header>
                        <!-- File upload -->
                        <?php if ($GLOBALS['allow_portal_uploads'] ?? 1) { ?>
                        <div class="card col-12 col-lg-5 col-md-3">
                            <div id="hideUpload" class="card-body" style="display: none;">
                                <h4 class="card-title"><i class="fa fa-file-text mr-1" role="button" onclick="$('#hideUpload').toggle();"></i><?php echo xlt('Uploads') ?></h4>
                                <div class="row">
                                    <div class="container-fluid h-25" id="file-queue-container">
                                        <div id="file-queue">
                                            <form id="patientFileDrop" method="post" enctype="multipart/form-data" class="dropzone bg-dark" action='<?php echo $urlAjax; ?>'>
                                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                            </form>
                                            <button name="file_submit" id="idSubmit" class="btn btn-success mt-2 d-none" type="submit" value="upload"><?php echo xlt('Upload to Clinic') ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <!-- editor form -->
                        <form class="container-xl p-0" id='template' name='template' role="form" action="./../lib/doc_lib.php" method="POST">
                            <div id="templatediv" class="card-body border overflow-auto">
                                <div id="templatecontent" class="template-body bg-light">
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
                                <button id="dismissOnsiteDocumentButton" class="dismissOnsiteDocumentButton btn btn-sm btn-outline-danger float-right m-1" onclick="window.location.reload()"><?php echo xlt('Dismiss Form'); ?></button>
                            </span>
                            <span>
                            </span>
                            <!-- delete button is a separate form to prevent enter key from triggering a delete-->
                            <form id="deleteOnsiteDocumentButtonContainer" class="form-inline" onsubmit="return false;">
                                <fieldset>
                                    <div class="form-group">
                                        <label class="col-form-label"></label>
                                        <div class="controls">
                                            <button id="deleteOnsiteDocumentButton" class="btn btn-delete btn-sm btn-danger mt-1"><?php echo xlt('Delete Document'); ?></button>
                                            <span id="confirmDeleteOnsiteDocumentContainer">
                                                <button id="cancelDeleteOnsiteDocumentButton" class="btn btn-link btn-sm"><?php echo xlt('Cancel'); ?></button>
                                                <button id="confirmDeleteOnsiteDocumentButton" class="btn btn-sm btn-danger"><?php echo xlt('Confirm'); ?></button>
                                          </span>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
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
                <h4 class="text-sm-center"><?php echo xlt('Document and Forms Activity') ?><small><cite> (Current and Past Status.)</cite></small></h4>
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
                    <% items.each(function(item) { %>
                    <tr id="<%= _.escape(item.get('id')) %>">
                        <th scope="row"><%= _.escape(item.get('id') || '') %></th>
                        <td>
                            <button type="button" class='btn btn-sm btn-outline-success history-btn'><%= _.escape(item.get('docType') || '') %></button>
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
