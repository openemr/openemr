<?php
/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

$pid = $this->cpid;
$recid = $this->recid;
$docid = $this->docid;
$is_module = $this->is_module;
$is_portal = $this->is_portal;
$is_dashboard = (!$is_module && !$is_portal);
$category = $this->save_catid;
$new_filename = $this->new_filename;
$webroot = $GLOBALS['web_root'];
$encounter = '';
// for location assign
$referer = $GLOBALS['web_root'] . "/controller.php?document&upload&patient_id=" . attr_url($pid) . "&parent_id=" . attr_url($category) . "&";

if (!$is_module) {
    $this->assign('title', xlt("Patient Portal") . " | " . xlt("Patient Documents"));
} else {
    $this->assign('title', xlt("Patient Template") . " | " . xlt("Documents"));
}
$this->assign('nav', 'onsitedocuments');

$catname = '';
if ($category) {
    $result = sqlQuery("SELECT name FROM categories WHERE id = ?", array($category));
    $catname = $result['name'] ? $result['name'] : '';
}
$catname = $catname ? $catname : xlt("Onsite Portal Reviewed");

if (!$docid) {
    $docid = 'Hipaa_Document';
}

$isnew = false;
$ptName = isset($_SESSION['ptName']) ? $_SESSION['ptName'] : $pid;
$cuser = isset($_SESSION['sessionUser']) ? $_SESSION['sessionUser'] : $_SESSION['authUserID'];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php
    if ($is_dashboard) {
        echo xlt("Portal Document Review");
    } elseif (!$is_module) {
        echo xlt("Patient Portal Documents");
    } else {
        echo xlt("Patient Document Templates");
    }
    ?>
    </title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="description" content="Developed By sjpadgett@gmail.com">
    <?php
    // some necessary js globals
    echo "<script>var cpid=" . js_escape($pid) . ";var cuser=" . js_escape($cuser) . ";var ptName=" . js_escape($ptName) .
    ";var catid=" . js_escape($category) . ";var catname=" . js_escape($catname) . ";</script>";
    echo "<script>var recid=" . js_escape($recid) . ";var docid=" . js_escape($docid) . ";var isNewDoc=" . js_escape($isnew) . ";var newFilename=" . js_escape($new_filename) . ";</script>";
    echo "<script>var isPortal=" . js_escape($is_portal) . ";var isModule=" . js_escape($is_module) . ";var webRoot=" . js_escape($webroot) . ";</script>";
    // translations
    echo "<script>var alertMsg1='" . xlt("Saved to Patient Documents") . '->' . xlt("Category") . ": " . attr($catname) . "';</script>";
    echo "<script>var msgSuccess='" . xlt("Save Successful") . "';</script>";
    echo "<script>var msgDelete='" . xlt("Delete Successful") . "';</script>";
    Header::setupHeader(['no_main-theme', 'jquery-ui', 'jquery-ui-sunny', 'emodal']);
    ?>
<link href="<?php echo $GLOBALS['web_root']; ?>/portal/assets/css/style.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet" />
<link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet" type="text/css" />
<script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signature_pad.umd.js?v=<?php echo $GLOBALS['v_js_includes']; ?>" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer_api.js?v=<?php echo $GLOBALS['v_js_includes']; ?>" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/libs/LAB.min.js"></script>
<script type="text/javascript">
    $LAB.setGlobalDefaults({BasePath: "<?php $this->eprint($this->ROOT_URL); ?>"});
    $LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/underscore/underscore-min.js")
        .script("<?php echo $GLOBALS['assets_static_relative']; ?>/moment/moment.js")
        .script("<?php echo $GLOBALS['assets_static_relative']; ?>/backbone/backbone-min.js")
        .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app.js?v=<?php echo $GLOBALS['v_js_includes']; ?>")
        .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
        .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/view.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
