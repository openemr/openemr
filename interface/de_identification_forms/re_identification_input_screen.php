<?php

/**
 * re_identification_input_screen.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Re Identification")]);
    exit;
}

?>
<html>
<head>
<title><?php echo xlt('Re Identification'); ?></title>

    <?php Header::setupHeader(); ?>

<style>
.style1 {
    text-align: center;
}
</style>
<script>
function form_validate()
{

 if(document.forms[0].re_id_code.value == "undefined" || document.forms[0].re_id_code.value == "")
 {
  alert(<?php echo xlj('Enter the Re Identification code'); ?>);
  return false;
 }
 top.restoreSession();
 return true;
}

function download_file()
{
 alert(<?php echo xlj('Re-identification files will be saved in'); ?> + ' `' + <?php echo js_escape($GLOBALS['temporary_files_dir']); ?> + '` ' + <?php echo xlj('location of the openemr machine and may contain sensitive data, so it is recommended to manually delete the files after its use'); ?>);
 document.re_identification.submit();
}

</script>
</head>
<body class="body_top">
<strong><?php echo xlt('Re Identification');  ?></strong>
<div id="overDiv"
    style="position: absolute; visibility: hidden; z-index: 1000;"></div>
<form name="re_identification" enctype="Re_identification_ip_single_code"
    action="re_identification_op_single_patient.php" method="POST" onsubmit="return form_validate();">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <?php
    $row = sqlQuery("SHOW TABLES LIKE 'de_identification_status'");
    if (empty($row)) {
        ?>
      <table>  <tr>    <td>&nbsp;</td> <td>&nbsp;</td> </tr>
          <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr>
    </table>
    <table class="de_identification_status_message" align="center" >
    <tr valign="top">
        <td>&nbsp;</td>
        <td rowspan="3">
        <br />
        <?php echo xlt('Please upgrade OpenEMR Database to include De Identification procedures, function, tables'); ?>
       <br /><br /><a  target="Blank" href="../../contrib/util/de_identification_upgrade.php"><?php echo xlt('Click here');?></a>
        <?php echo xlt('to run');
        echo " de_identification_upgrade.php<br />";?><br />
           </td>
           <td>&nbsp;</td>
       </tr>
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>
       </table>
        <?php
    } else {
          $query = "select status from re_identification_status";
          $res = sqlStatement($query);
        if ($row = sqlFetchArray($res)) {
            $reIdentificationStatus = $row['status'];
           /* $reIdentificationStatus:
        *  0 - There is no Re Identification in progress. (start new Re Identification process)
        *  1 - A Re Identification process is currently in progress.
        *  2 - The Re Identification process completed and xls file is ready to download
           */
        }

        if ($reIdentificationStatus == 1) {
            //1 - A Re Identification process is currently in progress
            ?>
        <table>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        </table>
        <table class="de_identification_status_message" align="center">
        <tr valign="top">
            <td>&nbsp;</td>
            <td rowspan="3"><br />
                <?php echo xlt('Re Identification Process is ongoing');
                echo "<br /><br />";
                echo xlt('Please visit Re Identification screen after some time');
                echo "<br />";   ?> <br />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        </table>
                <?php
        } elseif ($reIdentificationStatus == 0) {
           //0 - There is no Re Identification in progress. (start new Re Identification process)
            ?>
        <center><br />
        <br />
                <?php echo xlt('Enter the Re Identification code'); ?> <input
        type='text' size='50' name='re_id_code' id='re_id_code'
           title='<?php echo xla('Enter the Re Identification code'); ?>' /> <br />
        <br />
           <Input type="Submit" Name="Submit" Value=<?php echo xla("submit");?>></center>
            <?php
        } elseif ($reIdentificationStatus == 2) {
            //2 - The Re Identification process completed and xls file is ready to download
             $query = "SELECT count(*) as count FROM re_identified_data ";
             $res = sqlStatement($query);
            if ($row = sqlFetchArray($res)) {
                $no_of_items = $row['count'];
            }

            if ($no_of_items <= 1) {
                //start new search - no patient record fount
                $query = "update re_identification_status set status = 0";
                $res = sqlStatement($query);
                ?>
         <table>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         </table>
         <table class="de_identification_status_message" align="center">
         <tr valign="top">
             <td>&nbsp;</td>
             <td rowspan="3"><br />
                <?php echo xlt('No Patient record found for the given Re Identification code');
                echo "<br /><br />";
                echo xlt('Please enter the correct Re Identification code');
                echo "<br />";   ?> <br />
             </td>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         </table>
         <table align="center">
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         </table>

                <?php
            } else {
                ?>
             <table>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         </table>
         <table class="de_identification_status_message"" align="center">
         <tr valign="top">
             <td>&nbsp;</td>
             <td rowspan="3"><br />
                <?php echo xlt('Re Identification Process is completed');
                echo "<br /><br />";
                echo xlt('Please Click download button to download the Re Identified data');
                echo "<br />";   ?> <br />
             </td>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         </table>
         <table align="center">
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td colspan="2" class="style1"><input type="button" name="Download"
                    value=<?php echo xla("Download"); ?> onclick="download_file()" ></td>
         </tr>
         </table>
                <?php
            }
        }
    }

    ?>
</form>
</body>
</html>

