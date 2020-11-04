<?php

/**
 * Maintenance for the list of procedure providers.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('admin', 'users')) {
    die(xlt('Access denied'));
}

// Collect user id if editing entry
$ppid = $_REQUEST['ppid'];

$info_msg = "";

function invalue($name)
{
    $fld = add_escape_custom(trim($_POST[$name]));
    return "'$fld'";
}

function onvalue($name)
{
    $fld = ($_POST[$name] == 'on') ? '1' : '0';
    return "'$fld'";
}

?>
<html>
<head>
<?php Header::setupHeader(['opener']);?>
<title><?php echo $ppid ? xlt('Edit') : xlt('Add New{{Provider}}') ?> <?php echo xlt('Procedure Provider'); ?></title>
<style>
    td {
        font-size: 0.8125rem;
    }

    .inputtext {
        padding-left: 2px;
        padding-right: 2px;
    }

    .button {
        font-family: sans-serif;
        font-size: 0.75rem;
        font-weight: bold;
    }

    .label-div > a {
        display: none;
    }

    .label-div:hover > a {
        display: inline-block;
    }
    /* This is overridden on any theme */
    div[id$="_info"] {
        background: #F7FAB3;
        color: #000;
        padding: 20px;
        margin: 10px 15px 0 15px;
    }
    div[id$="_info"] > a {
        margin-left: 10px;
    }
</style>

</head>

