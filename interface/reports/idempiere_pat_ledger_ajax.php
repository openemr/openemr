<?php

require_once("../globals.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/odbcconf.php");
require_once("./idempiere_pat_ledger_fun.php");

function prepareChildData($form_extra_payment_filter, $rowData, $paymentColsList, $chargeColsList) {
    return array(
        "entryNumber" => $rowData,
        "type" => $form_extra_payment_filter == "charge" ? "payment" : "charge"
    );
}

$preparedHTML = '';

//This is print view
if(isset($_REQUEST['page']) && $_REQUEST['page'] == "print") {
    if($_REQUEST['form_extra_payment_filter'] == "charge") {
      $colsList = $printChargeColsList;
      $rowDetailColList = $paymentColsList1;
      $rowData = getChargesData($idempiere_connection, false, $_REQUEST['chartNumber'], $_REQUEST['form_extra_case_filter'], $_REQUEST['form_from_date'], $_REQUEST['form_to_date'], 'print');
      $subViewTitle = "Payment Details";
    } else if($_REQUEST['form_extra_payment_filter'] == "payment") {
      $colsList = $paymentColsList;
      $rowDetailColList = $chargeColsList1;
      $rowData = getPaymentData($idempiere_connection, false, $_REQUEST['chartNumber'], $_REQUEST['form_extra_case_filter'], $_REQUEST['form_from_date'], $_REQUEST['form_to_date']);
      $subViewTitle = "Charge Details";
    }

    $entryNumbers = array();
    foreach ($rowData as $key => $rowItem) {
      $entryNumbers[] = $rowItem['data'];
    }


    //Get RowDetails For Charge View or Payment View
    if($_REQUEST['form_extra_payment_filter'] == "charge") {
      $childRowData = getPaymentDataForPrint($idempiere_connection, $entryNumbers);
    } else if($_REQUEST['form_extra_payment_filter'] == "payment") {
      $childRowData = getChargeDataForPrint($idempiere_connection, $entryNumbers);
    }

    echo '<table class="printTable">';
    echo '<thead>';
    echo '<tr>';
      foreach ($colsList as $key => $col) {
          echo '<th>'.$col['title'].'</th>';
      }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
        $itemNo = 0;
        foreach ($rowData as $rowkey => $row) {
            echo '<tr data-child-value="'.$rowkey.'">';
                foreach ($colsList as $key => $col) {
                    echo '<td>'.$row[$col['name']].'</td>';
                }
            echo '</tr>';

            //Print Row Details
            echo '<tr><td colspan="'.count($colsList).'"><span>'.$subViewTitle.'</span></td></tr>';
            echo '<tr><td colspan="'.count($colsList).'" class="rowDetails">';
            echo '<table class="subTableContainer"><tr><td style="width: 30px;overflow: hidden;display: inline-block;white-space: nowrap;"></td><td>';
            echo prepareChildHTML("payment", $childRowData && $childRowData[$row['data']] ? $childRowData[$row['data']] : array(), $rowDetailColList, $colsList);
            echo '</td></tr></table>';
            echo '</td></tr>';
            if(++$itemNo !== count($rowData)) {
              echo '<tr><td colspan="'.count($colsList).'" style="height:28px;"></td></tr>';
            }
        }

        if(count($rowData) == 0) {
          echo '<tr><td align="center" colspan="'.count($colsList).'"><div class="emptyRow">No records found</div></td></tr>';
        }

    echo  '</tbody>';
    echo '</table>';

} else if(isset($_REQUEST['page']) && $_REQUEST['page'] == "balances") {
  //Balances View of Medisoft Leadger

  $chartNumber = $_REQUEST['chartNumber'];
  $form_extra_case_filter = $_REQUEST['form_extra_case_filter'];
  $balances = calculateBalance($idempiere_connection, $chartNumber, $form_extra_case_filter); 
?>
  <div>
      <div class='balance-label'>
        <span><b>Overall Balance:</b></span>
        <span><?php echo $balances['overallBalance'] ? number_format(($balances['overallBalance']), 2, '.', ',') : '0'; ?></span>
      </div>
      <div class='balance-label'>
        <span><b>OverAll UnAll Amt:</b></span>
        <span><?php echo $balances['overAllUnAllocatedAmt'] ? number_format(($balances['overAllUnAllocatedAmt']), 2, '.', ',') : '0'; ?></span>
      </div>
  </div>
  <div>
      <div class='balance-label'>
        <span><b>Case Billed:</b></span>
        <span><?php echo $balances['caseBilled'] ? number_format(($balances['caseBilled']), 2, '.', ',') : '0'; ?></span>
      </div>
      <div class='balance-label'>
        <span><b>Case Paid Amt: </b></span>
        <span><?php echo $balances['casePaidAmt'] ? number_format(($balances['casePaidAmt']), 2, '.', ',') : '0'; ?></span>
      </div>
  </div>
  <div>
      <div class='balance-label'>
        <span><b>Case Balance: </b></span>
        <span><?php echo $balances['caseBalance'] ? number_format(($balances['caseBalance']), 2, '.', ',') : '0'; ?></span>
      </div>
  </div>
  <div>
     <div class='balance-label'>
        <span><b>Case Adj Amt:</b></span>
        <span><?php echo $balances['caseAdjAmt'] ? number_format(($balances['caseAdjAmt']), 2, '.', ',') : '0'; ?></span>
      </div> 
     <div class='balance-label'>
        <span><b>Case UnAll Amt:</b></span>
        <span><?php echo $balances['caseUnAllocatedAmt'] ? number_format(($balances['caseUnAllocatedAmt']), 2, '.', ',') : '0'; ?></span>
      </div> 
  </div>
  <div>
  </div>
  <div>
      <div class='balance-label'>
        <span><b>Patients responsibility for the case:</b></span>
        <span><?php echo $balances['patientResponsibility'] ? number_format(($balances['patientResponsibility']), 2, '.', ',') : '0'; ?></span>
      </div>
  </div>
<?php
} else if(isset($_REQUEST['page']) && $_REQUEST['page'] == "datatable") {
  //This portion fetch Charge View or Payment View

  $form_from_date = $_REQUEST['form_from_date'];
  $form_to_date = $_REQUEST['form_to_date'];
  $chartNumber = $_REQUEST['chartNumber'];
  $form_extra_payment_filter = $_REQUEST['form_extra_payment_filter'];
  $form_extra_case_filter = $_REQUEST['form_extra_case_filter'];

  if($form_extra_payment_filter == "charge") {
    $rowData = getChargesData($idempiere_connection, false, $chartNumber, $form_extra_case_filter, $form_from_date, $form_to_date);
    $colsList = $chargeColsList;

  } else if($form_extra_payment_filter == "payment") {
    $rowData = getPaymentData($idempiere_connection, false, $chartNumber, $form_extra_case_filter, $form_from_date, $form_to_date);
    $colsList = $paymentColsList;
  }

  $childData = [];

?>
  <table id="ledger_result" class="ledger_result text table table-sm" cellspacing="0" width="100%">
      <thead class="thead-light">
          <tr>
              <th class="dt-control"></th>
              <?php
                  foreach ($colsList as $key => $col) {
                      $colWidth = $col['width'] ? 'width="'.$col['width'].'"' : '';
                      echo '<th '.$colWidth.' >'.$col['title'].'</th>';
                  }
              ?>
          </tr>
      </thead>
      <tbody>
        <?php
            foreach ($rowData as $rowkey => $row) {
                echo '<tr data-child-value="'.$rowkey.'">';
                    echo '<td class="details-control"></td>';
                    foreach ($colsList as $key => $col) {
                        $colWidth = $col['width'] ? 'width="'.$col['width'].'"' : '';
                        //       echo '<td '.$colWidth.'>'.$row[$col['name']].'</td>';
                         $dateDataOrder = '';
                        if($col['name'] == "date_from") {
                          $date_from_val = !empty($row[$col['name']]) ? str_replace('-', '/', $row[$col['name']]) : "";
                          $dateDataOrder = $col['name'] == "date_from" ? 'data-order="'.strtotime($date_from_val).'"' : '';
                        }

                        echo '<td '.$colWidth.' '.$dateDataOrder.'>'.$row[$col['name']].'</td>';
                    }
                echo '</tr>';

                $childData[$rowkey] = prepareChildData($form_extra_payment_filter, $row && $row['data'] ? $row['data'] : [], $paymentColsList, $chargeColsList);
            }
        ?>
      </tbody>
  </table>
  <script language="JavaScript">
        var childData = <?php echo json_encode($childData); ?>;
        var chartNumber = "<?php echo $chartNumber; ?>";
        var form_extra_payment_filter = "<?php echo $form_extra_payment_filter; ?>"; 
        var form_extra_case_filter = "<?php echo $form_extra_case_filter; ?>"; 
        var form_from_date = "<?php echo $form_from_date; ?>"; 
        var form_to_date = "<?php echo $form_to_date; ?>";

        function format(value) {
            var div = $('<div/>')
            .addClass( 'loading' )
            .text( 'Loading...' );

            jQuery.ajax( {
                url: 'idempiere_pat_ledger_ajax.php?page=rowdetails',
                data: {
                    entryNumber: childData[value]['entryNumber'],
                    type : childData[value]['type']
                },
                dataType: 'json',
                success: function ( json ) {
                    div.html(json.html).removeClass( 'loading' );

                    jQuery('[data-toggle="tooltip"]').tooltip({
                       position: {
                          my: "right center",
                          at: "left-10 left"
                       }
                    });
                }
            });
         
            return div;
        }
    </script>
<?php
} else if(isset($_REQUEST['page']) && $_REQUEST['page'] == "rowdetails") {
  //This portion help you to fetch Rowdetails for Charge view and Payment View

  if(isset($_REQUEST['entryNumber']) && isset($_REQUEST['type'])) {
      if($_REQUEST['type'] == "charge") {
        // $entryReference = getChargesReference($idempiere_connection, $_REQUEST['entryNumber']);

        // if(!empty($entryReference)) {
        //   $rowData = getChargesData($idempiere_connection, $entryReference);
        // }

        $rowData = getChargeRowDetails($idempiere_connection, $_REQUEST['entryNumber']);

        $preparedHTML = prepareChildHTML($_REQUEST['type'], $rowData ? $rowData : array(), $paymentColsList1, $chargeColsList1);
        
      } else if($_REQUEST['type'] == "payment") {
        // $entryReference = getPaymentReference($idempiere_connection, $_REQUEST['entryNumber']);
        
        // if(!empty($entryReference)) {
        //   $rowData = getPaymentData($idempiere_connection, $entryReference);
        // }

        $rowData = getPaymentRowDetails($idempiere_connection, $_REQUEST['entryNumber']);

        $preparedHTML = prepareChildHTML($_REQUEST['type'], $rowData ? $rowData : array(), $paymentColsList1, $chargeColsList1);
      }
  }

  echo json_encode(array(
      "html" => $preparedHTML,
      "row" => $rowData
  ));
}

?>
