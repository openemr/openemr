<?php
/**
 * Review Dictations
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");

$from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

?>
<html>
<head>
    <?php html_header_show();?>
    <title><?php xl('Dictation Review','e'); ?></title>
    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <script type="text/javascript">

        function submitForm() {
            var fromDate = $("#form_from_date").val();
            var toDate = $("#form_to_date").val();

            if (fromDate === '') {
                alert("<?php echo xls('Please select From date'); ?>");
                return false;
            }
            if (toDate === '') {
                alert("<?php echo xls('Please select To date'); ?>");
                return false;
            }
            if (Date.parse(fromDate) > Date.parse(toDate)) {
                alert("<?php echo xls('From date should be less than To date'); ?>");
                return false;
            }
            else {
                $("#form_refresh").attr("value", "true");
                $("#report_form").submit();
            }
        }

        $( document ).ready(function(){
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

    </script>
<style>
    .cell{
        display: table-cell;
        border: 1px solid #d3d3d3;
    }
</style>
</head>

<body class="body_top">

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Dictation Review','e'); ?>    </span>
<span><?php echo "<br>" . xlt("Click Submit without changing dates to show all dictations"); ?></span>
<div id="report_parameters_daterange">
    <?php //echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>

</div>

<form name='theform' method='post' action='dictation_review.php' id='theform' onsubmit='return top.restoreSession()'>

    <div id="report_parameters">

        <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
        <input type='hidden' name='form_refresh1' id='form_refresh1' value=''/>

        <table>
            <tr>
                <td width='410px'>
                    <div style='float:left'>

                        <table class='text'>
                            <tr>
                                <td class='control-label'>
                                    <?php xl('From','e'); ?>:
                                </td>
                                <td class='control-label'>
                                    <input type='text' name='form_from_date' id="form_from_date"
                                           class='datepicker form-control'
                                           size='10' value='<?php echo attr($from_date) ?>'
                                           title='yyyy-mm-dd'>
                                </td>
                                <td class='control-label'>
                                    <?php xl('To','e'); ?>:
                                </td>
                                <td>
                                    <input type='text' name='form_to_date' id="form_to_date"
                                           class='datepicker form-control'
                                           size='10' value='<?php echo attr($to_date) ?>'
                                           title='yyyy-mm-dd'>
                                </td>
                            </tr>
                        </table>

                    </div>

                </td>
                <td align='left' valign='middle' height="100%">
                    <table style='border-left:1px solid; width:100%; height:100%' >
                        <tr>
                            <td>
                                <div class="text-center">
                                    <div class="btn-group" role="group">
                                    <a href='#' class='btn btn-default btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php xl('Submit','e'); ?>
					</span>
                                    </a>

                                    <?php if ($_POST['form_refresh']) { ?>
                                        <a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
                                        </a>
                                        <a href='#' class='css_button' onclick='$("#form_refresh1").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php xl('Save','e'); ?>
					</span>
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
    /**
     * This is to record to the database with the posted information from the Save button being clicked.
     */
    if ($_POST['form_refresh1'] == true){


        foreach ($_POST as $key => $val){        //note: next steps are to integrate this with the function below to complete the save
            if (preg_match("/^signed/", $key)){
                echo $val ."<br>";
            }
        }

        /**
         *  This loop is for the signing of the note.
         *
         */
        foreach($_POST as $key => $val){

            //This statement is to find the signed key which is the check box on the form.
            if (preg_match("/^signed/", $key)){

                $sig = $val;

                $row = explode("*", $sig);  //Need to breake up the serialized data


                $data = date("Y-%m-%d");  //$row[2]; we can use row 2 or the real date it was signed.
                $id = 8;
                $tid = $row[1];     //
                $tsql = "SELECT id FROM forms WHERE form_id = ? AND formdir = 'dictation'";
                $get_tid = sqlQuery($tsql, array($tid));
                $tid = $get_tid['id'];
                $date = sha1($data);
                $doc_id  = sha1($id);

                $sql = "INSERT INTO `esign_signatures` ( `tid`, `table`, `uid`, `datetime`, `is_lock`, `hash`, `amendment`, `signature_hash` ) ";

                $sql .= "VALUES ( ?, ?, ?, ?, ?, ?, ?, ? ) ";

                $signiture = array($tid, 'forms', $id, $data, '', $date, '',$doc_id );

                sqlInsert($sql, $signiture);

                $signoff = array($row[0], $row[1]);
                $soapform = "UPDATE form_dictation SET signed = '1' WHERE pid = ? AND id = ? ";
                sqlInsert($soapform, $signoff);

            }
        }

        //Want to exist here to halt operations after save was done.
        echo "Patient Records have been updated. ";
        exit;
    }

    if ($_POST['form_refresh'] || $_POST['form_orderby'] ) {

        ?>

      <div class="container-fluid" style="background-color:#c2dedd; padding: 15px;">
        <div class="table table-bordered col-md-12" id="report_results" style="background-color: rgba(230,224,205,0.89);">
            <div class="col-md-12" style="display: table-row;">
                <div class="col-md-1 cell">
                    <p> <?php xl('Record ID','e'); ?> </p>
                </div>
                <div class="col-md-2 cell">
                    <p> <?php xl('Date/Time of Service','e'); ?> </p>
                </div>
                <div class="col-md-1 cell">
                    <p> <?php xl('Patient','e'); ?> </p>
                </div>
                <div class="col-md-1 cell">
                    <p> <?php xl('DOB','e'); ?> </p>
                </div>
                <div class="col-md-4 cell">
                    <p> <?php xl('Review','e'); ?> </p>
                </div>
                <div class="col-md-1 cell">
                    <p> <?php xl('Update','e'); ?> </p>
                </div>
                <div class="col-md-1 cell">
                        <p> <?php xl('Sign Off','e'); ?> </p>
                </div>
            </div>
                <?php
                $query =  "SELECT n.id, n.date, n.pid, n.dictation, n.additional_notes, n.signed, p.fname, p.lname, p.DOB FROM form_dictation AS n LEFT OUTER JOIN patient_data AS p ON p.pid = n.pid";
                if($_POST['form_from_date'] < $_POST['form_to_date']){

                    $query.= " AND c.date >= ? AND c.date <= ?";

                    $dates = array($_POST['form_from_date'] .' 00:00:00', $_POST['form_to_date'].' 00:00:00');

                    $res = sqlStatement($query, $dates);
                } else {
                    $res = sqlStatement($query);
                }
                $i=0;
                $c=0;  //intialize page count.
                while ($row = sqlFetchArray($res)) {
                    $r = ++$i;
                    if ($row['signed'] != 0){    //skipped if marked signed
                        continue;
                    }


                    ?>
            <div >
            <div class="col-md-12" style="display: table-row;">
                        <div class="col-md-1 cell">
                            <?php   echo $row['id'] ?>
                        </div>
                        <div class="col-md-2 cell">
                            <?php	echo $row['date'] ?>
                        </div>
                        <div class="col-md-1 cell">
                            <?php   echo $row['lname'] . ', ' . $row['fname']      ?>
                        </div>
                        <div class="col-md-1 cell">
                            <?php	echo $row['DOB']; ?>
                        </div>
                        <div class="col-md-4 cell">
                                <p>
                                    <textarea cols="80" rows="4" name="<?php echo $row['id']?>" readonly ><?php echo $row['dictation'] ."\n\n Additional Notes:\n".$row['additional_notes'];?></textarea>
                                </p>
                        </div>
                        <div class="col-md-1 cell" style="vertical-align: middle">
                            <a href="./edit_dictation.php?id=<?php echo $row['id']; ?>" target="RBot" >Edit Note</a>
                        </div>
                        <div class="col-md-1 cell"">
                            <?php
                            //Display checkbox and hold the value for the patient id to be updated.
                            if ($row['signed'] == 0){
                                echo "<input type='checkbox' name='signed".$r."' value='".$row['pid']."*".$row['id']."*".$row['date']."'>";
                            } else {
                                echo "Note Signed";
                            }

                            ?>
                        </div>
            </div><!--end of rows-->
                    <?php
                    $c++;
                    if ($c == 25) {
                        break;   //Stop loop after reaching 25
                    } // Count conditional
                } //closing loop
                echo "<strong>". $c . " of </strong>";
                $sql = "SELECT COUNT(id) AS c FROM `form_dictation` WHERE signed != 1 ";
                $remainder = sqlQuery($sql);

                echo "<strong>".$remainder['c']."</strong> Remaining";
                ?>
        </div><!--end reports results-->
    </div>
  </div>
     <?php } else { ?>
        <div class='text'>
            <?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
        </div>
    <?php } ?>

</form>
</body>
</html>