<body>
    <div class="container mt-3">
        <?php
        // If we are saving, then save and close the window.
        // lab_director is the id of the organization in the users table
        //
        if (!empty($_POST['form_save'])) {
            $org_qry  = "SELECT organization FROM users WHERE id = ?";
            $org_res  = sqlQuery($org_qry, array($_POST['form_name']));
            $org_name = $org_res['organization'];
            $sets =
            "name = '"        . add_escape_custom($org_name) . "', " .
            "lab_director = " . invalue('form_name')         . ", " .
            "npi = "          . invalue('form_npi')          . ", " .
            "send_app_id = "  . invalue('form_send_app_id')  . ", " .
            "send_fac_id = "  . invalue('form_send_fac_id')  . ", " .
            "recv_app_id = "  . invalue('form_recv_app_id')  . ", " .
            "recv_fac_id = "  . invalue('form_recv_fac_id')  . ", " .
            "DorP = "         . invalue('form_DorP')         . ", " .
            "direction = "    . invalue('form_direction')    . ", " .
            "protocol = "     . invalue('form_protocol')     . ", " .
            "remote_host = "  . invalue('form_remote_host')  . ", " .
            "login = "        . invalue('form_login')        . ", " .
            "password = "     . invalue('form_password')     . ", " .
            "orders_path = "  . invalue('form_orders_path')  . ", " .
            "results_path = " . invalue('form_results_path') . ", " .
            "notes = "        . invalue('form_notes')        . ", " .
            "active = "       . onvalue('form_active');

            if ($ppid) {
                $query = "UPDATE procedure_providers SET $sets " .
                "WHERE ppid = '"  . add_escape_custom($ppid) . "'";
                sqlStatement($query);
            } else {
                $ppid = sqlInsert("INSERT INTO `procedure_providers` SET $sets");
            }
        } elseif (!empty($_POST['form_delete'])) {
            if ($ppid) {
                sqlStatement("DELETE FROM procedure_providers WHERE ppid = ?", array($ppid));
            }
        }

        if (!empty($_POST['form_save']) || !empty($_POST['form_delete'])) {
          // Close this window and redisplay the updated list.
            echo "<script>\n";
            if ($info_msg) {
                echo " alert(" . js_escape($info_msg) . ");\n";
            }

            echo " window.close();\n";
            echo " if (opener.refreshme) opener.refreshme();\n";
            echo "</script></body></html>\n";
            exit();
        }

        if ($ppid) {
            $row = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?", array($ppid));
        }

        $ppid_active = $row['active'] ?? null;

        $org_query = "SELECT id, organization FROM users WHERE abook_type LIKE 'ord_%'";
        $org_res = sqlStatement($org_query);
        $optionsStr = '';
        while ($org_row = sqlFetchArray($org_res)) {
            $org_name = $org_row['organization'];
            $selected = '';
            if ($ppid) {
                if ($row['lab_director'] == $org_row['id']) {
                    $selected = "selected";
                    $optionsStr .= "<option value='" . attr($org_row['id']) . "' $selected>" .  text($org_name) . "</option>";
                }
            } else {
                $checkName = sqlQuery("SELECT `name` FROM `procedure_providers` WHERE `name` = ?", [$org_name]);
                if (empty($checkName['name'])) {
                    $optionsStr .= "<option value='" . attr($org_row['id']) . "' $selected>" .  text($org_name) . "</option>";
                }
            }
        }
        ?>

        <div class="row">
            <div class="col-sm-12">
                <form method='post' name='theform' action="procedure_provider_edit.php?ppid=<?php echo attr_url($ppid); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="card">
                        <div class="card-header" name="form_legend" id="form_legend">
                            <?php echo xlt('Enter Provider Details'); ?>  <i id="enter-details-tooltip" class="fa fa-info-circle oe-text-black oe-superscript" aria-hidden="true"></i>
                        </div>
                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="col-sm-6">
                                        <div class="clearfix">
                                            <div class="label-div">
                                                <label for="form_active"><?php echo xlt('Active'); ?>: </label>
                                            </div>
                                            <input type='checkbox' class='form-control' name='form_active' id='form_active'
                                                <?php
                                                if ($ppid) {
                                                    echo ($ppid_active) ? " checked" : "";
                                                } else {
                                                    echo " checked";
                                                } ?> />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="clearfix">
                                            <div class="label-div">
                                                <label for="form_name"><?php echo xlt('Name'); ?>:</label><a href="#name_info" class="info-anchor icon-tooltip"  data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <select name='form_name' id='form_name' class='form-control'>
                                                <?php echo $optionsStr ?? ''; ?>
                                            </select>
                                        </div>
                                        <div id="name_info" class="collapse">
                                            <a href="#name_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                            <p><?php echo xlt("Name - Select a provider name from the drop-down list");?></p>
                                            <p><?php echo xlt("For the name to appear on the drop-down list it must be first entered in Administration > Address Book ");?></p>
                                            <p><?php echo xlt("Select Lab Service in the Type drop-down box and enter a name under organization");?></p>
                                            <p><?php echo xlt("For detailed instructions close the 'Enter Provider Details' popup and click on the Help icon on the main form. ");?><i class="fa fa-question-circle" aria-hidden="true"></i></p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mt-3">
                                        <div class="clearfix">
                                            <div class="label-div">
                                                <label class="col-form-label" for="form_npi"><?php echo xlt('NPI'); ?>:</label> <a href="#npi_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <input type='text' name='form_npi' id='form_npi' maxlength='10' value='<?php echo attr($row['npi'] ?? ''); ?>' class='form-control' />
                                        </div>
                                        <div id="npi_info" class="collapse">
                                            <a href="#npi_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                            <p><?php echo xlt("NPI - Enter the Provider's unique 10-digit National Provider Identifier or NPI identification number");?></p>
                                            <p><?php echo xlt("It is issued to health care providers in the United States by the Centers for Medicare and Medicaid Services (CMS)");?></p>
                                            <p><?php echo xlt("This has to entered once in this form");?></p>
                                            <p><?php echo xlt("IMPORTANT: The NPI number also exists in the Address Book entry for the provider, take care to enter the correct NPI number");?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label class="col-form-label" for="form_DorP"><?php echo xlt('Usage'); ?>:</label> <a href="#usage_info" class="info-anchor icon-tooltip"  data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="col-sm-6">
                                            <select name='form_DorP' id='form_DorP' class='form-control' title='<?php echo xla('HL7 - MSH-11 - Processing ID'); ?>'>
                                            <?php
                                            foreach (
                                                array(
                                                    'D' => xl('Debugging'),
                                                    'P' => xl('Production'),
                                                ) as $key => $value
                                            ) {
                                                echo "    <option value='" . attr($key) . "'";
                                                if (!empty($row['DorP']) && ($key == $row['DorP'])) {
                                                    echo " selected";
                                                }
                                                echo ">" . text($value) . "</option>\n";
                                            }
                                            ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="usage_info" class="collapse">
                                        <a href="#usage_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Usage - is only required if you are submitting an electronic order to an external facility");?></p>
                                        <p><?php echo xlt("It is a field in the HL7 Message header known as Processing ID");?></p>
                                        <p><?php echo xlt("Health Level-7 or HL7 refers to a set of international standards for transfer of clinical and administrative data between software applications used by various healthcare providers");?></p>
                                        <p><?php echo xlt("This field is used to decide whether to process the message as defined in HL7 Application (level 7) Processing rules");?></p>
                                        <p><?php echo xlt("Select the appropriate choice - Debugging or Production");?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label class="col-form-label" for="form_send_app_id"><?php echo xlt('Sender IDs'); ?>:</label> <a href="#sender_id_info"  class="info-anchor icon-tooltip"  data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="row col-12">
                                            <div class="col-sm-6">
                                                <input type='text' name='form_send_app_id' id='form_send_app_id' maxlength='100'
                                                value='<?php echo attr($row['send_app_id'] ?? ''); ?>'
                                                title='<?php echo xla('HL7 - MSH-3.1 - Sending application'); ?>'
                                                placeholder='<?php echo xla('Enter Application Name'); ?>'
                                                class='form-control' />
                                            </div>
                                            <div class="col-sm-6">
                                                <input type='text' name='form_send_fac_id' id='form_send_fac_id' maxlength='100'
                                                value='<?php echo attr($row['send_fac_id'] ?? ''); ?>'
                                                title='<?php echo xla('HL7 - MSH-4.1 - Sending facility'); ?>'
                                                placeholder='<?php echo xla('Enter Facility Name'); ?>'
                                                class='form-control' />
                                            </div>
                                        </div>
                                    </div>
                                    <div id="sender_id_info" class="collapse">
                                        <a href="#sender_id_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Sender IDs - is only required if you are submitting an electronic order to an external facility");?></p>
                                        <p><?php echo xlt("It consists of two parts - the Sending application and Sending facility");?></p>
                                        <p><?php echo xlt("These are used to populate fields 3 and 4 in the HL7 MSH - message header");?></p>
                                        <p><?php echo xlt("Sending application name will be provided by the facility that you will be connecting to");?></p>
                                        <p><?php echo xlt("Sending facility name is user defined");?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label class="col-form-label" for="form_recv_app_id"><?php echo xlt('Receiver IDs'); ?>:</label> <a href="#receiver_id_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="row col-12">
                                            <div class="col-sm-6">
                                                <input type='text' name='form_recv_app_id' id='form_recv_app_id' maxlength='100' value='<?php echo attr($row['recv_app_id'] ?? ''); ?>' title='<?php echo xla('HL7 - MSH-5.1 - Receiving application'); ?>' placeholder='<?php echo xla('Enter Application Name'); ?>' class='form-control' />
                                            </div>
                                            <div class="col-sm-6">
                                                <input type='text' name='form_recv_fac_id' id='form_recv_fac_id' maxlength='100' value='<?php echo attr($row['recv_fac_id'] ?? ''); ?>' title='<?php echo xla('HL7 - MSH-6.1 - Receiving facility'); ?>' placeholder='<?php echo xla('Enter Facility Name'); ?>' class='form-control' />
                                            </div>
                                        </div>
                                    </div>
                                    <div id="receiver_id_info" class="collapse">
                                        <a href="#receiver_id_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Receiver IDs - is only required if you are submitting an electronic order to an external facility");?></p>
                                        <p><?php echo xlt("It consists of two parts - the Receiving application and Receiving facility");?></p>
                                        <p><?php echo xlt("These are used to populate fields 5 and 6 in the HL7 MSH - message header");?></p>
                                        <p><?php echo xlt("They will be provided by the facility that you will be connecting to");?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label for="form_protocol"><?php echo xlt('Protocol'); ?>:</label> <a href="#protocol_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="row col-12">
                                            <div class="col-sm-6">
                                                <select name='form_protocol' id='form_protocol' class='form-control'>
                                                <?php
                                                foreach (
                                                    array(
                                                        // Add to this list as more protocols are supported.
                                                        'DL'   => xl('Download'),
                                                        'SFTP' => xl('SFTP'),
                                                        'FS'   => xl('Local Filesystem'),
                                                    ) as $key => $value
                                                ) {
                                                    echo "    <option value='" . attr($key) . "'";
                                                    if (!empty($row['protocol']) && ($key == $row['protocol'])) {
                                                        echo " selected";
                                                    }
                                                    echo ">" . text($value) . "</option>\n";
                                                }
                                                ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <select name='form_direction' id='form_direction' class='form-control'>
                                                <?php
                                                foreach (
                                                    array(
                                                        'B' => xl('Bidirectional'),
                                                        'R' => xl('Results Only'),
                                                    ) as $key => $value
                                                ) {
                                                    echo "    <option value='" . attr($key) . "'";
                                                    if (!empty($row['direction']) && ($key == $row['direction'])) {
                                                        echo " selected";
                                                    }

                                                    echo ">" . text($value) . "</option>\n";
                                                }
                                                ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="protocol_info" class="collapse">
                                        <a href="#protocol_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Protocol - consists of two parts - the method used to send orders and receive results and whether it is used to receive results only or is used to send orders and receive result i.e. bidirectional");?></p>
                                        <p><?php echo xlt("If you do not submit orders electronically or receive result electronically leave it as the default value, Download");?></p>
                                        <p><?php echo xlt("Download will download a text file containing the order in the HL7v2.3 message format to the downloads directory of your computer");?></p>
                                        <p><?php echo xlt("SFTP will send the order as a HL7v2.3 message to the receiving lab using the SFTP protocol");?></p>
                                        <p><?php echo xlt("Secure File Transfer Protocol, or SFTP is a network protocol that provides file access, file transfer, and file management over a secure connection");?></p>
                                        <p><?php echo xlt("Local Filesystem will store the order as a HL7v2.3 message in a predefined location in the local server hosting openEMR");?></p>
                                        <p><?php echo xlt("Select Bidirectional or Results Only as appropriate, again only used for electronic orders");?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label for="form_login"><?php echo xlt('Login'); ?>:</label> <a href="#login_info" class="info-anchor icon-tooltip" data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="row col-12">
                                            <div class="col-sm-6">
                                                <input type='text' name='form_login' id='form_login' maxlength='255' value='<?php echo attr($row['login'] ?? ''); ?>' placeholder='<?php echo xla('Enter User Login ID'); ?>' class='form-control' />
                                            </div>
                                            <div class="col-sm-6">
                                                <input type='text' name='form_password' id='form_password' maxlength='255' value='<?php echo attr($row['password'] ?? ''); ?>' placeholder='<?php echo xla('Enter Password'); ?>' class='form-control' />
                                            </div>
                                        </div>
                                    </div>
                                    <div id="login_info" class="collapse">
                                        <a href="#login_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Login - details are only required if you are connecting to a facility using the SFTP protocol ");?></p>
                                        <p><?php echo xlt("Type in the username and password provided by the facility");?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label class="col-form-label" for="form_remote_host"><?php echo xlt('Remote Host'); ?>:</label> <a href="#remote_host_info"  class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="col-sm-12">
                                            <input type='text' name='form_remote_host' id='form_remote_host' maxlength='255' value='<?php echo attr($row['remote_host'] ?? ''); ?>' class='form-control' />
                                        </div>
                                    </div>
                                    <div id="remote_host_info" class="collapse">
                                        <a href="#remote_host_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Remote Host - is only required if you are submitting an electronic order to an external facility or just receiving results from it");?></p>
                                        <p><?php echo xlt("Type in the URL of the external facility to which the order will be sent, this will be provided by the facility");?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label class="col-form-label" for="form_orders_path"><?php echo xlt('Orders Path'); ?>:</label> <a href="#orders_path_info"  class="info-anchor icon-tooltip" data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="col-sm-12">
                                            <input type='text' name='form_orders_path' id='form_orders_path' maxlength='255'
                                            value='<?php echo attr($row['orders_path'] ?? ''); ?>' class='form-control' />
                                        </div>
                                    </div>
                                    <div id="orders_path_info" class="collapse">
                                        <a href="#orders_path_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Orders Path - is only required if you are submitting an electronic order to an external facility");?></p>
                                        <p><?php echo xlt("Type in the location of the directory or folder in which the created orders (HL7 messages) will be stored");?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label class="col-form-label" for="form_results_path"><?php echo xlt('Results Path'); ?>:</label> <a href="#results_path_info"  class="info-anchor icon-tooltip" data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="col-sm-12">
                                            <input type='text' name='form_results_path' id='form_results_path' maxlength='255'
                                            value='<?php echo attr($row['results_path'] ?? ''); ?>' class='form-control' />
                                        </div>
                                    </div>
                                    <div id="results_path_info" class="collapse">
                                        <a href="#results_path_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Results Path - is only required if you are submitting an electronic order to an external facility or just receiving results from it");?></p>
                                        <p><?php echo xlt("Type in the location of the directory or folder in which the returned results (HL7 messages) will be stored");?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="clearfix">
                                        <div class="col-sm-12 label-div">
                                            <label class="col-form-label" for="form_notes"><?php echo xlt('Notes'); ?>:</label> <a href="#notes_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="col-sm-12">
                                            <textarea rows='3' name='form_notes' id='form_notes' wrap='virtual' class='form-control'><?php echo text($row['notes'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div id="notes_info" class="collapse">
                                        <a href="#notes_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                        <p><?php echo xlt("Any additional information pertaining to this provider");?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
                    <div class="mt-3">
                        <div class="form-group clearfix" id="button-container">
                            <div class="col-sm-12 text-left position-override">
                                <div class="btn-group" role="group">
                                    <button type='submit' name='form_save' class="btn btn-primary btn-save" value='<?php echo xla('Save'); ?>'><?php echo xlt('Save'); ?></button>
                                    <button type="button" class="btn btn-secondary btn-cancel" onclick='window.close()';><?php echo xlt('Cancel');?></button>
                                    <?php if ($ppid) { ?>
                                        <button type='submit' name='form_delete' class="btn btn-danger btn-cancel btn-delete" value='<?php echo xla('Delete'); ?>'><?php echo xlt('Delete'); ?></button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div><!--end of container div-->
    <script>
        //jqury-ui tooltip
        $(function () {
            $('.icon-tooltip i').attr({"title": <?php echo xlj('Click to see more information'); ?>, "data-toggle":"tooltip", "data-placement":"bottom"}).tooltip({
                show: {
                    delay: 700,
                    duration: 0
                }
            });
            $('#enter-details-tooltip').attr({"title": <?php echo xlj('Additional help to fill out this form is available by hovering over labels of each box and clicking on the dark blue help ? icon that is revealed. On mobile devices tap once on the label to reveal the help icon and tap on the icon to show the help section'); ?>, "data-toggle":"tooltip", "data-placement":"bottom"}).tooltip();
        });
    </script>
</body>
</html>
