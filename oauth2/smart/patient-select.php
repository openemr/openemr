<?php
/**
 * patient-select.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\Header;

if ($oauthLogin !== true) {
    $message = xlt("Error. Not authorized");
    SessionUtil::oauthSessionCookieDestroy();
    echo $message;
    exit();
}

// make sure we have our patients set
$errorMessage = $errorMessage ?? "";
$patients = $patients ?? [];
$redirect = $redirect ?? "";
$searchAction = $searchAction ?? "";
$fname = $searchParams['fname'] ?? "";
$mname = $searchParams['mname'] ?? "";
$lname = $searchParams['lname'] ?? "";
$hasMore = $hasMore ?? false;

?>
<html>
<head>
    <title><?php echo xlt("OpenEMR Authorization"); ?></title>
    <?php Header::setupHeader('opener'); ?>
    <script>
        (function(window) {
            window.opener = null;
        })(window || {});
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
    </script>
</head>
<body class="container-fluid bg-dark">
<div class="row h-100 w-100 justify-content-center align-items-center">
    <div class="col-sm-6 bg-light text-dark">
        <div class="text-md-center">
            <h4 class="mb-4 mt-1"><?php echo xlt("Patient Selection"); ?></h4>
        </div>
        <div class="row w-100">
            <div class="col">
                <?php if (!empty($errorMessage)) : ?>
                    <p class="alert alert-warning"><?php echo xlt($errorMessage); ?></p>
                <?php endif; ?>

                <?php if (count($patients) < 0) : ?>
                    <p class="alert alert-info"><?php echo xlt("No patients to select"); ?></p>
                <?php else : ?>

                <form action="<?php echo $searchAction; ?>" method="GET">
                    <input class="w-25" name="search[fname]" type="text" class="form-control form-input" placeholder="<?php echo xlt("First Name"); ?>"
                           value="<?php echo attr($fname); ?>" />
                    <input class="w-25" name="search[mname]" type="text" class="form-control form-input" placeholder="<?php echo xlt("Middle Name"); ?>"
                           value="<?php echo attr($mname); ?>" />
                    <input class="w-25" name="search[lname]" type="text" class="form-control form-input" placeholder="<?php echo xlt("Last Name"); ?>"
                           value="<?php echo attr($lname); ?>" />
                    <input type="submit" value="Search" />
                </form>
                <?php if ($hasMore) : ?>
                <p class="alert alert-info"><?php echo xlt("Too many search results found.  Displaying a limited set of patients.  Narrow your search results through the filters above."); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row w-100">
            <p class="col">

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo xlt("Name"); ?></th>
                            <th><?php echo xlt("DOB"); ?></th>
                            <th><?php echo xlt("Sex"); ?></th>
                            <th><?php echo xlt("Email"); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient) : ?>
                        <tr>
                            <td>
                                <?php if ($patient['mname']) : ?>
                                <?php echo sprintf("%s %s %s", $patient['fname'], $patient['mname'], $patient['lname']); ?>
                                <?php else : ?>
                                    <?php echo sprintf("%s %s", $patient['fname'], $patient['lname']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $patient['DOB']; ?>
                            </td>
                            <td>
                                <?php echo $patient['sex']; ?>
                            </td>
                            <td>
                                <?php echo $patient['email']; ?>
                            </td>
                            <td>
                                <button data-patient-id="<?php echo $patient['uuid']; ?>" class="btn btn-primary patient-btn">Select patient</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <form method="post" name="patientForm" id="patientForm" action="<?php echo $redirect ?>">
            <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken('oauth2')); ?>" />
            <input id="patient_id" type="hidden" name="patient_id" value="" />
            <hr />
<!--            <div class="row">-->
<!--                <div class="col-md-12">-->
<!--                    <div class="btn-group">-->
<!--                        <button type="submit" name="proceed" value="1" class="btn btn-primary">--><?php //echo xlt("Complete Authorization"); ?><!--</button>-->
<!--                    </div>-->
<!--                    <div class="form-check-inline float-right">-->
<!--                        <input class="form-check-input" type="checkbox" name="persist_login" id="persist_login" value="1">-->
<!--                        <label for="persist_login" class="form-check-label">--><?php //echo xlt("Remember Me"); ?><!--</label>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
        </form>
    </div>
</div>
<script>
    (function(window) {

        function choosePatient(evt) {
            var target = evt.target;
            var patientId = target.dataset.patientId || undefined;
            if (!patientId) {
                console.error("<?php echo xlt("Developer error. Patient id is missing from dataset");?>)");
                return;
            }
            var patientInput = document.getElementById('patient_id');
            if (!patientInput) {
                console.error("<?php echo xlt("Developer error missing hidden form element 'selectedPatient'");?>)");
                return;
            }
            patientInput.value = patientId;

            // now submit our form.
            let form = document.getElementById('patientForm');
            if (!form) {
                console.error("<?php echo xlt("Developer error missing form 'patientForm'");?>)");
                return;
            }
            form.submit();
        }

        function setup() {
            var i;
            var btns = document.querySelectorAll(".patient-btn");
            // eventually browsers will support the foreach.. otherwise let's loop
            for (i = 0; i < btns.length; i++) {
                btns[i].addEventListener('click', choosePatient);
            }
        }
        window.addEventListener('load', setup);
    })(window)
</script>
</body>
</html>