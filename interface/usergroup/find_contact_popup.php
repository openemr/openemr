<?php
/**
 * The address book popup that allows you to select people.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    tony@mi-squared.com
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if($_GET) {
    $_SESSION['id'] = $_GET['id'];
}

$form_abook_type_notrim = '';

if ($_POST) {
    $form_fname = trim($_POST['form_fname']);
    $form_lname = trim($_POST['form_lname']);
    $form_specialty = trim($_POST['form_specialty']);
    $form_organization = trim($_POST['form_organization']);
    $form_abook_type = trim($_REQUEST['form_abook_type']);
    $form_abook_type_notrim = $_REQUEST['form_abook_type'];

    $sqlBindArray = array();
    $query = "SELECT u.*, lo.option_id AS ab_name, lo.option_value as ab_option FROM addressbook AS u " .
      "LEFT JOIN list_options AS lo ON " .
      "(lo.list_id = 'abook_type' AND lo.option_id = u.abook_type AND lo.activity = 1) WHERE id > 0 ";
    if ($form_organization) {
        $query .= "AND u.organization LIKE ? ";
        array_push($sqlBindArray, $form_organization."%");
    }

    if ($form_lname) {
        $query .= "AND u.lname LIKE ? ";
        array_push($sqlBindArray, $form_lname."%");
    }

    if ($form_fname) {
        $query .= "AND u.fname LIKE ? ";
        array_push($sqlBindArray, $form_fname."%");
    }

    if ($form_specialty) {
        $query .= "AND u.specialty LIKE ? ";
        array_push($sqlBindArray, "%".$form_specialty."%");
    }

    if ($form_abook_type) {
        $query .= "AND u.abook_type LIKE ? ";
        array_push($sqlBindArray, $form_abook_type);
    }

    if ($form_lname) {
        $query .= "ORDER BY u.lname, u.fname, u.mname";
    } elseif ($form_organization) {
        $query .= "ORDER BY u.organization";
    } else {
        $query .= "ORDER BY u.organization, u.lname, u.fname";
    }

    $query .= " LIMIT 500";
    $res = sqlStatement($query, $sqlBindArray);
}
?>

<!DOCTYPE html>
<html>

<head>

    <?php Header::setupHeader(['common', 'opener']); ?>

    <title><?php echo xlt('Address Book'); ?></title>
</head>

<body class="body_top">
    <div class="container-fluid">
        <h3><?php echo xlt('Address Book'); ?></h3>
        <form method='post' action='find_contact_popup.php' onsubmit='return top.restoreSession()'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="hidden" name="id" value="<?php echo attr($_SESSION['id']); ?>" />

            <div class="row">
              <label for="form_organization" class="col-sm col-form-label"><?php echo xlt('Organization') ?>:</label>
              <div class="col-sm-3">
                <input type='text' class="form-control inputtext" name='form_organization' size='10' value='<?php echo attr($_POST['form_organization']); ?>' title='<?php echo xla("All or part of the organization") ?>'/>
              </div>
              <label for="form_fname" class="col-sm-3 col-form-label"><?php echo xlt('First Name') ?>:</label>
              <div class="col-sm-3">
                <input type='text' class="form-control inputtext" name='form_fname' size='10' value='<?php echo attr($_POST['form_fname']); ?>' title='<?php echo xla("All or part of the first name") ?>'/>
              </div>
              <label for="form_lname" class="col-sm-3 col-form-label"><?php echo xlt('Last Name') ?>:</label>
              <div class="col-sm-3">
                <input type='text' class="form-control inputtext" name='form_lname' size='10' value='<?php echo attr($_POST['form_lname']); ?>' title='<?php echo xla("All or part of the last name") ?>'/>
              </div>
              <label for="form_specialty" class="col-sm col-form-label"><?php echo xlt('Specialty') ?>:</label>
              <div class="col-sm-3">
                <input type='text' class="form-control inputtext" name='form_specialty' size='10' value='<?php echo attr($_POST['form_specialty']); ?>' title='<?php echo xla("Any part of the desired specialty") ?>'/>
              </div>
              <label for="abook_type" class="col-sm-3 col-form-label"><?php echo xlt('Type'); ?>:</label>
              <div class="col-sm-3">
                <?php
                // Generates a select list named form_abook_type:
                echo generate_select_list("form_abook_type", "abook_type", $_REQUEST['form_abook_type'], '', 'All');
                ?>
              </div>
              <div class="col-sm-3 ml-auto">
                <input type='submit' class='btn btn-primary btn-search' title='<?php echo xla("Use % alone in a field to just sort on that column") ?>' name='form_search' value='<?php echo xla("Search") ?>' />
            </div>
            </div>
        </form>
        <?php if ($_POST) { ?>
        <div class="table-responsive">
            <table class="table table-condensed table-bordered table-striped table-hover">
                <thead>
                    <th title='<?php echo xla('Click to view or edit'); ?>'><?php echo xlt('Organization'); ?></th>
                    <th><?php echo xlt('Name'); ?></th>
                    <th><?php echo xlt('Local'); ?></th><!-- empty for external -->
                    <th><?php echo xlt('Type'); ?></th>
                    <th><?php echo xlt('Specialty'); ?></th>
                    <th><?php echo xlt('Phone(W)'); ?></th>
                    <th><?php echo xlt('Mobile'); ?></th>
                    <th><?php echo xlt('Fax'); ?></th>
                    <th><?php echo xlt('City'); ?></th>
                    <th><?php echo xlt('State'); ?></th>
                    <th><?php echo xlt('Postal'); ?></th>
                </thead>
                <?php
                $encount = 0;
                while ($row = sqlFetchArray($res)) {
                    ++$encount;

                    $displayName = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']; // Person Name
                    if ($row['suffix'] > '') {
                        $displayName .=", " . $row['suffix'];
                    }

                    if (acl_check('admin', 'practice') || (empty($row['ab_name']))) {
                       // Allow edit, since have access or (no item type and not a local user)
                        $trTitle = xl('Select') . ' ' . $displayName;
                        echo " <tr class='address_names detail' style='cursor: pointer' onclick='selAddrBookContact(" . attr_js($row['id']) . ", " . attr_js($row['fname']) . ", " . attr_js($row['lname']) . ")' title='" . attr($trTitle) . "'>\n";
                    } else {
                       // Do not allow edit, since no access and (item is a type or is a local user)
                        $trTitle = $displayName . " (" . xl("Not Allowed to Select") . ")";
                        echo " <tr class='address_names detail' title='" . attr($trTitle) . "'>\n";
                    }

                    echo "  <td>" . text($row['organization']) . "</td>\n";
                    echo "  <td>" . text($displayName) . "</td>\n";
                    echo "  <td>--</td>\n";
                    echo "  <td>" . generate_display_field(array('data_type'=>'1','list_id'=>'abook_type'), $row['ab_name']) . "</td>\n";
                    echo "  <td>" . text($row['specialty']) . "</td>\n";
                    echo "  <td>" . text($row['phonew1'])   . "</td>\n";
                    echo "  <td>" . text($row['phonecell']) . "</td>\n";
                    echo "  <td>" . text($row['fax'])       . "</td>\n";
                    echo "  <td>" . text($row['city'])      . "</td>\n";
                    echo "  <td>" . text($row['state'])     . "</td>\n";
                    echo "  <td>" . text($row['zip'])       . "</td>\n";
                    echo " </tr>\n";
                }
                ?>
            </table>
        </div>
        <?php } ?>
        <?php Header::setupAssets('topdialog'); ?>
        <script>
            <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

            function selAddrBookContact(cid, fname, lname) {
                if (opener.closed || !opener.setAddrBook) {
                    alert("<?php echo htmlspecialchars(xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
                } else {
                    opener.setAddrBook('<?php echo $_POST['id'] ?>', cid, fname, lname);
                    dlgclose();
                    return false;
                }
            }
            // Callback from popups to refresh this display.
            function refreshme() {
                // location.reload();
                document.forms[0].submit();
            }

        </script>
    </div>
</body>

</html>
