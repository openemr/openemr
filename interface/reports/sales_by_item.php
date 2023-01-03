<?php

/**
 * This is a report of sales by item description.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015-2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('acct', 'rep') && !AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Sales by Item")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_provider  = $_POST['form_provider'] ?? null;
if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    // only allow user to see their encounter information
    $form_provider = $_SESSION['authUserID'];
}

if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) {
    $form_details = (!empty($_POST['form_details'])) ? true : false;
} else {
    $form_details = false;
}

function bucks($amount)
{
    if ($amount) {
        return oeFormatMoney($amount);
    }
}

function display_desc($desc)
{
    if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
        $desc = $matches[1];
    }

    return $desc;
}

function thisLineItem($patient_id, $encounter_id, $rowcat, $description, $transdate, $qty, $amount, $irnumber = '')
{
    global $product, $category, $producttotal, $productqty, $cattotal, $catqty, $grandtotal, $grandqty;
    global $productleft, $catleft;

    $invnumber = $irnumber ? $irnumber : "$patient_id.$encounter_id";
    $rowamount = sprintf('%01.2f', $amount);

    $patdata = sqlQuery("SELECT " .
    "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
    "p.street, p.city, p.state, p.postal_code, " .
    "p.ss, p.sex, p.status, p.phone_home, " .
    "p.phone_biz, p.phone_cell, p.hipaa_notice " .
    "FROM patient_data AS p " .
    "WHERE p.pid = ? LIMIT 1", array($patient_id));

    $pat_name = $patdata['fname'] . ' ' . $patdata['mname'] . ' ' . $patdata['lname'];

    if (empty($rowcat)) {
        $rowcat = xl('None{{Sales}}');
    }

    $rowproduct = $description;
    if (! $rowproduct) {
        $rowproduct = xl('Unknown');
    }

    if ($product != $rowproduct || $category != $rowcat) {
        if ($product) {
            // Print product total.
            if ($_POST['form_csvexport']) {
                if (! $_POST['form_details']) {
                    echo csvEscape(display_desc($category)) . ',';
                    echo csvEscape(display_desc($product))  . ',';
                    echo csvEscape($productqty)             . ',';
                    echo csvEscape(bucks($producttotal));
                    echo "\n";
                }
            } else {
                ?>
       <tr bgcolor="#ddddff">
        <td class="detail">
                <?php echo text(display_desc($catleft));
                $catleft = " "; ?>
  </td>
  <td class="detail" colspan="3">
                <?php
                if ($_POST['form_details']) {
                    echo xlt('Total for') . ' ';
                }

                echo text(display_desc($product)); ?>
  </td>
                <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
  &nbsp;
  </td>
    <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right">
                <?php echo text($productqty); ?>
  </td>
  <td align="right">
                <?php echo text(bucks($producttotal)); ?>
  </td>
 </tr>
                <?php
            } // End not csv export
        }

        $producttotal = 0;
        $productqty = 0;
        $product = $rowproduct;
        $productleft = $product;
    }

    if ($category != $rowcat) {
        if ($category) {
            // Print category total.
            if (!$_POST['form_csvexport']) {
                ?>

       <tr bgcolor="#ffdddd">
        <td class="detail">
         &nbsp;
        </td>
        <td class="detail" colspan="3">
                <?php echo xlt('Total for category') . ' ';
                echo text(display_desc($category)); ?>
  </td>
                <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
    <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right">
                <?php echo text($catqty); ?>
  </td>
  <td align="right">
                <?php echo text(bucks($cattotal)); ?>
  </td>
 </tr>
                <?php
            } // End not csv export
        }

        $cattotal = 0;
        $catqty = 0;
        $category = $rowcat;
        $catleft = $category;
    }

    if (!empty($_POST['form_details'])) {
        if ($_POST['form_csvexport']) {
            echo csvEscape(display_desc($category)) . ',';
            echo csvEscape(display_desc($product)) . ',';
            echo csvEscape(oeFormatShortDate(display_desc($transdate))) . ',';
            if ($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2) {
                echo csvEscape($pat_name) . ',';
            }

            if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {
                echo csvEscape(display_desc($invnumber)) . ',';
            }

            if ($GLOBALS['sales_report_invoice'] == 1) {
                echo csvEscape($patient_id) . ',';
            }

           // echo '"' . display_desc($invnumber) . '",';
            echo csvEscape(display_desc($qty)) . ',';
            echo csvEscape(bucks($rowamount));
            echo "\n";
        } else {
            ?>

     <tr>
      <td class="detail">
            <?php echo text(display_desc($catleft));
            $catleft = " "; ?>
  </td>
  <td class="detail">
            <?php echo text(display_desc($productleft));
            $productleft = " "; ?>
  </td>
  <td>
            <?php echo text(oeFormatShortDate($transdate)); ?>
  </td>
            <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
        <?php } ?>
            <?php if ($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2) { ?>
  <td>
                <?php echo text($pat_name); ?>
  </td>
        <?php } ?>
  <td class="detail">
            <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) { ?>
   <a href='../patient_file/pos_checkout.php?ptid=<?php echo attr_url($patient_id); ?>&enc=<?php echo attr_url($encounter_id); ?>'>
                <?php echo text($invnumber); ?></a>
    <?php }

            if ($GLOBALS['sales_report_invoice'] == 1) {
                echo text($patient_id);
            }
            ?>
      </td>
            <?php if ($GLOBALS['sales_report_invoice'] == 0) {?>
  <td>
   &nbsp;
  </td>
        <?php } ?>
      <td align="right">
            <?php echo text($qty); ?>
      </td>
      <td align="right">
            <?php echo text(bucks($rowamount)); ?>
      </td>
     </tr>
            <?php
        } // End not csv export
    } // end details
    $producttotal += $rowamount;
    $cattotal     += $rowamount;
    $grandtotal   += $rowamount;
    $productqty   += $qty;
    $catqty       += $qty;
    $grandqty     += $qty;
} // end function

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_POST['form_facility'] ?? null;

if (!empty($_POST['form_csvexport'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=sales_by_item.csv");
    header("Content-Description: File Transfer");
    // CSV headers:
    if ($_POST['form_details']) {
        echo '"Category",';
        echo '"Item",';
        echo '"Date",';
        if ($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2) {
            echo '"Name",';
        }

        if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {
            echo '"Invoice",';
        }

        if ($GLOBALS['sales_report_invoice'] == 1) {
            echo '"ID",';
        }

        echo '"Qty",';
        echo '"Amount"' . "\n";
    } else {
        echo '"Category",';
        echo '"Item",';
        echo '"Qty",';
        echo '"Total"' . "\n";
    }
} else { // end export
    ?>
<html>
<head>

    <title><?php echo xlt('Sales by Item'); ?></title>

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
            #report_results {
               margin-top: 30px;
            }
        }

        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
              display: none;
            }
        }

        table.mymaintable, table.mymaintable td {
            border-collapse: collapse;
        }
        table.mymaintable td {
            padding: 1px 5px 1px 5px;
        }
    </style>

    <script>
        $(function () {
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });
    </script>
</head>

<title><?php echo xlt('Sales by Item') ?></title>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Sales by Item'); ?></span>

<form method='post' action='sales_by_item.php' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
<tr>
<td width='630px'>
  <div style='float:left'>
  <table class='text'>
      <tr>
          <td class='col-form-label'>
            <?php echo xlt('Facility'); ?>:
          </td>
          <td>
        <?php dropdown_facility($form_facility, 'form_facility', true); ?>
          </td>
          <td class='col-form-label'>
            <?php echo xlt('From'); ?>:
          </td>
          <td>
            <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
          </td>
          <td class='col-form-label'>
            <?php echo xlt('To{{Range}}'); ?>:
          </td>
          <td>
            <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
          </td>
      </tr>
  </table>
  <table class='text'>
      <tr>
        <td class='col-form-label'>
        <?php echo xlt('Provider'); ?>:
        </td>
        <td>
        <?php
        if (AclMain::aclCheckCore('acct', 'rep_a')) {
          // Build a drop-down list of providers.
            $query = "select id, lname, fname from users where " .
              "authorized = 1 order by lname, fname";
            $res = sqlStatement($query);
            echo "   &nbsp;<select name='form_provider' class='form-control'>\n";
            echo "    <option value=''>-- " . xlt('All Providers') . " --\n";
            while ($row = sqlFetchArray($res)) {
                $provid = $row['id'];
                echo "    <option value='" . attr($provid) . "'";
                if (!empty($_REQUEST['form_provider']) && ($provid == $_REQUEST['form_provider'])) {
                    echo " selected";
                }

                echo ">" . text($row['lname']) . ", " . text($row['fname']) . "\n";
            }

            echo "   </select>\n";
        } else {
            echo "<input type='hidden' name='form_provider' value='" . attr($_SESSION['authUserID']) . "'>";
        }
        ?>
            &nbsp;
          </td>
          <td>
            <div class='checkbox'>
           <label><input type='checkbox' name='form_details'<?php echo ($form_details) ? ' checked' : ''; ?>>
            <?php echo xlt('Details'); ?></label>
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
                      <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                      </a>
                    <?php if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) { ?>
                            <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                            </a>
                            <a href='#' class='btn btn-secondary btn-transmit' onclick='$("#form_refresh").attr("value",""); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
                                <?php echo xlt('CSV Export'); ?>
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
    if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) {
        ?>

<div id="report_results">
<table width='98%' id='mymaintable' class='table table-striped mymaintable'>
<thead class='thead-light'>
 <th>
        <?php echo xlt('Category'); ?>
 </th>
 <th>
        <?php echo xlt('Item'); ?>
 </th>
 <th>
        <?php
        if ($form_details) {
            echo xlt('Date');
        } ?>
 </th>
        <?php if ($GLOBALS['sales_report_invoice'] == 2) {?>
  <th>
   &nbsp;
  </th>
    <?php } ?>
 <th>
        <?php
        if ($GLOBALS['sales_report_invoice'] == 0) {
            if ($form_details) {
                echo ' ';
            }
            ?>
   </th>
   <th>
            <?php
            if ($form_details) {
                echo xlt('Invoice');
            }
        }

        if ($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2) {
            if ($form_details) {
                echo xlt('Name');
            }
        } ?>
  </th>
  <th>
        <?php
        if ($GLOBALS['sales_report_invoice'] == 2) {
            if ($form_details) {
                echo xlt('Invoice');
            }
        }

        if ($GLOBALS['sales_report_invoice'] == 1) {
            if ($form_details) {
                echo xlt('ID');
            }
        }
        ?>
  </th>
  <th align="right">
        <?php echo xlt('Qty'); ?>
  </th>
  <th align="right">
        <?php echo xlt('Amount'); ?>
  </th>
 </thead>
 <tbody>
        <?php
    } // end not export
}

if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) {
    $from_date = $form_from_date . ' 00:00:00';
    $to_date = $form_to_date . ' 23:59:59';
    $category = "";
    $catleft = "";
    $cattotal = 0;
    $catqty = 0;
    $product = "";
    $productleft = "";
    $producttotal = 0;
    $productqty = 0;
    $grandtotal = 0;
    $grandqty = 0;

    $sqlBindArray = array();
    $query = "SELECT b.fee, b.pid, b.encounter, b.code_type, b.code, b.units, " .
    "b.code_text, fe.date, fe.facility_id, fe.provider_id, fe.invoice_refno, lo.title " .
    "FROM billing AS b " .
    "JOIN code_types AS ct ON ct.ct_key = b.code_type " .
    "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
    "LEFT JOIN codes AS c ON c.code_type = ct.ct_id AND c.code = b.code AND c.modifier = b.modifier " .
    "LEFT JOIN list_options AS lo ON lo.list_id = 'superbill' AND lo.option_id = c.superbill AND lo.activity = 1 " .
    "WHERE b.code_type != 'COPAY' AND b.activity = 1 AND b.fee != 0 AND " .
    "fe.date >= ? AND fe.date <= ?";
    array_push($sqlBindArray, $from_date, $to_date);
    // If a facility was specified.
    if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray, $form_facility);
    }

    if ($form_provider) {
        $query .= " AND fe.provider_id = ?";
        array_push($sqlBindArray, $form_provider);
    }

    $query .= " ORDER BY lo.title, b.code, fe.date, fe.id";
    //
    $res = sqlStatement($query, $sqlBindArray);
    while ($row = sqlFetchArray($res)) {
        thisLineItem(
            $row['pid'],
            $row['encounter'],
            $row['title'],
            $row['code'] . ' ' . $row['code_text'],
            substr($row['date'], 0, 10),
            $row['units'],
            $row['fee'],
            $row['invoice_refno']
        );
    }

    //
    $sqlBindArray = array();
    $query = "SELECT s.sale_date, s.fee, s.quantity, s.pid, s.encounter, " .
    "d.name, fe.date, fe.facility_id, fe.provider_id, fe.invoice_refno " .
    "FROM drug_sales AS s " .
    "JOIN drugs AS d ON d.drug_id = s.drug_id " .
    "JOIN form_encounter AS fe ON " .
    "fe.pid = s.pid AND fe.encounter = s.encounter AND " .
    "fe.date >= ? AND fe.date <= ? " .
    "WHERE s.fee != 0";
    array_push($sqlBindArray, $from_date, $to_date);
    // If a facility was specified.
    if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray, $form_facility);
    }

    if ($form_provider) {
        $query .= " AND fe.provider_id = ?";
        array_push($sqlBindArray, $form_provider);
    }

    $query .= " ORDER BY d.name, fe.date, fe.id";
    //
    $res = sqlStatement($query, $sqlBindArray);
    while ($row = sqlFetchArray($res)) {
        thisLineItem(
            $row['pid'],
            $row['encounter'],
            xl('Products'),
            $row['name'],
            substr($row['date'], 0, 10),
            $row['quantity'],
            $row['fee'],
            $row['invoice_refno']
        );
    }

    if ($_POST['form_csvexport']) {
        if (! $_POST['form_details']) {
            echo csvEscape(display_desc($product)) . ',';
            echo csvEscape($productqty)            . ',';
            echo csvEscape(bucks($producttotal));
            echo "\n";
        }
    } else {
        ?>

 <tr bgcolor="#ddddff">
  <td class="detail">
        <?php echo text(display_desc($catleft));
        $catleft = " "; ?>
  </td>
  <td class="detail" colspan="3">
        <?php
        if (!empty($_POST['form_details'])) {
            echo xlt('Total for') . ' ';
        }

        echo text(display_desc($product)); ?>
  </td>
        <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
    <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right">
        <?php echo text($productqty); ?>
  </td>
  <td align="right">
        <?php echo text(bucks($producttotal)); ?>
  </td>
 </tr>

 <tr bgcolor="#ffdddd">
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail" colspan="3"><strong>
        <?php echo xlt('Total for category') . ' ';
        echo text(display_desc($category)); ?>
  </strong></td>
        <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
    <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right"><strong>
        <?php echo text($catqty); ?>
  </strong></td>
  <td align="right"><strong>
        <?php echo text(bucks($cattotal)); ?>
  </strong></td>
 </tr>

 <tr>
  <td class="detail" colspan="4"><strong>
        <?php echo xlt('Grand Total'); ?>
  </strong></td>
        <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
    <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right"><strong>
        <?php echo text($grandqty); ?>
  </strong></td>
  <td align="right"><strong>
        <?php echo text(bucks($grandtotal)); ?>
  </strong></td>
 </tr>
        <?php $report_from_date = oeFormatShortDate($form_from_date)  ;
        $report_to_date = oeFormatShortDate($form_to_date)  ;
        ?>
<div align='right'><span class='title' ><?php echo xlt('Report Date') . ' '; ?><?php echo text($report_from_date);?> - <?php echo text($report_to_date);?></span></div>
        <?php
    } // End not csv export
}

if (empty($_POST['form_csvexport'])) {
    if (!empty($_POST['form_refresh'])) {
        ?>

</tbody>
</table>
</div> <!-- report results -->
        <?php
    } else { ?>
<div class='text'>
        <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
        <?php
    } ?>

</form>

</body>

</html>
    <?php
} // End not csv export
?>
