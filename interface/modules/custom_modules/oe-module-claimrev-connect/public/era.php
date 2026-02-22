<?php

/**
 * ERA search page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\ClaimRevConnector\EraPage;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApiException;

$tab = "eras";
$selected = " selected ";
$datas = [];
$errorMessage = null;

// Ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: ClaimRev Connect - ERAs", xl("ClaimRev Connect - ERAs"));
}

$dlStatus = 2;
if (!empty($_POST)) {
    $dlStatus = $_POST['downloadStatus'];

    if (isset($_POST['SubmitButton'])) {
        try {
            $datas = EraPage::searchEras($_POST);
            if ($datas === null) {
                $datas = [];
            }
        } catch (ClaimRevApiException) {
            $errorMessage = xlt('Failed to search ERAs. Please check your ClaimRev connection settings.');
            $datas = [];
        }
    }
}
?>

<html>
    <head>
        <?php Header::setupHeader(['common', 'opener']); ?>
        <link rel="stylesheet" href="../../../../../public/assets/bootstrap/dist/css/bootstrap.min.css">
        <script>
            function downloadEra(objectId) {
                window.location = 'EraDownload.php?eraId=' + objectId;

            }
        </script>
    </head>
    <title><?php echo xlt("ClaimRev Connect - ERAs"); ?></title>
    <body>
        <div class="row">
            <div class="col">
            <?php
                require '../templates/navbar.php';
            ?>
            </div>
        </div>
        <form method="post" action="era.php">
            <div class="card">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="startDate"><?php echo xlt("Receive Date Start") ?>:</label>
                            <input type="date" class="form-control"  id="startDate" name="startDate" value="<?php echo isset($_POST['startDate']) ? attr($_POST['startDate']) : '' ?>"  placeholder="yyyy-mm-dd"/>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="endDate"><?php echo xlt("Receive Date End"); ?>:</label>
                            <input type="date" class="form-control"  id="endDate" name="endDate" value="<?php echo isset($_POST['endDate']) ? attr($_POST['endDate']) : '' ?>" placeholder="yyyy-mm-dd"/>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="downloadStatus"><?php echo xlt("Download Status") ?>:</label>
                            <select name="downloadStatus" id="downloadStatus"  class="form-control">
                                <option value=2 <?php echo ($dlStatus == 2) ? $selected : ''; ?> ><?php echo xlt("Waiting") ?></option>
                                <option value=3 <?php echo ($dlStatus == 3) ? $selected : ''; ?>><?php echo xlt("Downloaded") ?></option>
                        </select>
                        </div>

                    </div>
                    <div class="col"></div>
                </div>
                <div class="row">
                    <div class="col">
                        <button type="submit" name="SubmitButton" class="btn btn-primary"><?php echo xlt("Submit") ?></button>
                    </div>
                </div>
            </div>
        </form>
        <?php

        if ($errorMessage !== null) {
            echo '<div class="alert alert-danger">' . text($errorMessage) . '</div>';
        } elseif (empty($datas)) {
            echo xlt("No results found");
        } else { ?>
               <table class="table">
                <thead>
                    <tr>

                        <th scope="col"><?php echo xlt("Date") ?></th>
                        <th scope="col"><?php echo xlt("Payer Name") ?></th>
                        <th scope="col"><?php echo xlt("Payer Number") ?></th>
                        <th scope="col"><?php echo xlt("Billed Amt") ?></th>
                        <th scope="col"><?php echo xlt("Payer Paid Amt") ?></th>
                        <th scope="col"><?php echo xlt("Patient Responsibility") ?></th>
                        <th scope="col"><?php echo xlt("Actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($datas as $data) {
                        ?>
                        <tr>
                            <td scope="row"><?php echo text(substr((string) $data->receivedDate, 0, 10)); ?></td>
                            <td scope="row"><?php echo text($data->payerName); ?></td>
                            <td scope="row"><?php echo text($data->payerNumber); ?></td>
                            <td scope="row"><?php echo text($data->billedAmt); ?></td>
                            <td scope="row"><?php echo text($data->payerPaidAmt); ?></td>
                            <td scope="row"><?php echo text($data->patientResponsibility); ?></td>
                            <td scope="row">
                                <button type="button" onClick="downloadEra('<?php echo attr($data->id); ?>');" name="downloadFile" class="btn btn-primary">
                                    <?php echo xlt("Download ERA"); ?>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                </table>
        <?php }  ?>
    </body>
</html>
