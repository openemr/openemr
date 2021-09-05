<?php

/**
 * edih_view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin McCormick Longview, Texas
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 Kevin McCormick Longview, Texas
 * @copyright Copyright (c) 2018-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

//
if (!AclMain::aclCheckCore('acct', 'eob')) {
    die(xlt("Access Not Authorized"));
}

?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo xlt("EDI History"); ?></title>
    <?php Header::setupHeader(['datetime-picker', 'datatables', 'datatables-dt', 'datatables-bs', 'datatables-scroller']); ?>
    <?php if ($_SESSION['language_direction'] == "rtl") { ?>
      <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rtl_edi_history_v2.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } else { ?>
      <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/edi_history_v2.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } ?>
</head>
<!-- style for OpenEMR color -->
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-sm-12">
                <div class="clearfix">
                    <h2><?php echo xlt('EDI History'); ?></h2>
                </div>
            </div>
        </div>
        <div class="container-fluid mb-3">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-item nav-link font-weight-bold active" id="nav-newfiles-tab" data-toggle="tab" href="#nav-newfiles" role="tab" aria-controls="nav-newfiles" aria-selected="true">
                        <?php echo xlt("New Files"); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link font-weight-bold" id="nav-csvtables-tab" data-toggle="tab" href="#nav-csvtables" role="tab" aria-controls="nav-csvtables" aria-selected="false">
                        <?php echo xlt("CSV Tables"); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link font-weight-bold" id="nav-edifile-tab" data-toggle="tab" href="#nav-edifile" role="tab" aria-controls="nav-edifile" aria-selected="false">
                        <?php echo xlt("EDI File"); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link font-weight-bold" id="nav-notes-tab" data-toggle="tab" href="#nav-notes" role="tab" aria-controls="nav-notes" aria-selected="false">
                        <?php echo xlt("Notes"); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link font-weight-bold" id="nav-archive-tab" data-toggle="tab" href="#nav-archive" role="tab" aria-controls="nav-archive" aria-selected="false">
                        <?php echo xlt("Archive"); ?>
                    </a>
                </li>
            </ul>

            <div class="tab-content jumbotron py-4" id="nav-tabContent">
                <!-- New Files Section -->
                <div class="tab-pane fade show active" id="nav-newfiles" role="tabpanel" aria-labelledby="nav-newfiles-tab">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <form id="formupl" name="form_upl" action="edih_main.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("Select one or more files to upload"); ?></h4>
                                    <div class="custom-file">
                                        <label class="custom-file-label"><?php echo xlt("Choose file"); ?></label>
                                        <input type="file" class="custom-file-input" id="uplmulti" name="fileUplMulti[]" multiple />
                                        <input type="hidden" name="NewFiles" form="formupl" value="ProcessNew" />
                                        <div class="btn-group mt-3">
                                            <button type="submit" class="btn btn-primary btn-add" id="uplsubmit" name="upl_submit" form="formupl" value="<?php echo xla("Submit"); ?>">
                                                <?php echo xlt("Submit"); ?>
                                            </button>
                                            <input type="reset" class="btn btn-secondary" id="uplreset" name="upl_reset" form="formupl" value="<?php echo xla("Reset"); ?>" />
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <form id="processnew" name="process_new" action="edih_main.php" method="GET">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("Process new files for CSV records"); ?>:</h4>
                                    <input type="checkbox" id="processhtml" name="process_html" form="processnew"  value="htm" checked /> <?php echo xlt("HTML Output?"); ?>
                                    <input type="checkbox" id="processerr" name="process_err" form="processnew"  value="err" checked /> <?php echo xlt("Show Errors Only?"); ?> &nbsp;&nbsp;<br />
                                    <input type="hidden" name="ProcessFiles" form="processnew" value="ProcessNew" />
                                    <label for="process"><?php echo xlt("Process New Files"); ?></label>
                                    <input type="submit" class="btn btn-primary btn-sm" id="fuplprocess" name="process" form="processnew" value="<?php echo xla("Process"); ?>" />
                                </form>
                            </div>
                            <div class="col-sm-md-6 d-none" id="fileupl1">

                            </div>
                            <div class="col-sm-md-6 d-none" id="fileupl2">

                            </div>
                            <div class="col-12">
                                <div class="alert alert-primary mt-3 d-none" id="processed">
                                </div>
                                <div id="rsp" title="<?php echo xla("Response"); ?>"></div>
                                <div id="sub" title="<?php echo xla("Submitted"); ?>"></div>
                                <div id="seg" title="<?php echo xla("x12 Segments"); ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- CSV Tables Section -->
                <div class="tab-pane fade" id="nav-csvtables" role="tabpanel" aria-labelledby="nav-csvtables-tab">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12 col-md-8">
                                <form id="formcsvtables" name="form_csvtables" action="edih_main.php" method="GET">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("View CSV tables"); ?>:</h4>
                                    <table>
                                        <tr>
                                            <td colspan='4'><?php echo xlt("Choose a period or dates (YYYY-MM-DD)"); ?></td>
                                        </tr>
                                        <tr>
                                            <td class='text-center'><?php echo xlt("Choose CSV table"); ?>:</td>
                                            <td class='text-center'><?php echo xlt("From Period"); ?></td>
                                            <td class='text-center'><?php echo xlt("Start Date"); ?>: &nbsp;&nbsp; <?php echo xlt("End Date"); ?>:</td>
                                            <td class='text-center'><?php echo xlt("Submit"); ?></td>
                                        </tr>
                                        <tr height='1.5em'>
                                            <td class='text-center'>
                                                <select class="custom-select" id="csvselect" name="csvtables"></select>
                                            </td>
                                            <td class='text-center'>
                                                <select class="custom-select" id="csvperiod" name="csv_period">
                                                    <option value='2w' selected='selected'>2 <?php echo xlt('weeks'); ?></option>
                                                    <option value='1m'>1 <?php echo xlt('month'); ?></option>
                                                    <option value='2m'>2 <?php echo xlt('months'); ?></option>
                                                    <option value='3m'>3 <?php echo xlt('months'); ?></option>
                                                    <option value='6m'>6 <?php echo xlt('months'); ?></option>
                                                    <option value='9m'>9 <?php echo xlt('months'); ?></option>
                                                    <option value='1y'>1 <?php echo xlt('year'); ?></option>
                                                    <option value='ALL'><?php echo xlt('All Dates'); ?></option>
                                                </select>
                                            </td>
                                            <!-- datekeyup(e, defcc, withtime)  dateblur(e, defcc, withtime) -->
                                            <td class='text-left'>
                                                <input type='text' size='10' class='datepicker' name="csv_date_start" id="caldte1" value="" title="<?php echo xla('yyyy-mm-dd Start Date'); ?>" />
                                                <input type="text" size="10" class="datepicker" name="csv_date_end" id="caldte2" value="" title="<?php echo xla('yyyy-mm-dd End Date'); ?>" />
                                            </td>
                                            <td class='text-left'>
                                                <input type="hidden" name="csvShowTable" form="formcsvtables" value="gettable" />
                                                <button type="submit" class="btn btn-primary btn-add" id="csvshow" name="csv_show" form="formcsvtables" value="<?php echo xla("Submit"); ?>">
                                                    <?php echo xlt("Submit"); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <form id="formcsvhist" name="hist_csv" action="edih_main.php" method="get">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("Per Encounter"); ?></h4>
                                    <table>
                                        <tr>
                                            <td colspan='2'><?php echo xlt("Enter Encounter Number"); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo xlt("Encounter"); ?></td>
                                            <td><?php echo xlt("Submit"); ?></td>
                                        </tr>
                                        <tr>
                                            <td><input id="histenctr" type="text" class="form-control" size=10 name="hist_enctr" value="" /></td>
                                            <td>
                                                <button type="submit" class="btn btn-primary btn-add" id="histsbmt" name="hist_sbmt" form="formcsvhist" value="<?php echo xla("Submit"); ?>">
                                                    <?php echo xlt("Submit"); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                            <div class="col-12">
                                <div class='alert alert-primary mt-3 d-none' id='tblshow'>
                                </div>
                                <div id='tbcsvhist'></div>
                                <div id='tbrpt'></div>
                                <div id='tbrsp'></div>
                                <div id='tbsub'></div>
                                <div id='tbseg'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- EDI File Section -->
                <div class="tab-pane fade" id="nav-edifile" role="tabpanel" aria-labelledby="nav-edifile-tab">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <form id="x12view" name="x12_view" action="edih_main.php" enctype="multipart/form-data" method="post">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("View EDI x12 file"); ?>:</h4>
                                    <table>
                                        <tr>
                                            <td class='text-left'><label for="x12htm"><?php echo xlt("Report?"); ?></label></td>
                                            <td class='text-center'><label for="x12file"><?php echo xlt("Choose File"); ?>:</label></td>
                                            <td class='text-left'><label for="x12_filebtn"><?php echo xlt("Submit"); ?>:</label></td>
                                            <td class='text-center'><label for="x12_filereset"><?php echo xlt("Reset"); ?>:</label></td>
                                        </tr>
                                        <tr>
                                            <td class='text-left'>
                                                <input type="hidden" name="viewx12Files" value="view_x12" />
                                                <input type="checkbox" id="x12htm" name="x12_html" value="html" />
                                            </td>
                                            <td class='text-left'>
                                                <div class="custom-file">
                                                    <label class="custom-file-label"><?php echo xlt("Choose file"); ?></label>
                                                    <input id="x12file" type="file" class="custom-file-input" size=30 name="fileUplx12" />
                                                </div>
                                            </td>
                                            <td class='text-center'>
                                                <button type="submit" class="btn btn-primary btn-add" id="x12filebtn" name="x12_filebtn" form="x12view" value="<?php echo xla("Submit"); ?>">
                                                    <?php echo xlt("Submit"); ?>
                                                </button>
                                            </td>
                                            <td class='text-center'>
                                                <button type="button" class="btn btn-secondary btn-cancel" id="x12filerst" name="x12_filereset" form="x12view" value="<?php echo xla("Reset"); ?>">
                                                    <?php echo xlt("Reset"); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                                <div id="x12rsp"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Notes Section -->
                <div class="tab-pane fade" id="nav-notes" role="tabpanel" aria-labelledby="nav-notes-tab">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <a class="text-decoration-none" href="<?php echo $web_root; ?>/Documentation/Readme_edihistory.html" rel="noopener" target="_blank"><?php echo xlt("View the README file"); ?></a>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <form id="formlog" name="form_log" action="edih_main.php" enctype="multipart/form-data" method="post">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("Inspect the log"); ?></h4>
                                    <label for="logfile"><?php echo xlt("View Log"); ?></label>
                                    <select class="custom-select" id="logselect" name="log_select"></select>
                                    <input type="hidden" name="logshowfile" value="getlog">
                                    <div class="btn-group mt-3">
                                        <button type="submit" class="btn btn-primary btn-add" id="logshow" form="formlog" value="<?php echo xla("Submit"); ?>">
                                            <?php echo xlt("Submit"); ?>
                                        </button>
                                        <button type="button" class="btn btn-primary" id="logarch" form="formlog" value="<?php echo xla("Archive"); ?>">
                                            <?php echo xlt("Archive"); ?>
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-cancel" id="logclose" form="formlog" value="<?php echo xla("Close"); ?>">
                                            <?php echo xlt("Close"); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <form id="formnotes" name="form_notes" action="edih_main.php" enctype="multipart/form-data" method="post">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("Notes"); ?></h4>
                                    <label for="notesget"><?php echo xlt("Notes"); ?></label>
                                    <div class="btn-group ml-2">
                                        <button type="button" class="btn btn-primary" id="notesget" name="notes_get" form="formnotes" value="<?php echo xla("Open"); ?>">
                                            <?php echo xlt("Open"); ?>
                                        </button>
                                        <input id="noteshidden" type="hidden" name="notes_hidden" value="putnotes" />
                                        <button type="submit" class="btn btn-primary btn-save" id="notessave" name="notes_save" form="formnotes" value="<?php echo xla("Save"); ?>">
                                            <?php echo xlt("Save"); ?>
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-cancel" id="notesclose" name="notes_close" form="formnotes" value="<?php echo xla("Close"); ?>">
                                            <?php echo xlt("Close"); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-12">
                                <div id='logrsp'></div>
                                <div id='notesrsp'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Archive Section -->
                <div class="tab-pane fade" id="nav-archive" role="tabpanel" aria-labelledby="nav-archive-tab">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <span><?php echo xlt("Selected files and data will be removed from folders and tables"); ?></span>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <form id="formarchive" name="form_archive" action="edih_main.php" enctype="multipart/form-data" method="POST">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("Archive old files"); ?></h4>
                                    <label for="archive_sel"><?php echo xlt("Older than"); ?>:</label>
                                    <select class="custom-select" id="archiveselect" name="archive_sel">
                                        <option value="" selected="selected"><?php echo xlt('Choose'); ?></option>
                                        <option value="24m">24 <?php echo xlt('months'); ?></option>
                                        <option value="18m">18 <?php echo xlt('months'); ?></option>
                                        <option value="12m">12 <?php echo xlt('months'); ?></option>
                                        <option value="9m">9 <?php echo xlt('months'); ?></option>
                                        <option value="6m">6 <?php echo xlt('months'); ?></option>
                                        <option value="3m">3 <?php echo xlt('months'); ?></option>
                                    </select>
                                    <label for="archivereport"><?php echo xlt("Report"); ?>:</label>
                                    <input type="button" class="btn btn-sm btn-secondary" id="archiverpt" name="archivereport" form="formarchive" value="<?php echo xla("Report"); ?>" />
                                    <input type="hidden" name="ArchiveRequest" form="formarchive" value="requested" />
                                    <label for="archivesbmt"><?php echo xlt("Archive"); ?>:</label>
                                    <input type="submit" class="btn btn-sm btn-secondary" id="archivesbmt" name="archive_sbmt" form="formarchive" value="<?php echo xla("Archive"); ?>" />
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <form id="formarchrestore" name="form_archrestore" action="edih_main.php" enctype="multipart/form-data" method="POST">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <h4><?php echo xlt("Restore Archive"); ?></h4>
                                    <label for="archrestore_sel"><?php echo xlt("Restore"); ?>:</label>
                                    <select class="custom-select" id="archrestoresel" name="archrestore_sel"> </select>
                                    <input type="hidden" name="ArchiveRestore" form="formarchrestore" value="restore" />
                                    <label for="arch_restore"><?php echo xlt("Restore"); ?>:</label>
                                    <input type="submit" class="btn btn-sm btn-secondary" id="archrestore" name="arch_restore" form="formarchrestore" value="<?php echo xla("Restore"); ?>" />
                                </form>
                            </div>
                            <div class="col-12">
                                <div id="archiversp"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- end DataTables js Begin local js -->
<script>
    $(function () {
        // set some button disabled
        $('#processfiles').prop('disabled', true);
        $('#archivesubmit').prop('disabled', true);
        // update list of available csv tables
        $(function () { csvlist() });
        // update list of available log files
        $(function () { loglist() });
        // update list of archive files
        $(function () { archlist() });
        // hide these div elements until used
        $("#fileupl1").toggle(false);
        $("#fileupl2").toggle(false);

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
/* ************
 *   end of document ready() $
 * ************
 */
