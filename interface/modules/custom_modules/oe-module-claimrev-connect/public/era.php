<?php

/**
 * ERA search page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApiException;
use OpenEMR\Modules\ClaimRevConnector\EraPage;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

$tab = "eras";
$selected = " selected ";
$datas = [];
$errorMessage = null;

// Ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: ClaimRev Connect - ERAs", xl("ClaimRev Connect - ERAs"));
}

$startDate = ModuleInput::postString('startDate');
$endDate = ModuleInput::postString('endDate');
$dlStatus = ModuleInput::postInt('downloadStatus', 2);
$searchPayload = [
    'startDate' => $startDate,
    'endDate' => $endDate,
    'downloadStatus' => $dlStatus,
];

if (ModuleInput::isPostRequest() && ModuleInput::postExists('SubmitButton')) {
    try {
        $datas = EraPage::searchEras($searchPayload);
        if ($datas === null) {
            $datas = [];
        }
    } catch (ClaimRevApiException) {
        $errorMessage = xlt('Failed to search ERAs. Please check your ClaimRev connection settings.');
        $datas = [];
    }
}
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - ERAs"); ?></title>
        <?php Header::setupHeader(['common', 'opener']); ?>
        <script>
            function downloadEra(objectId) {
                window.location = 'EraDownload.php?eraId=' + objectId;
            }
        </script>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <form method="post" action="era.php">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="startDate"><?php echo xlt("Receive Date Start") ?>:</label>
                                    <input type="date" class="form-control"  id="startDate" name="startDate" value="<?php echo attr($startDate); ?>"  placeholder="yyyy-mm-dd"/>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="endDate"><?php echo xlt("Receive Date End"); ?>:</label>
                                    <input type="date" class="form-control"  id="endDate" name="endDate" value="<?php echo attr($endDate); ?>" placeholder="yyyy-mm-dd"/>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="downloadStatus"><?php echo xlt("Download Status") ?>:</label>
                                    <select name="downloadStatus" id="downloadStatus"  class="form-control">
                                        <option value="2" <?php echo $dlStatus === 2 ? $selected : ''; ?>><?php echo xlt("Waiting") ?></option>
                                        <option value="3" <?php echo $dlStatus === 3 ? $selected : ''; ?>><?php echo xlt("Downloaded") ?></option>
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
                </div>
            </form>
            <?php

            if ($errorMessage !== null) {
                echo '<div class="alert alert-danger mt-3">' . text($errorMessage) . '</div>';
            } elseif ($datas === []) {
                echo "<div class='mt-3'>" . xlt("No results found") . "</div>";
            } else { ?>
                <table class="table table-striped mt-3">
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
                        $eraStr = static function (\stdClass $o, string $prop): string {
                            if (!property_exists($o, $prop)) {
                                return '';
                            }
                            $v = $o->$prop;
                            if (is_string($v)) {
                                return $v;
                            }
                            if (is_int($v) || is_float($v)) {
                                return (string) $v;
                            }
                            return '';
                        };
                foreach ($datas as $data) {
                    ?>
                            <tr>
                                <td scope="row"><?php echo text(substr($eraStr($data, 'receivedDate'), 0, 10)); ?></td>
                                <td scope="row"><?php echo text($eraStr($data, 'payerName')); ?></td>
                                <td scope="row"><?php echo text($eraStr($data, 'payerNumber')); ?></td>
                                <td scope="row"><?php echo text($eraStr($data, 'billedAmt')); ?></td>
                                <td scope="row"><?php echo text($eraStr($data, 'payerPaidAmt')); ?></td>
                                <td scope="row"><?php echo text($eraStr($data, 'patientResponsibility')); ?></td>
                                <td scope="row">
                                    <button type="button" onClick="downloadEra('<?php echo attr($eraStr($data, 'id')); ?>');" name="downloadFile" class="btn btn-primary">
                                        <?php echo xlt("Download ERA"); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php }  ?>
        </div>
    </body>
</html>
