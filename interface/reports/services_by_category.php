<?php

/**
 * Services by category report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Utils\FormatMoney;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

?>
<html>
<head>
    <title><?php echo xlt('Services by Category'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <style>
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }
            #report_results table {
               margin-top: 0px;
            }
        }

        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
        }

        table.mymaintable,
        table.mymaintable td,
        table.mymaintable th {
            border-collapse: collapse;
        }
        table.mymaintable td, table.mymaintable th {
            padding: 1pt 4pt 1pt 4pt;
        }
    </style>

    <script>

     $(function () {
         oeFixedHeaderSetup(document.getElementById('mymaintable'));
         var win = top.printLogSetup ? top : opener.top;
         win.printLogSetup(document.getElementById('printbutton'));
     });
    </script>
</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Services by Category'); ?></span>

<form method='post' action='services_by_category.php' name='theform' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

<table>
 <tr>
  <td width='280px'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td>
               <select name='filter' class='form-control'>
                <option value='0'><?php echo xlt('All'); ?></option>
            <?php
            foreach ($code_types as $key => $value) {
                echo "<option value='" . attr($value['id']) . "'";
                if (!empty($filter) && ($value['id'] == $filter)) {
                    echo " selected";
                }

                echo ">" . text($key) . "</option>\n";
            }
            ?>
               </select>
            </td>
            <td>
        <div class="checkbox">
                <label><input type='checkbox' name='include_uncat' value='1'<?php echo (!empty($_REQUEST['include_uncat'])) ? " checked" : ""; ?> />
                <?php echo xlt('Include Uncategorized'); ?></label>
        </div>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left:1px solid;'>
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                      <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                            <?php echo xlt('Submit'); ?>
                      </a>
                        <?php if (!empty($_POST['form_refresh'])) { ?>
                        <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                        </a>
                        <?php } ?>
          </div>
                </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

<?php
if (!empty($_POST['form_refresh'])) {
    ?>

<div id="report_results">


<table width='98%' id='mymaintable' class='table table-striped mymaintable'>
<thead class='thead-light'>
<tr>
<th class='bold'><?php echo xlt('Category'); ?></th>
<th class='bold'><?php echo xlt('Type'); ?></th>
<th class='bold'><?php echo xlt('Code'); ?></th>
<th class='bold'><?php echo xlt('Mod'); ?></th>
<th class='bold'><?php echo xlt('Units'); ?></th>
<th class='bold'><?php echo xlt('Description'); ?></th>
    <?php if (related_codes_are_used()) { ?>
   <th class='bold'><?php echo xlt('Related'); ?></th>
<?php } ?>
    <?php
    $pres = sqlStatement("SELECT title FROM list_options " .
     "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq");
    while ($prow = sqlFetchArray($pres)) {
    // Added 5-09 by BM - Translate label if applicable
        echo "   <th class='bold' align='right' nowrap>" . text(xl_list_label($prow['title'])) . "</th>\n";
    }
    ?>
</tr>
</thead>
<tbody>
    <?php

    $sqlBindArray = array();
    $filter = sanitizeNumber($_REQUEST['filter']);
    $where = "c.active = 1";
    if ($filter) {
        $where .= " AND c.code_type = ?";
        array_push($sqlBindArray, $filter);
    }
    if (empty($_REQUEST['include_uncat'])) {
        $where .= " AND c.superbill != '' AND c.superbill != '0'";
    }

    $res = sqlStatement("SELECT c.*, lo.title FROM codes AS c " .
    "LEFT OUTER JOIN list_options AS lo ON lo.list_id = 'superbill' " .
    "AND lo.option_id = c.superbill AND lo.activity = 1 " .
    "WHERE $where ORDER BY lo.title, c.code_type, c.code, c.modifier", $sqlBindArray);

    $last_category = '';
    $irow = 0;
    while ($row = sqlFetchArray($res)) {
        $category = $row['title'] ? $row['title'] : xl('Uncategorized');
        $disp_category = ' ';
        if ($category !== $last_category) {
            $last_category = $category;
            $disp_category = $category;
            ++$irow;
        }

        foreach ($code_types as $key => $value) {
            if ($value['id'] == $row['code_type']) {
                break;
            }
        }

        echo "  <tr>\n";
        // Added 5-09 by BM - Translate label if applicable
        echo "   <td class='text'>" . text(xl_list_label($disp_category)) . "</td>\n";
        echo "   <td class='text'>" . text($key) . "</td>\n";
        echo "   <td class='text'>" . text($row['code']) . "</td>\n";
        echo "   <td class='text'>" . text($row['modifier']) . "</td>\n";
        echo "   <td class='text'>" . text($row['units']) . "</td>\n";
        echo "   <td class='text'>" . text($row['code_text']) . "</td>\n";

        if (related_codes_are_used()) {
            // Show related codes.
            echo "   <td class='text'>";
            $arel = explode(';', $row['related_code']);
            foreach ($arel as $tmp) {
                list($reltype, $relcode) = explode(':', $tmp);
                $reltype = $code_types[$reltype]['id'];
                $relrow = sqlQuery("SELECT code_text FROM codes WHERE " .
                "code_type = ? AND code = ? LIMIT 1", array($reltype, $relcode));
                echo text($relcode) . ' ' . text(trim($relrow['code_text'])) . '<br />';
            }

            echo "</td>\n";
        }

        $pres = sqlStatement("SELECT p.pr_price " .
        "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
        "p.pr_id = ? AND p.pr_selector = '' " .
        "AND p.pr_level = lo.option_id " .
        "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq", array($row['id']));
        while ($prow = sqlFetchArray($pres)) {
            echo "   <td class='text' align='right'>" . text(FormatMoney::getBucks($prow['pr_price'])) . "</td>\n";
        }

        echo "  </tr>\n";
    }
    ?>
</tbody>
</table>

<?php } // end of submit logic ?>
</div>

</body>
</html>
