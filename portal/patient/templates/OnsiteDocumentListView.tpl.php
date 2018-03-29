<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

    $this->assign('title', xlt("Patient Portal") . " | " . xlt("Patient Documents"));
    $this->assign('nav', 'onsitedocuments');

    $pid = $this->cpid;
    $recid = $this->recid;
    $docid = $this->docid;
    $encounter= '';

if (!$docid) {
     $docid = 'Hipaa_Document';
}

    $isnew = false;
    $ptName = isset($_SESSION['ptName']) ? $_SESSION['ptName'] : $pid;
    $cuser = isset($_SESSION ['sessionUser']) ? $_SESSION ['sessionUser'] : $_SESSION ['authUserID'];
    echo "<script>var cpid='" . attr($pid) . "';var cuser='" . attr($cuser) . "';var ptName='" . attr($ptName) . "';</script>";
    echo "<script>var recid='" . attr($recid) . "';var docid='" . attr($docid) . "';var webRoot='" . $GLOBALS['web_root'] . "';var isNewDoc='" . attr($isnew) . "';</script>";
    echo "<script>var alertMsg1='" . xlt("Saved to Documents->Onsite Portal->Reviewed - Open there to move or rename.") . "';</script>";
    echo "<script>var msgSuccess='" . xlt("Save Successful") . "';</script>";
    echo "<script>var msgDelete='" . xlt("Delete Successful") . "';</script>";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo xlt('OpenEMR Portal'); ?> | <?php echo xlt('Documents'); ?></title>