/* ****  from http://scratch99.com/web-development/javascript/convert-bytes-to-mb-kb/ *** */
    function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes == 0) return 'n/a';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        if (i == 0) return bytes + ' ' + sizes[i];
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
    };
/* *** variables for upload maximums *** */
/* *** phpserver: 'maxfsize''maxfuploads''postmaxsize''tmpdir'  phpserver['postmaxsize'] *** */
    var phpserver = [];
    $(function () {
        $.ajax({
            url: 'edih_main.php',
            data: {
                srvinfo: 'yes',
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            dataType: 'json',
            success: function(rsp){ phpserver = rsp }
        });
    });

    /**
     * update the list of available csv tables
     */
    function csvlist() {
        $.ajax({
            type: 'GET',
            url: 'edih_main.php',
            data: {
                csvtbllist: 'yes',
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            dataType: 'json',
            success: function(data) {
              var options = $('#csvselect').attr('options');
              var optct = $.isPlainObject(data);  // data.length
              if (optct) {
                var options = [];
                options.push("<option value='' selected='selected'><?php echo xla("Choose from list"); ?></option>");
                $.each(data.claims, function(idx, value) {
                    options.push("<option value=" + value.fname + ">" + value.desc + "</option>");
                });
                $.each(data.files, function(idx, value) {
                    options.push("<option value=" + value.fname + ">" + value.desc + "</option>");
                });
                $("#csvselect").html(options.join(''));
              }
            }
        });
    };
/* *** update the list of log files *** */
    function loglist() {
        $.ajax({
            type: 'GET',
            url: 'edih_main.php',
            data: {
                loglist: 'yes',
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            dataType: 'json',
            success: function(data) {
              var options = $('#logselect').attr('options');
              var optct = data.length;
              if (optct) {
                var options = [];
                options.push('<option selected="selected"><?php echo xla("Choose from list"); ?></option>');
                for (var i=0; i<optct; i++) {
                  options.push('<option value=' + data[i] + '>' + data[i] + '</option>');
                }
                $("#logselect").html(options.join(''));
              }
            }
        });
    };
/* *** update the list of archive files *** id="archrestoresel name="archrestore_sel" */
    function archlist() {
        $.ajax({
            type: 'GET',
            url: 'edih_main.php',
            data: {
                archlist: 'yes',
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            dataType: 'json',
            success: function(data) {
                //var options = $('#archrestoresel').attr('options');
                $('#archrestoresel').empty();
                var optct = data.length;
                var options = [];
                if (optct) {
                    options.push("<option selected='selected'><?php echo xla("Choose from list"); ?></option>");
                    for (var i=0; i<optct; i++) {
                        options.push("<option value=" + data[i] + ">" + data[i] + "</option>");
                    }
                } else {
                    options.push("<option selected='selected'><?php echo xla("No Archives"); ?></option>");
                }
                $('#archrestoresel').html(options.join(""));
            }
        });
    };

    $('#tbcsvhist').on('click', 'a', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('<div/>', {'class':'edihDlg', 'id':'link-'+($(this).index()+1)})
            .load($(this).attr('href')).appendTo('#tbcsvhist');
/* #csvTable  ****  */
    $('#tblshow').on('click', 'a', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('<div/>', {'class':'edihDlg', 'id':'link-'+($(this).index()+1)})
            .load($(this).attr('href')).appendTo('#tblshow');
    });

/* **** links in dialog in uploads - processed div  ****    */
    $('#processed').on('click', 'a', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('<div/>', {'class':'edihDlg', 'id':'link-'+($(this).index()+1)})
            .load($(this).attr('href')).appendTo('#processed');
    });
/* ****************************
 *
 * === upload multiple files
 *     buttons are enabled/disabled
 *     selected and uploaded files are listed
 *     the process files script html output displayed,
 */
/* **** if files have been uploaded **** */
    var upld_ct = 0;
/* ***** list files selected in the multifile upload input **** */
    $('#uplmulti').on('change', function(){
        // clear uploaded files list, since new selected files list is coming
        $('#fileupl2').html('');
        $('#fileupl2').addClass('d-none');
        $('#fileupl2').removeClass('flist');
        $('#processed').html('');
        $('#processed').addClass('d-none');
        var uplfiles = this.files; //event.target.files;
        var fct = uplfiles.length;
        var fsize = 0;
        var fl1 = $('#fileupl1');
        fl1.html('');
        fl1.toggle(true);
        fl1.addClass('flist1');
        fl1.removeClass('d-none')
        var fmaxupl = phpserver['maxfuploads'];   // $("#srvvals").data('mf');
        var pmaxsize = phpserver['postmaxsize']
        var str = "<p><em><?php echo xla('Selected Files'); ?>:</em></p>";
        str = str + "<ul id='uplsel' class='fupl'>";
        for(var i = 0; i < fct; i++) {
            if (i == fmaxupl) str = str + '</ul><p><?php echo xla('max file count reached'); ?><br /> - <?php echo xla('reload names below'); ?> </p><ul class=fupl>';
            str = str + "<li>" + uplfiles[i].name + "</li>";  //' ' +
            fsize += uplfiles[i].size;
        };
        str = str + '</ul><p><?php echo xla('Total size'); ?>: ' + bytesToSize(fsize) + ' (<?php echo xla('max'); ?> ' + pmaxsize + ')</p>';
        $('#uplsubmit').prop('disabled', false);
        if (upld_ct === 0 ) {
            $('#processupl').prop('disabled', true);
        }
        fl1.html(str);
    });
    // uplreset button click the file input is reset and associated values cleared
    $('#uplreset').on('click', function( event ) {
        event.preventDefault();
        event.stopPropagation();
        $('#fileupl1').html('');
        $('#fileupl2').html('');
        $('#fileupl1').hide();
        $('#fileupl2').hide();
        $('#processed').html('');
        $('#processed').addClass('d-none');
        $('#uplsubmit').prop('disabled', true);
        if (upld_ct == 0 ) {
            $('#fuplprocess').prop('disabled', true);
        } else {
            $('#fuplprocess').prop('disabled', false);
        }
        // $('#fupl').reset();
        document.getElementById('formupl').reset();
        return false;
    });

/* ***** uplsubmit button click --upload files are scanned and copied into folders  *** */
/* ***** files are listed next to file selected list by css  *** */
    $('#formupl').on('submit', function( event )  {
        event.stopPropagation();
        event.preventDefault();
        var uplForm = document.getElementById("formupl");
        var upldata = new FormData( document.getElementById('formupl') );
        var rspElem = $('#fileupl2');
        rspElem.html('');
        rspElem.addClass('d-none');
        $.ajax({
                url: $('#formupl').attr('action'),
                type: 'POST',
                cache: false,
                data: upldata,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(data) {
                    rspElem.html(data);
                    rspElem.toggle();
                    rspElem.removeClass('d-none');
                    $('#fuplprocess').prop('disabled', false );
                    $('#fuplupload').prop('disabled', true);
                    uplForm.reset();
                    upld_ct++;
                },
                error: function( xhr, status ) { alert( <?php echo xlj('Sorry, there was a problem!'); ?> ); },
            });
        return false;
    });
/* **** process button, files parsed and csv rows displayed  *** */
    $('#processnew').on('submit', function(e) {
        e.stopPropagation();
        e.preventDefault();
        $.ajax({
                url: $('#processnew').attr('action'),
                type: 'GET',
                data: $('#processnew').serialize(),  //prcForm.serialize(),
                success: [
                    function(data) {
                        $('#fileupl1').html('');
                        $('#fileupl1').addClass('d-none');
                        $('#fileupl2').html('');
                        $('#fileupl2').addClass('d-none');
                        $('#processed').html(data);
                        $('#processed').removeClass('d-none');
                    }
                ],
                error: function( xhr, status ) {
                    alert( <?php echo xlj('Sorry, there was a problem!'); ?> ),
                    $('#processed').html(status);
                    $('#processed').removeClass('d-none');
                }
            });
        upld_ct = 0;
        /* ***  update list of csv tables *** */
        csvlist();
        $('#fuplprocess').prop('disabled', true );
        return false;
    });

/* *********************************************
 *
 *  ==== file upload lists  match uploaded to selected
 *       when mouse is over element in one list, matching element
 *       in other list is highlighted also
 */
    function outlineMatch(matchElem, matchText) {
        if (matchText == 'none') {
            matchElem.css('font-weight', 'normal');
            return false;
        } else {
            matchElem.each(function( index ) {
                if ( matchText == $(this).text() ) {
                    $(this).siblings().css('font-weight', 'normal');
                    $(this).css('font-weight', 'bolder');
                    return false;
                };
            });
        }
       return false;
    }

/* *** do not use .hover event   */
    $('#fileupl2').on('mouseenter', 'li', function(event){
        var fl1 = $('#fileupl1').find('li');
        var fname = $(this).text();
        $(this).css('font-weight', 'bolder');
        $(this).siblings().css('font-weight', 'normal');
        outlineMatch(fl1, fname);
    });
    $('#fileupl2').on('mouseleave', 'li', function(){
        var fl1 = $('#fileupl1').find('li');
        $(this).css('font-weight', 'normal');
        outlineMatch(fl1, 'none');
    });
    $('#fileupl1').on('mouseenter', 'li', function(event){
        $(this).css('font-weight', 'bolder');
        if ( $('#fileupl2').length ) {
            var fl2 = $('#fileupl2').find('li');
            var fname = $(this).text();
            outlineMatch(fl2, fname);
        }
    });
    $('#fileupl1').on('mouseleave', 'li', function(){
        $(this).css('font-weight', 'normal');
        if ( $('#fileupl2').length ) {
            var fl2 = $('#fileupl2').find('li');
            var fname = $(this).text();
            outlineMatch(fl2, 'none');
        }
    });

/* *****  ==== end file upload lists  match uploaded to selected
/* ****************************
 * ===  end upload multiple files section
 */

/* ****************
 * begin csv tables section
 * the csv tables are displayed using jquery dataTables plugin
 * here, the 'success' action is to execute an array of functions
 * the helper function bindlinks() applies jquery .on method
 * so most links will open a jquery-ui dialog
 */
    $('#formcsvtables').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // verify a csv file is selected
        if ($('#csvselect').val() == '') {
            $("#tblshow").html('<?php echo xla("No table selected! Select a table."); ?>');
            $('#tblshow').removeClass('d-none');
            return false;
        }
        $.ajax({
            type:'get',
            url: "edih_main.php",
            data: $('#formcsvtables').serialize(),
            dataType: "html",
            success: [
                function(data){
                    $('#tblshow').html(data);
                    $('#tblshow').css('maxWidth', 'fit-contents');
                    $('#tblshow').removeClass('d-none');
                    $('#tblshow table#csvTable').DataTable({
                        'processing': true,
                        'scrollY': '300px',
                        'scrollCollapse': true,
                        'scrollX': true,
                        'paging': true,
                        <?php // Bring in the translations ?>
                        <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
                    });
                },
            ]
        });
    });

    // csv encounter history
    $('#formcsvhist').on('submit', function(e) {
        e.preventDefault();
        $('#tbcsvhist').empty();
        var chenctr = $('#histenctr').value;
        $.ajax({
            type: "GET",
            url: $('#formcsvhist').attr('action'),
            data: $('#formcsvhist').serialize(), //{ csvenctr: chenctr },
            dataType: "html",
            success: [ function(data){
                $('<div/>', {'class':'edihDlg', 'id':'link-'+($(this).index()+1)})
                    .appendTo('#tbcsvhist').html($.trim(data));
                }
            ]
        });
    });
    //
    $('#csvClear').on('click', function(e) {
        e.preventDefault();
        $("#tblshow").html('');
        $('#tblshow').addClass('d-none');
    });
/* **************
 * === end of csv tables and claim history
 */
/* ****************8
 * === view x12 file form  form"view_x12" file"x12file" submit"fx12" check"ifhtml" newWin"x12nwin"
 */
    $('#x12view').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        //
        var rspElem = $('#x12rsp');
        var frmData = new FormData( document.getElementById('x12view') );
        $.ajax({
            url: $('#x12view').attr('action'),
            type: 'POST',
            data: frmData,
            processData: false,
            contentType: false,
            //
            success: function(data) {
                rspElem.html('');
                rspElem.html(data);
                $('#x12filesbmt').prop('disabled', true);
            },
            error: function( xhr, status ) { alert( <?php echo xlj('Sorry, there was a problem!'); ?> ); }
        });
    });
    //
    $('#x12file').on('change', function(){
        // clear file display
        $('#x12rsp').html('');
        $('#x12filesbmt').prop('disabled', false);
    });
    //
    $('#x12filerst').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        // clear file display
        $('#x12rsp').html('');
        $('#x12filesbmt').prop('disabled', true);
        $('#x12view').trigger('reset');
    });

