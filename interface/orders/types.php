<?php
/**
 * types.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2010-2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/acl.inc");

use OpenEMR\Core\Header;

// This script can be run either inside the OpenEMR frameset for order catalog
// maintenance, or as a popup window for selecting an item to order. In the
// popup case the GET variables 'popup' (a boolean) and 'order' (an optional
// item ID to select) will be provided, and maintenance may also be permitted.

$popup = empty($_GET['popup']) ? 0 : 1;
$order = isset($_GET['order']) ? $_GET['order'] + 0 : 0;
$labid = isset($_GET['labid']) ? $_GET['labid'] + 0 : 0;

// If Save was clicked, set the result, close the window and exit.
//
if ($popup && $_POST['form_save']) {
    $form_order = isset($_GET['form_order']) ? $_GET['form_order'] + 0 : 0;
    $ptrow = sqlQuery("SELECT name FROM procedure_type WHERE procedure_type_id = ?", [$form_order]);
    $name = $ptrow['name'];
    ?>
    <script type="text/javascript"
        src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js">
    </script>
    <script language="JavaScript">
    if (opener.closed || ! opener.set_proc_type) {
        alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
    } else {
        opener.set_proc_type(<?php echo js_escape($form_order) . ", " . js_escape($name); ?>);
        <?php
            // This is to generate the "Questions at Order Entry" for the Procedure Order form.
            // GET parms needed for this are: formid, formseq.
        if (isset($_GET['formid'])) {
            require_once("qoe.inc.php");
            $qoe_init_javascript = '';
            echo ' opener.set_proc_html("';
            echo generate_qoe_html($form_order, intval($_GET['formid']), 0, intval($_GET['formseq']));
            echo '", "' . $qoe_init_javascript . '");' . "\n";
        }
        ?>
    }
    window.close(); // comment out for debugging
    </script>
    <?php
    exit();
}
// end Save logic

?>
<!DOCTYPE html>
<html>

<head>
    <?php Header::setupHeader(['opener', 'datetime-picker', 'jquery-ui', 'jquery-ui-base']);?>

    <title><?php echo xlt('Configure Orders and Results'); ?></title>

    <style type="text/css">
        #con0 table {
            margin: 0;
            padding: 0;
            width: 100%;
        }
        #con0 td {
            /*padding: 0;*/
            font-family: sans-serif;
            font-size: 11px;
            line-height: 25px;
        }
        .plusminus {
            font-family: monospace;
            /*font-size: 11px;*/
        }
        .haskids {
            color: #0000dd;
            cursor: pointer;
            cursor: hand;
        }
        tr.head {
            font-size: 14px;
            background-color: #cccccc;
            font-weight: bold;
        }
        tr.evenrow {
            background-color: #ddddff;
        }
        tr.oddrow {
            background-color: #ffffff;
        }
        tr.outertr {
            padding: 0px 0px 0px 10px;
        }
        td {
            line-height: 25px;
        }
        .col1 {
            width: 33%
        }
        .col2 {
            width: 12%
        }
        .col3 {
            width: 8%
        }
        .col4 {
            width: 28%
        }
        .col5 {
            width: 5%
        }
        .col6 {
            width: 8%
        }

        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           }
        }

    </style>


    <?php
    if ($popup) { ?>
        <script type="text/javascript" src="../../library/topdialog.js"></script>
    <?php } ?>


    <script language="JavaScript">

    <?php
    if ($popup) {
        require($GLOBALS['srcdir'] . "/restoreSession.php");
    }
    ?>

    <?php
    // Create array of IDs to pre-select, leaf to top.
    echo "preopen = [";
    echo $order > 0 ? $order : 0;
    for ($parentid = $order; $parentid > 0;) {
        $row = sqlQuery("SELECT parent FROM procedure_type WHERE procedure_type_id = ?", [$parentid]);
        $parentid = $row['parent'] + 0;
        echo ", $parentid";
    }

    echo "];\n";
    ?>


    // initiate by loading the top-level nodes
    $(function (){
     nextOpen();
    });

    // This is called repeatedly at initialization until all desired nodes
    // have been opened.
    function nextOpen() {
      if (preopen.length) {
        var thisid = preopen.pop();

      if (thisid == 0 || preopen.length > 0) {
       if (thisid > 0)
        toggle(thisid);
       else
        $.getScript('types_ajax.php?id=' + encodeURIComponent(thisid) + '&order=' + <?php echo js_url($order); ?> + '&labid=' + <?php echo js_url($labid); ?>);
      }
      else {
       recolor();
      }
     }
     else {
      recolor();
     }
    }

    // toggle expansion indicator from + to - or vice versa
    function swapsign(td1, from, to) {
     var s = td1.html();
     var i = s.indexOf('>' + from + ' <');
     if (i >= 0) td1.html(s.substring(0,i+1) + to + s.substring(i+2));
    }

    // onclick handler to expand or collapse a node
    function toggle(id) {
      var td1 = $('#td' + id);
     if (!td1.hasClass('haskids')) return;
     if (td1.hasClass('isExpanded')) {
      $('#con' + id).remove();
      td1.removeClass('isExpanded');
      swapsign(td1, '-', '+');
      recolor();
     }
     else {
      td1.parent().after('<tr class="outertr"><td colspan="7" id="con' + id + '" style="padding:0">Loading...</td></tr>');
      td1.addClass('isExpanded');
      swapsign(td1, '+', '-');
      $.getScript('types_ajax.php?id=' + encodeURIComponent(id) + '&order=' + <?php echo js_url($order); ?> + '&labid=' + <?php echo js_url($labid); ?>);
     }
    }

    // Called by the edit window to refresh a given node's children
    function refreshFamily(id, haskids) {
     if (id) { // id == 0 means top level
      var td1 = $('#td' + id);
      if (td1.hasClass('isExpanded')) {
       $('#con' + id).remove();
       td1.removeClass('isExpanded');
       swapsign(td1, '-', '+');
      }
      if (td1.hasClass('haskids') && !haskids) {
       td1.removeClass('haskids');
       // swapsign(td1, '+', '.');
       swapsign(td1, '+', '|');
       return;
      }
      if (!td1.hasClass('haskids') && haskids) {
       td1.addClass('haskids');
       // swapsign(td1, '.', '+');
       swapsign(td1, '|', '+');
      }
      if (haskids) {
       td1.parent().after('<tr class="outertr"><td colspan="7" id="con' + id + '" style="padding:0">Loading...</td></tr>');
       td1.addClass('isExpanded');
       swapsign(td1, '+', '-');
      }
     }
     if (haskids)
      $.getScript('types_ajax.php?id=' + encodeURIComponent(id) + '&order=' + <?php echo js_url($order); ?> + '&labid=' + <?php echo js_url($labid); ?>);
     else
      recolor();
    }

    // edit/add a node
    function handleNode(id, type, add, lab) {
        var editTitle = '<i class="fa fa-pencil" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Edit Mode"); ?> + ' ';
        var addTitle = '<i class="fa fa-plus" style="width:20px;" aria-hidden="true"></i> ' + <?php echo xlj("Add Mode"); ?>;
        if (type > 0) {
            type = (type === 1 && !add) ? 'fgp' : 'for';
        }
        let url = 'types_edit.php?addfav=' + encodeURIComponent(type) + '&labid=' + encodeURIComponent(lab) + '&parent=0&typeid=' + encodeURIComponent(id);

        if (add) {
            url = 'types_edit.php?addfav=' + encodeURIComponent(type) + '&labid=' + encodeURIComponent(lab) + '&typeid=0&parent=' + encodeURIComponent(id);
            dlgopen(url, '_blank', 800, 750, false, addTitle);
        } else {
            dlgopen(url, '_blank', 800, 750, false, editTitle);
        }
    }

    // call this to alternate row colors when anything changes the number of rows
    function recolor() {
     var i = 0;
     $('#con0 tr').each(function(index) {
      // skip any row that contains other rows
      if ($(this).hasClass('outertr')) return;
      this.className = (i++ & 1) ? "evenrow" : "oddrow";
     });
    }

    // Callback from popups to refresh this display.
    function refreshme() {
     // location.reload();
     document.forms[0].submit();
    }
    </script>

