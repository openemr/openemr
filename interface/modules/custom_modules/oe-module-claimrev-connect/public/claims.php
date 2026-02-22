<?php

/**
 * Claims search page
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
use OpenEMR\Modules\ClaimRevConnector\ClaimsPage;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApiException;

$tab = "claims";
$errorMessage = null;

// Ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: ClaimRev Connect - Claims", xl("ClaimRev Connect - Claims"));
}
?>

<html>
    <head>
        <link rel="stylesheet" href="../../../../../public/assets/bootstrap/dist/css/bootstrap.min.css">
    </head>
    <title><?php echo xlt("ClaimRev Connect - Claims"); ?></title>
    <body>
        <div class="row">
            <div class="col">
            <?php
                require '../templates/navbar.php';
            ?>
            </div>
        </div>
        <form method="post" action="claims.php">
            <div class="card">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="startDate"><?php echo xlt("Send Date Start") ?></label>
                            <input type="date" class="form-control"  id="startDate" name="startDate" value="<?php echo isset($_POST['startDate']) ? attr($_POST['startDate']) : '' ?>"  placeholder="yyyy-mm-dd"/>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="endDate"><?php echo xlt("Send Date End"); ?></label>
                            <input type="date" class="form-control"  id="endDate" name="endDate" value="<?php echo isset($_POST['endDate']) ? attr($_POST['endDate']) : '' ?>" placeholder="yyyy-mm-dd"/>
                        </div>
                    </div>
                    <div class="col">

                    </div>
                    <div class="col">

                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="patFirstName"><?php echo xlt("Patient First Name") ?></label>
                            <input type="text" class="form-control"  id="patFirstName" name="patFirstName"  value="<?php echo isset($_POST['patFirstName']) ? attr($_POST['patFirstName']) : '' ?>"  placeholder="<?php echo xla("Patient First Name"); ?>"/>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="patLastName"><?php echo xlt("Patient Last Name") ?></label>
                            <input type="text" class="form-control"  id="patLastName" name="patLastName"  value="<?php echo isset($_POST['patLastName']) ? attr($_POST['patLastName']) : '' ?>" placeholder="<?php echo xla("Patient Last Name"); ?>"/>
                        </div>
                    </div>
                    <div class="col">

                    </div>
                    <div class="col">

                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button type="submit" name="SubmitButton" class="btn btn-primary"><?php echo xlt("Submit") ?></button>
                    </div>
                    <div class="col-10">

                    </div>
                </div>

            </div>
        </form>


        <?php
            $datas = [];
        if (isset($_POST['SubmitButton'])) {
            /** @var array<string, mixed> $_POST */
            try {
                $datas = ClaimsPage::searchClaims($_POST);
                if ($datas === null) {
                    $datas = [];
                }
            } catch (ClaimRevApiException) {
                $errorMessage = xlt('Failed to search claims. Please check your ClaimRev connection settings.');
                $datas = [];
            }
        }
        if ($errorMessage !== null) {
            echo '<div class="alert alert-danger">' . text($errorMessage) . '</div>';
        } elseif (empty($datas)) {
            echo xlt("No results found");
        } else { ?>
                <table class="table">
                <thead>
                    <tr>

                        <th scope="col"><?php echo xlt("Status") ?></th>
                        <th scope="col"><?php echo xlt("Payer Info") ?></th>
                        <th scope="col"><?php echo xlt("Provider Info") ?></th>
                        <th scope="col"><?php echo xlt("Patient Info") ?></th>
                        <th scope="col"><?php echo xlt("Claim Info") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($datas as $data) {
                        ?>
                        <tr>
                            <td>
                                <div class="row">
                                    <div class="col">
                                        <div class="row">
                                            <div class="font-weight-bold col">
                                            <?php echo xlt("ClaimRev Status"); ?>:
                                            </div>
                                        </div>
                                         <div class="row">
                                            <div class="col">
                                            <?php echo text($data->statusName); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="font-weight-bold col">
                                            <?php echo xlt("File Status"); ?>:
                                            </div>
                                        </div>
                                         <div class="row">
                                            <div class="col">
                                            <?php echo text($data->payerFileStatusName); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="row">
                                            <div class="font-weight-bold col">
                                            <?php echo xlt(" Payer Acceptance"); ?>:
                                            </div>
                                        </div>
                                         <div class="row">
                                            <div class="col">
                                            <?php echo text($data->payerAcceptanceStatusName); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="font-weight-bold col">
                                            <?php echo xlt("ERA"); ?>:
                                            </div>
                                        </div>
                                         <div class="row">
                                            <div class="col">
                                            <?php echo text($data->paymentAdviceStatusName); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Name"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->payerName); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Number"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->payerNumber); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Control #"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->payerControlNumber); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Name"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->providerFirstName); ?>  <?php echo text($data->providerLastName); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("NPI"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->providerNpi); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Name"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->pLastName); ?>, <?php echo text($data->pFirstName); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("DOB"); ?>:
                                    </div>
                                    <div class="col">
                                <?php echo text(substr((string) $data->birthDate, 0, 10)); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Gender"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->patientGender); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Member #"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->memberNumber); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Trace #"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->traceNumber); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Control #"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->payerControlNumber); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Billed Amt"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->billedAmount); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Paid Amt"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text($data->payerPaidAmount); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="font-weight-bold col">
                                    <?php echo xlt("Service Date"); ?>:
                                    </div>
                                    <div class="col">
                                    <?php echo text(substr((string) $data->serviceDate, 0, 10)); ?> / <?php echo text(substr((string) $data->serviceDateEnd, 0, 10)); ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php if ($data->errors) {
                            ?>
                        <tr>
                            <td colspan="6">
                                <ul>
                                <?php
                                foreach ($data->errors as $err) {
                                    ?>
                                        <li><?php echo text($err->errorMessage); ?></li>
                                    <?php
                                }
                                ?>
                                </ul>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php } ?>
                  </tbody>
                </table>
        <?php }
        ?>



    </body>
</html>