</script>
</head>
<body class="skin-blue">
<script type="text/javascript">
    $LAB.script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsitedocuments.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
        .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsiteportalactivities.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait(
        function () {
            $(function () {
                page.init();
                pageAudit.init();
                console.log('init done template');
            });
            setTimeout(function () {
                if (!page.isInitialized) {
                    page.init();
                    if (!pageAudit.isInitialized)
                        pageAudit.init();
                }
            }, 1000);
        });

    function printaDoc(divName) {
        divName = 'templatediv';
        flattenDocument();
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;
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
        if ($(el).data('value') == 'Yes') {
            $(el).data('value', 'No');
            $(el).attr('data-value', 'No');
        }
        else {
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
    }

    function replaceCheckMarks() {
        $('.checkMark').each(function () {
            var ckid = $(this).data('id');
            var v = $('#' + ckid).data('value');
            if (v)
                $(this).replaceWith(v)
            else {
                $(this).replaceWith('No')
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
    }

    function restoreCheckMarks() {
        $('.checkMark').each(function () {
            var ckid = $(this).data('id');
            if ($('#' + ckid).data('value') == 'Yes')
                $('#' + ckid).prop('checked', true);
            else
                $('#' + ckid).prop('checked', false);
        });
    }

    function replaceSignatures() {
        $('.signature').each(function () {
            let type = $(this).data('type');
            if ($(this).attr('src') != signhere && $(this).attr('src')) {
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
        replaceSignatures()
    }

    function restoreDocumentEdits() {
        restoreCheckMarks();
        restoreRadioValues();
        restoreTextInputs();
    }
</script>
<style>
@media print {
    #templatecontent {
        width: 1220px
    }
}
hr {
    margin-top: 2px;
    margin-bottom: 2px;
    border: 0;
    border-top: 2px solid #eee;
}
.h4, .h5, .h6, h4, h5, h6 {
    margin-top: 0px;
    margin-bottom: 0px;
}
body {
    margin-top: 70px;
}
@media ( min-width : 979px) {
    #sidebar.affix-top {
        position: static;
        margin-top: 10px;
        width: 150px;
    }
    #sidebar.affix {
        position: fixed;
        top: 70px;
        width: 150px;
    }
}
.affix, affix-top {
    position: static;
}
.nopadding {
   padding: 0 !important;
   margin: 0 !important;
}
</style>
<script type="text/template" id="onsiteDocumentModelTemplate">
    <aside class="col-sm-2 col-xs-3" id="sidebar-pills">
        <ul class="nav nav-pills  nav-stacked" id="sidebar">
            <li data-toggle="pill" class="bg-info"><a id="signTemplate" href="#openSignModal"
                data-toggle="modal" data-backdrop="true" data-target="#openSignModal" data-type="patient-signature"><span><?php echo xlt('Signature');?></span></a></li>
            <li data-toggle="pill" class="bg-info"><a id="saveTemplate" href="#"><span"><?php echo xlt('Save');?></span></a></li>
            <li data-toggle="pill" class="bg-info"><a id="printTemplate" href="javascript:;" onclick="printaDoc('templatecontent');"><span"><?php echo xlt('Print');?></span></a></li>
            <li data-toggle="pill" class="bg-info"><a id="submitTemplate"  href="#"><span"><?php echo xlt('Download');?></span></a></li>
            <li data-toggle="pill" class="bg-info"><a id="sendTemplate"  href="#"><span"><?php echo xlt('Send for Review');?></span></a></li>
            <li data-toggle="pill" class="bg-info"><a id="chartTemplate"  href="#"><span"><?php echo xlt('Chart to Category') . ' ' . text($catname);?></span></a></li>
            <li data-toggle="pill" class="bg-info"><a id="downloadTemplate"  href="#"><span"><?php echo xlt('Download');?></span></a></li>
            <?php if (!$is_module) { ?>
                <li data-toggle="pill" class="bg-warning">
                    <a id="homeTemplate" href="#" onclick='window.location.replace("./../home.php")'><?php echo xlt('Return Home'); ?></a>
                </li>
            <?php } else { ?>
                <li data-toggle="pill" class="bg-warning">
                    <a id="homeTemplate" href="#" onclick='window.location.replace("<?php echo $referer ?>")'><?php echo xlt(' Return'); ?></a>
                </li>
            <?php } ?>
        </ul>
    </aside>
    <div class="col-md-8 col-sm-8 col-xs-8 nopadding">
        <span id="modelLoader" class="loader progress progress-striped active"><span class="bar"></span></span>
        <div class="panel panel-primary" id="docpanel">
            <header class="panel-heading" id='docPanelHeader'><?php echo xlt('Patient Document');?></header>
            <div id="loader" style="display:none;"></div>
            <form id='template' name='template' role="form" action="./../lib/doc_lib.php" method="POST" >
                <div id="loader" style="display:none;"></div>
                <div id="templatediv" class="panel-body" style="margin:0 auto; background:white">
                    <div id="templatecontent" class="template-body" style="margin:0 auto; background:white;padding:0 20px 0 20px"></div>
                </div>
                <input type="hidden" name="content" id="content" value="">
                <input type="hidden" name="cpid" id="cpid" value="">
                <input type="hidden" name="docid" id="docid" value="">
                <input type="hidden" name="handler" id="handler" value="download">
                <input type="hidden" name="status" id="status" value="Open">
             </form>
            <div class="panel-footer">
<!-- delete button is a separate form to prevent enter key from triggering a delete-->
<form id="deleteOnsiteDocumentButtonContainer" class="form-inline" onsubmit="return false;">
    <fieldset>
        <div class="form-group">
            <label class="control-label"></label>
            <div class="controls">
                <button id="deleteOnsiteDocumentButton" class="btn btn-mini btn-danger"><i class="icon-trash icon-white"></i><?php echo xlt('Delete Document');?></button>
                <span id="confirmDeleteOnsiteDocumentContainer">
                    <button id="cancelDeleteOnsiteDocumentButton" class="btn btn-mini"><?php echo xlt('Cancel');?></button>
                    <button id="confirmDeleteOnsiteDocumentButton" class="btn btn-mini btn-danger"><?php echo xlt('Confirm');?></button>
                </span>
            </div>
        </div>
    </fieldset>
</form>
</div>
</div>
</div>
</div>
</div>
</script>
<script type="text/template" id="onsiteDocumentCollectionTemplate">
        <nav class="nav navbar-fixed-top" id="topnav">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#"><i class="fa fa-file-text-o">&nbsp;</i><?php echo xla('Pending Documents') ?></a>
                </div>
                <ul class="nav navbar-nav" style='margin-top:5px;font-size:16px;font-weight:600'>
                    <?php require_once(dirname(__FILE__) . '/../../lib/template_menu.php'); ?>
                    <?php if (!$is_module) { ?>
                        <li class="bg-warning">
                            <a href="#" onclick='window.location.replace("./../home.php")'><?php echo xlt('Return Home'); ?></a>
                        </li>
                    <?php } else { ?>
                        <li class="bg-warning">
                            <a id="a_docReturn" href="#" onclick='window.location.replace("<?php echo $referer ?>")'><?php echo xlt('Return'); ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div id="collectionAlert"></div>
        </nav>
        <div class="container">
        <table class="collection table table-condensed table-hover">
        <thead>
            <tr class='bg-primary' style='cursor:pointer'>
                <th id="header_Id"><?php echo xlt('Doc Id');?><% if (page.orderBy == 'Id') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
                <th id="header_DocType"><?php echo xlt('Document');?><% if (page.orderBy == 'DocType') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
                <th id="header_CreateDate"><?php echo xlt('Create Date');?><% if (page.orderBy == 'CreateDate') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
                <th id="header_ReviewDate"><?php echo xlt('Reviewed Date');?><% if (page.orderBy == 'ReviewDate') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
                <th id="header_DenialReason"><?php echo xlt('Review Status');?><% if (page.orderBy == 'DenialReason') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
                <th id="header_PatientSignedStatus"><?php echo xlt('Patient Signed');?><% if (page.orderBy == 'PatientSignedStatus') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
                <th id="header_PatientSignedTime"><?php echo xlt('Patient Signed Date');?><% if (page.orderBy == 'PatientSignedTime') { %> <i class='icon-arrow-<%= page.orderDesc ? 'up' : 'down' %>' /><% } %></th>
            </tr>
        </thead>
        <tbody>
        <% items.each(function(item) {
            // if ((!isPortal && item.get('denialReason') == 'Locked')) return;
        %>
            <tr style='background:white' id="<%= _.escape(item.get('id')) %>">
                <td><%= _.escape(item.get('id') || '') %></td>
                <td><button class='btn btn-primary btn-sm'><%= _.escape(item.get('docType').slice(0, -4).replace(/_/g, ' ') || '') %></button></td>
                <td><%if (item.get('createDate')) { %><%= item.get('createDate') %><% } else { %>NULL<% } %></td>
                <td><%if (item.get('reviewDate')) { %><%= item.get('reviewDate') %><% } else { %>NULL<% } %></td>
                <td><%= _.escape(item.get('denialReason') || 'Pending') %></td>
                <td><%if (item.get('patientSignedStatus')=='1') { %><%= 'Yes' %><% } else { %>No<% } %></td>
                <td><%if (item.get('patientSignedTime')) { %><%= item.get('patientSignedTime') %><% } else { %>NULL<% } %></td>
            </tr>
        <% }); %>
        </tbody>
        </table>
        <%=  view.getPaginationHtml(page) %>
    </div>
</script>
    <!-- modal edit dialog -->
<div class="modal fade" id="onsiteDocumentDetailDialog" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><a class="close" data-dismiss="modal">×</a>
                <h3><i class="icon-edit"></i> <?php echo xlt('Edit Document');?>
                    <span id="modelLoader" class="loader progress progress-striped active"><span class="bar"></span></span>
                </h3>
            </div>
            <div class="modal-body">
                <div id="modelAlert"></div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                <button id="saveOnsiteDocumentButton" class="btn btn-primary"><?php echo xlt('Save Changes');?></button>
            </div>
        </div>
    </div>
</div>
    <!-- processed templates go here.-->
    <div id="onsiteDocumentModelContainer" class="modelContainer"></div>
    <div id="onsiteDocumentCollectionContainer" class="collectionContainer"></div>
<?php
    $this->display('_Footer.tpl.php');
?>
