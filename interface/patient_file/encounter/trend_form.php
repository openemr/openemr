<?php

/**
 * Trending script for graphing objects.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2011 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 */

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$formname = $_GET["formname"];
$is_lbf = substr($formname, 0, 3) === 'LBF';

if ($is_lbf) {
  // Determine the default field ID and its title for graphing.
  // This is from the last graphable field in the form.
    $default = sqlQuery(
        "SELECT field_id, title FROM layout_options WHERE " .
        "form_id = ? AND uor > 0 AND edit_options LIKE '%G%' " .
        "ORDER BY group_id DESC, seq DESC, title DESC LIMIT 1",
        array($formname)
    );
}

//Bring in the style sheet
?>
<?php require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; ?>

<?php
// Special case where not setting up the header for a script, so using setupAssets function,
//  which does not autoload anything. The actual header is set up in the script called at
//  the bottom of this script.
Header::setupAssets(['dygraphs', 'jquery']);
?>

<?php
// Hide the current value css entries. This is currently specific
//  for the vitals form but could use this mechanism for other
//  forms.
// Hiding classes:
//  currentvalues - input boxes
//  valuesunfocus - input boxes that are auto-calculated
//  editonly      - the edit and cancel buttons
// Showing class:
//  readonly      - the link back to summary screen
// Also customize the 'graph' class to look like links.
?>
<style>
  .currentvalues {
    display: none;
  }
  .valuesunfocus {
    display: none;
  }
  .editonly {
    display: none !important;
  }

  .graph {
    color: #0000cc;
  }

  #chart {
    margin:0em 1em 2em 2em;
  }
</style>

<script>


// Show the selected chart in the 'chart' div element
function show_graph(table_graph, name_graph, title_graph)
{
    top.restoreSession();
    $.ajax({ url: '../../../library/ajax/graphs.php',
    type: 'POST',
        data: ({
            table: table_graph,
            name: name_graph,
            title: title_graph,
            csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
        }),
        dataType: "json",
        success: function(returnData){

        g2 = new Dygraph(
            document.getElementById("chart"),
            returnData.data_final,
            {
                title: returnData.title,
                delimiter: '\t',
                xRangePad: 20,
                yRangePad: 20,
                width: 480,
                height: 320,
                xlabel: xlabel_translate
            }
        );

            // ensure show the chart div
            $('#chart').show();
        },
        error: function() {
            // hide the chart div
          $('#chart').hide();
          if(!title_graph){
              alert(<?php echo xlj('This item does not have enough data to graph');?> + ".\n" + <?php echo xlj('Please select an item that has more data');?> + ".");
          }
          else {
              alert(title_graph + " " + <?php echo xlj('does not have enough data to graph');?> + ".\n" + <?php echo xlj('Please select an item that has more data');?> + ".");
          }

        }
    });
}

$(function () {

  // Use jquery to show the 'readonly' class entries
  $('.readonly').show();

  // Place click callback for graphing
<?php if ($is_lbf) { ?>
  // For LBF the <td> has an id of label_id_$fieldid
  $(".graph").on("click", function(e){ show_graph(<?php echo js_escape($formname); ?>, this.id.substring(9), $(this).text()) });
<?php } else { ?>
  $(".graph").on("click", function(e){ show_graph('form_vitals', this.id, $(this).text()) });
<?php } ?>

  // Show hovering effects for the .graph links
  $(".graph").on("mouseenter",
    function(){
         $(this).css({color:'#ff5555'});
    }).on("mouseleave",
    function(){
         $(this).css({color:'#0000cc'});
    }
  );

  // show blood pressure graph by default
<?php if ($is_lbf) { ?>
    <?php if (!empty($default)) { ?>
  show_graph(<?php echo js_escape($formname); ?>,<?php echo js_escape($default['field_id']); ?>,<?php echo js_escape($default['title']); ?>);
<?php } ?>
<?php } else { ?>
  show_graph('form_vitals','bps','');
<?php } ?>
});
</script>

<?php
if ($is_lbf) {
  // Use the List Based Forms engine for all LBFxxxxx forms.
    include_once("$incdir/forms/LBF/new.php");
} else {
  // ensure the path variable has no illegal characters
    check_file_dir_name($formname);

    include_once("$incdir/forms/$formname/new.php");
}
?>