</head>

<body class="body_nav">
    <?php
    if ($GLOBALS['enable_help'] == 1) {
        $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 2) {
        $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 0) {
        $help_icon = '';
    }
    ?>
    <div class="container">
        <div class="row">
             <div class="col-sm-12">
                <div class="page-header clearfix">
                    <h2 id="header_title" class="clearfix"><span id='header_text'><?php echo xlt('Configure Orders and Results');?></span><?php echo $help_icon; ?></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <form method='post' name='theform'
                    action='types.php?popup=<?php echo attr_url($popup); ?>&order=<?php
                    echo attr_url($order);
                    if (isset($_GET['formid'])) {
                        echo '&formid=' . attr_url($_GET['formid']);
                    }

                    if (isset($_GET['formseq'])) {
                        echo '&formseq=' . attr_url($_GET['formseq']);
                    }
                    ?>'>
                    <div class="btn-group">
                        <button type="button" name="form_search" class="btn btn-default btn-refresh" onclick="refreshme()"><?php echo xlt('Refresh');?></button>
                        <button type="button" class="btn btn-default btn-add" name='add_node_btn' id='add_node_button'  onclick='handleNode(0,"","")'><?php echo xlt('Add Top Level');?></button>
                    </div>
                    <br>
                    <br>
                    <div class = "table-responsive">
                        <table class="table" style="margin-bottom:0">
                            <thead>
                                <tr class='head'>
                                    <td class='col1' align='left'>&nbsp;&nbsp;<?php echo xlt('Name') ?> <i id="name-tooltip" class="fa fa-info-circle oe-text-black" aria-hidden="true"></i></td>
                                    <td class='col2 oe-pl0' align='left'><?php echo xlt('Category') ?> <i id="order-tooltip" class="fa fa-info-circle oe-text-black" aria-hidden="true"></i></td>
                                    <td class='col3 oe-pl0' align='left'><?php echo xlt('Code') ?> <i id="code-tooltip" class="fa fa-info-circle oe-text-black" aria-hidden="true"></i></td>
                                    <td class='col6 oe-pl0' align='left'><?php echo xlt('Tier') ?> <i id="tier-tooltip" class="fa fa-info-circle oe-text-black" aria-hidden="true"></i></td>
                                    <td class='col4 oe-pl0' align='left'><?php echo xlt('Description') ?></td>
                                    <td class='col5 oe-pl0' align='left'><?php echo xlt('Edit') ?></td>
                                    <td class='col5 oe-pl0' align='center'><?php echo xlt('Add') ?></td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div id="con0"></div>
                    <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 text-left position-override">
                            <div class="btn-group btn-group-pinch" role="group">
                                <?php if ($popup) { ?>
                                    <button type="submit" class="btn btn-default btn-save" name='form_save' value='<?php echo xla('Save'); ?>'><?php echo xlt('Save');?></button>
                                    <button class="btn btn-link btn-cancel btn-separate-left" onclick="CancelDistribute()"><?php echo xlt('Cancel');?></button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div><!--End of Container div-->
   <br>
    <?php
    //home of the help modal ;)
    //$GLOBALS['enable_help'] = 0; // Please comment out line if you want help modal to function on this page
    if ($GLOBALS['enable_help'] == 1) {
        echo "<script>var helpFile = 'configure_orders_help.php'</script>";
        //help_modal.php lives in interface, set path accordingly
        require "../help_modal.php";
    }
    ?>
    <script>
    //jqury-ui tooltip
        $(function (){
            //for jquery tooltip to function if jquery 1.12.1.js is called via jquery-ui in the Header::setupHeader
            // the relevant css file needs to be called i.e. jquery-ui-darkness - to get a black tooltip
            $('#name-tooltip').attr( "title", <?php echo xlj('The actual tests or procedures that can be searched for and ordered are highlighted in yellow'); ?> +  ". "  + <?php echo xlj('Click on the blue plus sign under Name to reveal test names'); ?>).tooltip();
            $('#order-tooltip').attr( "title", <?php echo xlj('The entries highlighted in yellow can be ordered as a test or procedure those highlighted in pink can be ordered as a Custom Group'); ?> +  ". "  + <?php echo xlj('Click on the blue plus sign under Name to reveal test names'); ?>).tooltip();
            $('#code-tooltip').attr( "title", <?php echo xlj('Category - Order, Result and Recommendation need an identifying code');?> + ". " + <?php echo xlj('Red Triangle indicates a required code that is missing')?> + ".").
            tooltip();
            $('#tier-tooltip').attr( "title", <?php echo xlj('Shows the hierarchal level of this line');?> + ". " + <?php echo xlj('Tier 1 entries should be of Category Top Group')?> + ".").
            tooltip();
            $('table td .required-tooltip').attr( "title", <?php echo xlj('For proper tabulated display of tests and results an identifying code is required'); ?>).tooltip();
            $("table td .required-tooltip").fadeIn(500);
            $("table td .required-tooltip3").fadeOut(1000);
            $("table td .required-tooltip").fadeIn(500);
            $(".plusminus").click(function(){
                $(".required-tooltip").effect("pulsate", {times:1}, 4000);
            });
        });
    </script>

</body>
</html>