<meta	content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<meta name="description" content="Developed By sjpadgett@gmail.com">

        <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <?php if ($_SESSION['language_direction'] == 'rtl') { ?>
            <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />
        <?php } ?>

        <link href="<?php echo $GLOBALS['web_root']; ?>/portal/assets/css/style.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet" />
        <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.min.css" rel="stylesheet" />
        <link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signpad.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet">

        <script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/libs/LAB.min.js"></script>
        <script type="text/javascript">
            $LAB.setGlobalDefaults({BasePath: "<?php $this->eprint($this->ROOT_URL); ?>"});
            $LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js")
                .script("<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signpad.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
                .script("<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
                .script("<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js")
                .script("<?php echo $GLOBALS['assets_static_relative']; ?>/underscore-1-8-3/underscore-min.js")
                .script("<?php echo $GLOBALS['assets_static_relative']; ?>/moment-2-13-0/moment.js")
                .script("<?php echo $GLOBALS['assets_static_relative']; ?>/backbone-1-3-3/backbone-min.js")
                .script("<?php echo $GLOBALS['assets_static_relative']; ?>/emodal-1-2-65/dist/eModal.js")
                .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app.js?v=<?php echo $GLOBALS['v_js_includes']; ?>")
                .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
                .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/view.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()

        </script>
    </head>

<script type="text/javascript">
    $LAB.script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsitedocuments.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
        .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsiteportalactivities.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait(function(){
        $(document).ready(function(){
            page.init();
            pageAudit.init();
              $('#openSignModal').on('show.bs.modal', function(e) {
                    $('.sigPad').signaturePad({
                        drawOnly: true
                    });
               });
        });
        setTimeout(function(){ // second chance init ie of course....
            if (!page.isInitialized) page.init();
        },1000);
    });
    function printaDoc(divName){divName='templatediv'
        flattenDocument();
         var printContents = document.getElementById(divName).innerHTML;
         var originalContents = document.body.innerHTML;
         document.body.innerHTML = printContents;
         window.print();
         document.body.innerHTML = originalContents;
         $('.sigPad').signaturePad({
             drawOnly: true
           });
         location.reload();
    };
    function templateText(el){
        $(el).data('textvalue',$(el).val());
        $(el).attr("data-textvalue",$(el).val())
        return false;
    }
    function templateCheckMark(el){
        if( $(el).data('value') == 'Yes' ){
            $(el).data('value','No');
            $(el).attr('data-value','No');
        }
        else{
            $(el).data('value','Yes');
            $(el).attr('data-value','Yes');
        }
        return false;
    }
    function templateRadio(el){
        var rid = $(el).data('id')
        $('#rgrp'+rid).data( 'value', $(el).val() )
        $('#rgrp'+rid).attr( 'data-value', $(el).val() )
        $(el).prop('checked',true)
        return false;
    }
    function replaceTextInputs(){
        $('.templateInput').each( function(){
            var rv = $(this).data('textvalue');
            $(this).replaceWith(rv)
        });
     }
    function replaceRadioValues(){
        $('.ynuGroup').each( function(){
            var gid = $(this).data('id');
            var grpid = $(this).prop('id');
            var rv = $('input:radio[name="ynradio'+gid+'"]:checked').val();
            $(this).replaceWith(rv)
        });
    }
    function replaceCheckMarks(){
        $('.checkMark').each( function(){
            var ckid = $(this).data('id');
            var v = $('#'+ckid).data('value');
            if(v)
              $(this).replaceWith(v)
              else
                  $(this).replaceWith('No')
        });
    }
    function restoreTextInputs(){
        $('.templateInput').each( function(){
            var rv = $(this).data('textvalue');
            $(this).val(rv)
        });
     }
    function restoreRadioValues(){
        $('.ynuGroup').each( function(){
            var gid = $(this).data('id');
            var grpid = $(this).prop('id');
            var value = $(this).data('value');
            $("input[name=ynradio"+gid+"][value='"+value+"']").prop('checked', true);
        });
    }
    function restoreCheckMarks(){
        $('.checkMark').each( function(){
            var ckid = $(this).data('id');
            //var v = $('#'+ckid).data('value');
            if( $('#'+ckid).data('value') == 'Yes' )
                $('#'+ckid).prop('checked',true)
              else
                  $('#'+ckid).prop('checked',false)
        });
    }
    function flattenDocument(){
        replaceCheckMarks();
        replaceRadioValues();
        replaceTextInputs();

    }
    function restoreDocumentEdits(){
        restoreCheckMarks();
        restoreRadioValues();
        restoreTextInputs();
    }

</script>
<style>
 @media print {
    #templatecontent { width: 1220px }
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
} /* */
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
                    <li data-toggle="pill" class="bg-info"><a id="signTemplate"  href="#openSignModal"
                        data-toggle="modal" data-backdrop="true" data-target="#openSignModal"><span><?php echo xlt('Signature');?></span></a></li>
                    <li data-toggle="pill" class="bg-info"><a id="saveTemplate" href="#"><span"><?php echo xlt('Save');?></span></a></li>
                    <li data-toggle="pill" class="bg-info"><a id="printTemplate" href="javascript:;" onclick="printaDoc('templatecontent');"><span"><?php echo xlt('Print');?></span></a></li>
                    <li data-toggle="pill" class="bg-info"><a id="submitTemplate"  href="#"><span"><?php echo xlt('Download');?></span></a></li>
                    <li data-toggle="pill" class="bg-info"><a id="sendTemplate"  href="#"><span"><?php echo xlt('Send for Review');?></span></a></li>
                    <li data-toggle="pill" class="bg-info"><a id="chartTemplate"  href="#"><span"><?php echo xlt('Save to Chart');?></span></a></li>
                    <li data-toggle="pill" class="bg-info"><a id="downloadTemplate"  href="#"><span"><?php echo xlt('Download');?></span></a></li>
                    <li data-toggle="pill" class="bg-danger"><a id="homeTemplate" href="#"  onclick='window.location.replace("./../home.php")'><?php echo xlt('Return Home');?></a></li>
                </ul>
            </aside>
            <div class="col-md-8  col-sm-8 col-xs-8 nopadding">
                <span id="modelLoader" class="loader progress progress-striped active"><span class="bar"></span></span>
                <div class="panel panel-primary" id="docpanel">
                    <header class="panel-heading" id='docPanelHeader'><?php echo xlt('Patient Document');?></header>
                    <div id="loader" style="display:none;"></div>
                    <form id='template' name='template' role="form" action="./../lib/doc_lib.php" method="POST" >
                        <div id="loader" style="display:none;"></div>
                        <div id="templatediv" class="container panel-body" style="margin:0 auto; background:white">
                            <div id="templatecontent" class="template-body" style="margin:0 auto; background:white;padding:0 20px 0 20px"></div>
                        </div>
                        <input type="hidden" name="content" id="content" value="">
                        <input type="hidden" name="docid" id="docid" value="">
                        <input type="hidden" name="handler" id="handler" value="download">
                        <input type="hidden" name="status" id="status" value="Open">
                     </form>
                    <!-- <button type="button" id="submitTemplatepdf" class="btn btn-primary ">Pdf<i class="fa fa-arrow-circle-right fa-lg"></i></button>  -->
                    <div class="panel-footer">
        <!-- delete button is is a separate form to prevent enter key from triggering a delete-->
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
<!-- 	</div> -->
<script type="text/template" id="onsiteDocumentCollectionTemplate">
<body class="skin-blue">
    <div class="container">
        <div class="row">
        <div class="nav navbar-fixed-top" id="topnav">
            <!--<img class='pull-left' style='width:14%;height:auto;margin-right:10px;' class='logo' src='<?php echo $GLOBALS['images_static_relative']; ?>/logo-full-con.png'/>-->
            <ul class="nav nav-pills"  style='margin-top:5px'>
                <li class="page-header" style='margin-left:10px;'><h4><a href="javascript:location.reload(true);"><?php echo xla('Attention: Pending Documents') . '>'?></a></h4></li>
                <?php require_once(dirname(__FILE__) . '/../../lib/template_menu.php');?>
                <li class="bg-danger"><a href="#"    onclick='window.location.replace("./../home.php")'><?php echo xlt('Return Home');?></a></li>
            </ul>
            <div id="collectionAlert"></div>
        </div>
        <table class="collection table table-hover">
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
        <% items.each(function(item) { %>
            <tr  style='background:white' id="<%= _.escape(item.get('id')) %>">
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
</script>
<!--  Signature Modal -->
<div id="openSignModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="input-group">
                    <span class="input-group-addon"
                        onclick="getSignature(document.getElementById('patientSignaturemodal'))"><em><?php echo xlt('Show Current Signature On File');?><br>
                        <?php echo xlt('As will appear on documents.');?></em>
                    </span> <img class="signature form-control" type="patient-signature" id="patientSignaturemodal"
                        onclick="getSignature(this)" alt="<?php echo xla('Signature On File'); ?>" src="">
                    <!-- <span class="input-group-addon" onclick="clearSig(this)"><i class="glyphicon glyphicon-trash"></i></span> -->
                </div>
                <!-- <h4 class="modal-title">Sign</h4> -->
            </div>
            <div class="modal-body">
                <form name="signit" id="signit" class="sigPad">
                    <input type="hidden" name="name" id="name" class="name">
                    <ul class="sigNav">
                        <li style='display: none;'><input style="display: none"
                            type="checkbox" id="isAdmin" name="isAdmin" /><?php echo xlt('Is Authorizing Signature');?></li>
                        <li class="clearButton"><a href="#clear"><button><?php echo xlt('Clear Pad');?></button></a></li>
                    </ul>
                    <div class="sig sigWrapper">
                        <div class="typed"></div>
                        <canvas class="spad" id="drawpad" width="765" height="325"
                            style="border: 1px solid #000000; left: 0px;"></canvas>
                        <img id="loading"
                            style="display: none; position: absolute; TOP: 150px; LEFT: 315px; WIDTH: 100px; HEIGHT: 100px"
                            src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/loading.gif" /> <input type="hidden" id="output" name="output" class="output">
                    </div>
                    <input type="hidden" name="type" id="type" value="patient-signature">
                    <button type="button" onclick="signDoc(this)"><?php echo xlt('Acknowledge as my Electronic Signature');?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<img id="waitend"	style="display: none; position: absolute; top: 100px; left: 250px; width: 100px; height: 100px" src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/loading.gif" />
<!-- Modal -->
    <!-- modal edit dialog -->
<div class="modal fade" id="onsiteDocumentDetailDialog">
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

    <div id="onsiteDocumentModelContainer" class="modelContainer"></div>
    <div id="onsiteDocumentCollectionContainer" class="collectionContainer"></div>

</body>
</div> <!-- /container -->

<?php
    $this->display('_Footer.tpl.php');
?>
</html>