/*
 * === functions for logs, notes, and archive "frm_archive" "archiveselect""archivesubmit"
 */
    $('#logarch').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        //
        $.ajax({
            type: 'get',
            url: $('#formlog').attr('action'),
            data: {
                archivelog: 'yes',
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            dataType: "json",
            success: function(data) {
                var str = "<p><?php echo xla('Archive Log Files'); ?></p><ul id='logarchlist'>";
                var fct = data.length;
                if (fct == 0) {
                    str = str + "<li><?php echo xla('No logs older than 7 days'); ?></li>";
                } else {
                    for(var i = 0; i < fct; i++) {
                        str = str + "<li>" + data[i] + "</li>";
                    }
                };
                str = str + "</ul>";
                $('#notesrsp').hide();
                $('#logrsp').html('');
                $('#logrsp').html(str);
                $('#logrsp').show();
            },
            error: function( xhr, status ) { alert( <?php echo xlj('Sorry, there was a problem!'); ?> ); }
        });
        loglist();

    });

    $('#logclose').on('click', function(e) {
        e.preventDefault();
        $('#logrsp').html('');
        $('#logrsp').hide();
        $('#notesrsp').show();
    });

    $('#logselect').on('change', function(e) {
        $('#logshow').prop('disabled', false );
    });

    $('#logshow').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var fn = $('#logselect').val();
        $.ajax({
            type: 'get',
            url: $('#formlog').attr('action'),
            //data: { archivelog: 'yes', logfile: fn },
            data: $('#formlog').serialize(),
            dataType: "html",
            success: function(data){
                $('#notesrsp').hide();
                $('#logrsp').html(''),
                $('#logrsp').html($.trim(data));
                $('#logrsp').show();
            }
        });
    });

    $('#notesget').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $.ajax({
            type:'GET',
            url: $('#formnotes').attr('action'),
            data: {
                getnotes: "yes",
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            dataType: "text",
            success: function(data){
                $('#notesrsp').html('');
                $('#notesrsp').html("<H4>Notes:</H4>");
                $('#notesrsp').append("<textarea class='form-control' id='txtnotes', name='txtnotes',form='formnotes',rows='10',cols='600',wrap='hard' autofocus='autofocus'></textarea>");
                // necessary to trim the data since php from script has leading newlines (UTF-8 issue) '|:|'
                $('#logrsp').hide();
                $('#notesrsp \\:textarea').val($.trim(data));
                $('#notesrsp').show();
            }
        });
    });

    $('#notessave').on('click', function(e) {
        e.preventDefault();
        var notetxt = $('#txtnotes').val();
        var noteURL = $('#formnotes').attr('action');
        $.post(noteURL, { putnotes: 'yes', tnotes: notetxt, csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?> },
            function(data){ $('#notesrsp').append(data); });
    });

    $('#notesclose').on('click', function(e) {
        e.preventDefault();
        $('#notesrsp').html('');
        $('#notesrsp').toggle(false);
    });

