<?php
/**
 * Functions to globally validate and prepare data for sql database insertion.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MMF Systems, Inc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 MMF Systems, Inc
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__file__)."/../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/edi.inc");

if (!empty($_POST)) {
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }
}

//  File location (URL or server path)
$target         = $GLOBALS['edi_271_file_path'];

if (isset($_FILES) && !empty($_FILES)) {
    $target     = $target .time().basename($_FILES['uploaded']['name']);

    $FilePath   = $target;

    if ($_FILES['uploaded']['size'] > 350000) {
        $message .=  xlt('Your file is too large')."<br>";
    }

    if ($_FILES['uploaded']['type']!="text/plain") {
        $message .= xlt('You may only upload .txt files')."<br>";
    }

    if (!isset($message)) {
        if (move_uploaded_file($_FILES['uploaded']['tmp_name'], $target)) {
            $message    = xlt('The following EDI file has been uploaded') . ': "' . text(basename($_FILES['uploaded']['name'])) . '"';

            // Stores the content of the file
            $Response271= file($FilePath);

            // Counts the number of lines
            $LineCount  = count($Lines);

            //This will be a two dimensional array
            //that holds the content nicely organized

            $DataSegment271 = array();
            $Segments271    = array();

            // We will use this as an index
            $i          =   0;
            $j          =   0;
            $patientId  = "";

            // Loop through each line
            foreach ($Response271 as $Value) {
               // In the array store this line
                // with values delimited by ^ (tilt)
                // as separate array values

                $DataSegment271[$i] = explode("^", $Value);


                if (count($DataSegment271[$i])<6) {
                        $messageEDI = true;
                        $message = "";
                    if (file_exists($target)) {
                        unlink($target);
                    }
                } else {
                    foreach ($DataSegment271[$i] as $datastrings) {
                        $Segments271[$j] = explode("~", $datastrings);

                        $segment         = $Segments271[$j][0];


                        // Switch Case for Segment

                        switch ($segment) {
                            case 'ISA':
                                $j = 0;

                                foreach ($Segments271[$j] as $segmentVal) {
                                    if ($j == 6) {
                                        $x12PartnerId = $segmentVal;
                                    }

                                    $j  =   $j + 1;
                                }
                                break;

                            case 'REF':
                                foreach ($Segments271[$j] as $segmentVal) {
                                    if ($segmentVal == "EJ") {
                                        $patientId = $Segments271[$j][2];
                                    }
                                }
                                break;

                            case 'EB':
                                foreach ($Segments271[$j] as $segmentVal) {
                                }
                                break;

                            case 'MSG':
                                foreach ($Segments271[$j] as $segmentVal) {
                                    if ($segment != $segmentVal) {
                                        eligibility_response_save($segmentVal, $x12PartnerId);

                                        eligibility_verification_save($segmentVal, $x12PartnerId, $patientId);
                                    }
                                }
                                break;
                        }



                       // Increase the line index
                        $j++;
                    }
                }

              //Increase the line index
                $i++;
            }
        }
    } else {
        $message .= xlt('Sorry, there was a problem uploading your file') . "<br><br>";
    }
}

?>
<html>
<head>
<?php html_header_show();?>
<title><?php echo xlt('EDI-271 Response File Upload'); ?></title>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">

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

</style>

<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-1-3-2/jquery.js"></script>

<script type="text/javascript">
        function edivalidation(){

            var mypcc = <?php echo xlj('Required Field Missing: Please choose the EDI-271 file to upload'); ?>;

            if(document.getElementById('uploaded').value == ""){
                alert(mypcc);
                return false;
            }
            else
            {
                $("#theform").submit();
            }

        }
</script>

</head>
<body class="body_top">

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
    <?php   if (isset($message) && !empty($message)) {
    ?>
                <div style="margin-left:25%;width:50%;color:RED;text-align:center;font-family:arial;font-size:15px;background:#ECECEC;border:1px solid;" ><?php echo $message; ?></div>
    <?php
                $message = "";
}

if (isset($messageEDI)) {
    ?>
    <div style="margin-left:25%;width:50%;color:RED;text-align:center;font-family:arial;font-size:15px;background:#ECECEC;border:1px solid;" >
<?php echo xlt('Please choose the proper formatted EDI-271 file'); ?>
    </div>
    <?php
    $messageEDI = "";
}
    ?>

<div>

<span class='title'><?php echo xlt('EDI-271 File Upload'); ?></span>

<form enctype="multipart/form-data" name="theform" id="theform" action="edi_271.php" method="POST" onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />

<div id="report_parameters">
    <table>
        <tr>
            <td width='550px'>
                <div style='float:left'>
                    <table class='text'>
                        <tr>
                            <td style='width:125px;' class='label_custom'> <?php echo xlt('Select EDI-271 file'); ?>:   </td>
                            <td> <input name="uploaded" id="uploaded" type="file" size=37 /></td>
                        </tr>
                    </table>
                </div>
            </td>
            <td align='left' valign='middle' height="100%">
                <table style='border-left:1px solid; width:100%; height:100%' >
                    <tr>
                        <td>
                            <div style='margin-left:15px'>
                                <a href='#' class='css_button' onclick='return edivalidation(); '><span><?php echo xlt('Upload'); ?></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>


<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby); ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>
</html>
