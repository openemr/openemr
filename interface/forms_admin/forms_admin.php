<?php

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.




//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
require_once("../globals.php");
require_once("$srcdir/registry.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'forms')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Forms Administration")]);
    exit;
}

if (!empty($_GET['method']) && ($_GET['method'] == "enable")) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    updateRegistered($_GET['id'], "state=1");
} elseif (!empty($_GET['method']) && ($_GET['method'] == "disable")) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    updateRegistered($_GET['id'], "state=0");
} elseif (!empty($_GET['method']) && ($_GET['method'] == "install_db")) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $dir = getRegistryEntry($_GET['id'], "directory");
    if (installSQL("$srcdir/../interface/forms/{$dir['directory']}")) {
        updateRegistered($_GET['id'], "sql_run=1");
    } else {
        $err = xl('ERROR: could not open table.sql, broken form?');
    }
} elseif (!empty($_GET['method']) && ($_GET['method'] == "register")) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
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
                if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
                    CsrfUtils::csrfNotVerified();
                }
                foreach ($_POST as $key => $val) {
                    if (preg_match('/nickname_(\d+)/', $key, $matches)) {
                        sqlQuery("update registry set nickname = ? where id = ?", array($val, $matches[1]));
                    } elseif (preg_match('/category_(\d+)/', $key, $matches)) {
                        sqlQuery("update registry set category = ? where id = ?", array($val, $matches[1]));
                    } elseif (preg_match('/priority_(\d+)/', $key, $matches)) {
                        sqlQuery("update registry set priority = ? where id = ?", array($val, $matches[1]));
                    } elseif (preg_match('/aco_spec_(\d+)/', $key, $matches)) {
                        sqlQuery("update registry set aco_spec = ? where id = ?", array($val, $matches[1]));
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
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
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
                                    array($registry['id'])
                                );
                                $patientPortalCompliant = file_exists($GLOBALS['srcdir'] . "/../interface/forms/" . $registry['directory'] . "/patient_portal.php");
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
                                    echo "<td><a class='link_submit text-danger' href='./forms_admin.php?id=" . attr_url($registry['id']) . "&method=enable&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . xlt('disabled') . "</a>";
                                } else {
                                    echo "<td><a class='link_submit text-success' href='./forms_admin.php?id=" . attr_url($registry['id']) . "&method=disable&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . xlt('enabled') . "</a>";
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
                                        echo "<a class='link_submit' href='./forms_admin.php?id=" . attr_url($registry['id']) . "&method=install_db&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "'>" . xlt('install DB') . "</a>";
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
                                $fname != "." && $fname != ".." && $fname != "CVS" && $fname != "LBF" &&
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
                            if (stristr($fname, ".tar.gz") || stristr($fname, ".tar") || stristr($fname, ".zip") || stristr($fname, ".gz")) {
                                $phpState = "PHP compressed";
                            } else {
                                $phpState =  "PHP extracted";
                            }
                            ?>
                            <tr>
                                <td colspan="2">
                                    <?php
                                    $form_title_file = @file($GLOBALS['srcdir'] . "/../interface/forms/$fname/info.txt");
                                    if ($form_title_file) {
                                            $form_title = $form_title_file[0];
                                    } else {
                                        $form_title = $fname;
                                    }
                                    $patientPortalCompliant = file_exists($GLOBALS['srcdir'] . "/../interface/forms/" . $fname . "/patient_portal.php");
                                    ?>
                                    <?php
                                    echo text(xl_form_title($form_title));
                                    echo ($patientPortalCompliant) ? ' <i class="fas fa-cloud-arrow-up" title="' . xla('Patient Portal Compliant') . '"></i>' : '';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($phpState == "PHP extracted") {
                                        echo '<a class="link_submit" href="./forms_admin.php?name=' . attr_url($fname) . '&method=register&csrf_token_form=' . attr_url(CsrfUtils::collectCsrfToken()) . '">' . xlt('register') . '</a>';
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