/*
 * ==== Archive form id="formarchive"
 *
 */
    $('#formarchive').on('submit', function(e) {
        //e.stopPropagation();
        e.preventDefault();
        var archForm = document.getElementById('formarchive');
        var archdata = new FormData(archForm);
        var rspElem = $('#archiversp');
        rspElem.html('');
        $.ajax({
            url: $('#formarchive').attr('action'),
            type: 'POST',
            cache: false,
            data: archdata,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(data) {
                rspElem.html(data);
                $('#archivesubmit').prop('disabled', true );
                archForm.reset();

            },
            error: function( xhr, status ) { alert( <?php echo xlj('Sorry, there was a problem!'); ?> ); },
            // code to run regardless of success or failure
            // complete: function( xhr, status ) { alert( "The request is complete!" ); }
        });
        archlist();
        csvlist();
        return false;
    });
    //
    $('#archiverpt').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        // id="#archiversp"
        var rspElem = $('#archiversp');
        rspElem.html('');
        var sprd = $('#archiveselect').val();
        var surl = $('#formarchive').attr('action');
        //
        //console.log(surl);
        $.ajax({
            url: 'edih_main.php',
            type: 'GET',
            //cache: false,
            dataType: 'html',
            data: { archivereport: 'yes', period: sprd, csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?> },

            success: function(data) {
                //rspElem.html(data);
                //rspElem.show();
                $('#archiversp').html(data);
            },
            error: function( xhr, status ) {
                alert( <?php echo xlj('Sorry, there was a problem!'); ?> );
                rspElem.html(status);
                rspElem.show();
            }
        });
        return false;
    });
    //
    $('#archiveselect').on('change', function(e) {
        $('#archivesubmit').prop('disabled', false );
    });

    //
    $('#formarchrestore').on('submit', function(e) {
        //e.stopPropagation();
        e.preventDefault();

        var sel = $( "#archrestoresel option:selected" ).text();
        console.log( sel );
        if (sel == "No Archives") {
            alert(<?php echo xlj('No archive files present'); ?>);
            return false;
        }
        var archrstForm = document.getElementById('formarchrestore');
        var archrstdata = new FormData(archrstForm);
        var rspElem = $('#archiversp');
        //var archf = $('#archrestoresel').val();
        //archrstdata = { archrestore: 'yes', archfile: archf };
        $.ajax({
            url: $('#formarchrestore').attr('action'),
            type: 'POST',
            data: archrstdata,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(data) {
                rspElem.html('');
                rspElem.html(data);
            },
            error: function( xhr, status ) { alert( <?php echo xlj('Sorry, there was a problem!'); ?> ); },
        });
        archlist();
        csvlist();
        archrstForm.reset();
        return false;
    });

/* ************
 * end of javascript block
 */
</script>

</body>

</html>
