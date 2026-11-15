<?php

/**
 * Forms administration page.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
require_once("../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\InfoTxt;
use OpenEMR\Core\OEGlobalsBag;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!AclMain::aclCheckCore('admin', 'forms')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for admin/forms: Forms Administration", xl("Forms Administration"));
}

// Mutating form registration (the enable/disable/install_db/register GET
// branches below + the POST metadata save at :87) must additionally require
// the `write` return mode. The gate above accepts any grant on admin/forms
// including read-only `view`; the stronger `write` mode matches the
// mutation intent and stays consistent with how the UI composes the two.
$formsRequest = CurrentRequest::get();
// Use getRealMethod() so a POST carrying `X-HTTP-METHOD-OVERRIDE: GET` can't
// trick `isMethod('POST')` into reporting GET while $_POST is still populated.
$isMutating = $formsRequest->query->getAlpha('method') !== '' || $formsRequest->getRealMethod() === 'POST';
if ($isMutating && !AclMain::aclCheckCore('admin', 'forms', '', ['write'])) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for admin/forms write: Forms Administration", xl("Forms Administration"));
}

if (!empty($_GET['method']) && ($_GET['method'] == "enable")) {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);
    updateRegistered($_GET['id'], "state=1");
} elseif (!empty($_GET['method']) && ($_GET['method'] == "disable")) {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);
    updateRegistered($_GET['id'], "state=0");
} elseif (!empty($_GET['method']) && ($_GET['method'] == "install_db")) {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);
    $dir = getRegistryEntry($_GET['id'], "directory");
    if (installSQL("$srcdir/../interface/forms/{$dir['directory']}")) {
        updateRegistered($_GET['id'], "sql_run=1");
    } else {
        $err = xl('ERROR: could not open table.sql, broken form?');
    }
} elseif (!empty($_GET['method']) && ($_GET['method'] == "register")) {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);
    $newRegisteredFormId = registerForm($_GET['name']) or $err = xl('error while registering form!');
    if (empty($err)) {
        // below block of code will insert the patient portal template (if it has not yet already been added) if the
        //  form is patient portal compliant
        CoreFormToPortalUtility::insertPatientPortalTemplate($newRegisteredFormId);
    }
}

$bigdata = getRegistered("%") or $bigdata = false;

//START OUT OUR PAGE....
?>

<html>
<head>
<?php Header::setupHeader(); ?>
</head>
<body class="body_top">

    <div class="container-fluid">
        <!-- Page header -->
        <div class="row">
            <div class="col-12">
                <h2 class="title"><?php echo xlt('Forms Administration');?></h2>
            </div>
        </div>
        <!-- Form table -->
        <div class="row">
           <div class="col-12 mt-3">
           <?php
            if (!empty($_POST)) {
                CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
                foreach ($_POST as $key => $val) {
                    if (preg_match('/nickname_(\d+)/', (string) $key, $matches)) {
                        sqlQuery("update registry set nickname = ? where id = ?", [$val, $matches[1]]);
                    } elseif (preg_match('/category_(\d+)/', (string) $key, $matches)) {
                        sqlQuery("update registry set category = ? where id = ?", [$val, $matches[1]]);
                    } elseif (preg_match('/priority_(\d+)/', (string) $key, $matches)) {
                        sqlQuery("update registry set priority = ? where id = ?", [$val, $matches[1]]);
                    } elseif (preg_match('/aco_spec_(\d+)/', (string) $key, $matches)) {
                        sqlQuery("update registry set aco_spec = ? where id = ?", [$val, $matches[1]]);
                    }
                }
            }
            ?>

            <?php //ERROR REPORTING
            if (!empty($err)) {
                echo "<span>" . text($err) . "</span>\n";
            }
            ?>

            <?php //REGISTERED SECTION ?>
            <span class="font-weight-bold"><?php echo xlt('Registered');?></span>
            <form method="post" action ='./forms_admin.php'>
                <span class="font-italic">
                    <?php echo xlt('click here to update priority, category, nickname and access control settings'); ?>
                </span>
                <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
                <input class="btn btn-primary" type='submit' name='update' value='<?php echo xla('Save'); ?>'>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-sm">
                      <thead>
                        <tr>
                            <th colspan="5"></th>
                            <th><?php echo xlt('Priority'); ?> </th>
                            <th><?php echo xlt('Category'); ?> </th>
                            <th><?php echo xlt('Nickname'); ?> </th>
                            <th><?php echo xlt('Access Control'); ?></th>
                        </tr>
                      </thead>
                        <?php
                        if ($bigdata != false) {
                            foreach ($bigdata as $registry) {
                                $priority_category = sqlQuery(
                                    "select priority, category, nickname, aco_spec from registry where id = ?",
                                    [$registry['id']]
                                );
                                $patientPortalCompliant = file_exists(OEGlobalsBag::getInstance()->getSrcDir() . "/../interface/forms/" . $registry['directory'] . "/patient_portal.php");
                                ?>
                            <tr>
                                <td>
                                    <span class='text'><?php echo text($registry['id']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    echo text(xl_form_title($registry['name']));
                                    echo ($patientPortalCompliant) ? ' <i class="fas fa-cloud-arrow-up" title="' . xla('Patient Portal Compliant') . '"></i>' : '';
                                    ?>
                                </td>
                                <?php
                                if ($registry['sql_run'] == 0) {
                                    echo "<td><span class='text'>" . xlt('registered') . "</span>";
                                } elseif ($registry['state'] == "0") {
                                    echo "<td><a class='link_submit text-danger' href='./forms_admin.php?id=" . attr_url($registry['id']) . "&method=enable&csrf_token_form=" . CsrfUtils::collectCsrfToken(session: $session) . "'>" . xlt('disabled') . "</a>";
                                } else {
                                    echo "<td><a class='link_submit text-success' href='./forms_admin.php?id=" . attr_url($registry['id']) . "&method=disable&csrf_token_form=" . CsrfUtils::collectCsrfToken(session: $session) . "'>" . xlt('enabled') . "</a>";
                                }
                                ?>
                                </td>
                                <td>
                                    <span class='text'><?php
                                    if ($registry['unpackaged']) {
                                        echo xlt('PHP extracted');
                                    } else {
                                        echo xlt('PHP compressed');
                                    }
                                    ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    if ($registry['sql_run']) {
                                        echo "<span class='text'>" . xlt('DB installed') . "</span>";
                                    } else {
                                        echo "<a class='link_submit' href='./forms_admin.php?id=" . attr_url($registry['id']) . "&method=install_db&csrf_token_form=" . CsrfUtils::collectCsrfToken(session: $session) . "'>" . xlt('install DB') . "</a>";
                                    }
                                    ?>
                                </td>
                                <?php
                                echo "<td><input type='text' class='form-control form-control-sm' size='4'  name='priority_" . attr($registry['id']) . "' value='" . attr($priority_category['priority']) . "'></td>";
                                echo "<td><input type='text' class='form-control form-control-sm' size='10' name='category_" . attr($registry['id']) . "' value='" . attr($priority_category['category']) . "'></td>";
                                echo "<td><input type='text' class='form-control form-control-sm' size='10' name='nickname_" . attr($registry['id']) . "' value='" . attr($priority_category['nickname']) . "'></td>";
                                echo "<td>";
                                echo "<select name='aco_spec_" . attr($registry['id']) . "' class='form-control form-control-sm'>";
                                echo "<option value=''></option>";
                                echo AclExtended::genAcoHtmlOptions($priority_category['aco_spec']);
                                echo "</select>";
                                echo "</td>";
                                ?>
                            </tr>
                                <?php
                            } //end of foreach
                        }
                        ?>
                    </table>
                </div>
                <hr>

                <?php  //UNREGISTERED SECTION ?>
                <span class="font-weight-bold"><?php echo xlt('Unregistered'); ?></span>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-sm">
                        <?php
                        $dpath = "$srcdir/../interface/forms/";
                        $dp = opendir($dpath);

                        for ($i = 0; false != ($fname = readdir($dp)); $i++) {
                            if (
                                !in_array($fname, [".", "..", "CVS", "LBF"]) &&
                                (is_dir($dpath . $fname) || stristr($fname, ".tar.gz") ||
                                stristr($fname, ".tar") || stristr($fname, ".zip") ||
                                stristr($fname, ".gz"))
                            ) {
                                $inDir[$i] = $fname;
                            }
                        }

                        // ballards 11/05/2005 fixed bug in removing registered form from the list
                        if ($bigdata != false) {
                            foreach ($bigdata as $registry) {
                                $key = array_search($registry['directory'], $inDir) ;  /* returns integer or FALSE */
                                unset($inDir[$key]);
                            }
                        }

                        foreach ($inDir as $fname) {
                            if (stristr((string) $fname, ".tar.gz") || stristr((string) $fname, ".tar") || stristr((string) $fname, ".zip") || stristr((string) $fname, ".gz")) {
                                $phpState = "PHP compressed";
                            } else {
                                $phpState =  "PHP extracted";
                            }
                            ?>
                            <tr>
                                <td colspan="2">
                                    <?php
                                    $form_title = InfoTxt::read(OEGlobalsBag::getInstance()->getSrcDir() . "/../interface/forms/$fname/info.txt")->name ?? $fname;
                                    $patientPortalCompliant = file_exists(OEGlobalsBag::getInstance()->getSrcDir() . "/../interface/forms/" . $fname . "/patient_portal.php");
                                    ?>
                                    <?php
                                    echo text(xl_form_title($form_title));
                                    echo ($patientPortalCompliant) ? ' <i class="fas fa-cloud-arrow-up" title="' . xla('Patient Portal Compliant') . '"></i>' : '';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($phpState == "PHP extracted") {
                                        echo '<a class="link_submit" href="./forms_admin.php?name=' . attr_url($fname) . '&method=register&csrf_token_form=' . CsrfUtils::collectCsrfToken(session: $session) . '">' . xlt('register') . '</a>';
                                    } else {
                                        echo '<span class="text">' . xlt('n/a') . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="text"><?php echo xlt($phpState); ?></span>
                                </td>
                                <td>
                                    <span class="text"><?php echo xlt('n/a'); ?></span>
                                </td>
                            </tr>
                            <?php
                            flush();
                        }//end of foreach
                        ?>
                    </table>
                </div>
            </form>
           </div>
        </div>
    </div>
</body>
</html>
