<?php

/**
 * old api for 3rd parties
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Core\Header;

$GLOBALS['form_exit_url'] = "javascript:parent.closeTab(window.name, false)";

function formHeader($title = "My Form")
{
    ?>
    <html>
    <head>
    <?php Header::setupHeader(); ?>
    <title><?php echo text($title); ?></title>
    </head>
    <body background="<?php echo $GLOBALS['backpic']?>" topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
    <?php
}

function formFooter()
{
    ?>
    </body>
    </html>
    <?php
}

function formSubmit($tableName, $values, $id, $authorized = "0")
{
    global $attendant_type;

    $sqlBindingArray = [$_SESSION['pid'], $_SESSION['authProvider'], $_SESSION['authUser'], $authorized];
    $sql = "insert into " . escape_table_name($tableName) . " set " .  escape_sql_column_name($attendant_type, array($tableName)) . "=?, groupname=?, user=?, authorized=?, activity=1, date = NOW(),";
    foreach ($values as $key => $value) {
        if ($key == "csrf_token_form") {
            continue;
        }
        if (strpos($key, "openemr_net_cpt") === 0) {
            //code to auto add cpt code
            if (!empty($value)) {
                $code_array = explode(" ", $value, 2);

                BillingUtilities::addBilling(date("Ymd"), 'CPT4', $code_array[0], $code_array[1], $_SESSION['pid'], $authorized, $_SESSION['authUserID']);
            }
        } elseif (strpos($key, "diagnosis") == (strlen($key) - 10) && !(strpos($key, "diagnosis") === false )) {
            //case where key looks like "[a-zA-Z]*diagnosis[0-9]" which is special, it is used to auto add ICD codes
            //icd auto add ICD9-CM
            if (!empty($value)) {
                $code_array = explode(" ", $value, 2);
                BillingUtilities::addBilling(date("Ymd"), 'ICD9-M', $code_array[0], $code_array[1], $_SESSION['pid'], $authorized, $_SESSION['authUserID']);
            }
        } else {
            $sql .= " " . escape_sql_column_name($key, array($tableName)) . " = ?,";
            $sqlBindingArray[] = $value;
        }
    }

    $sql = substr($sql, 0, -1);
    return sqlInsert($sql, $sqlBindingArray);
}


function formUpdate($tableName, $values, $id, $authorized = "0")
{
    $sqlBindingArray = [$_SESSION['pid'], $_SESSION['authProvider'], $_SESSION['authUser'], $authorized];
    $sql = "update " . escape_table_name($tableName) . " set pid =?, groupname=?, user=? ,authorized=?, activity=1, date = NOW(),";
    foreach ($values as $key => $value) {
        if ($key == "csrf_token_form") {
            continue;
        }
        $sql .= " " . escape_sql_column_name($key, array($tableName)) . " = ?,";
        $sqlBindingArray[] = $value;
    }

    $sql = substr($sql, 0, -1);
    $sql .= " where id=?";
    $sqlBindingArray[] = $id;

    return sqlInsert($sql, $sqlBindingArray);
}

function formJump($address = '')
{
    echo "<script>\n";
    if ($address) {
        echo "top.restoreSession();\n";
        echo "location.href = " . js_escape($address) . ";\n";
    } else {
        echo "parent.closeTab(window.name, true);\n";
    }
    echo "</script>\n";
    // TBD: Exit seems wrong here, but that's how it has been forever.
    exit;
}

function formFetch($tableName, $id, $cols = "*", $activity = "1")
{
        // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
    return sqlQuery("select " . escape_sql_column_name(process_cols_escape($cols), array($tableName)) . " from `" . escape_table_name($tableName) . "` where id=? and pid = ? and activity like ? order by date DESC LIMIT 0,1", array($id,$GLOBALS['pid'],$activity)) ;
}

function formDisappear($tableName, $id)
{
        // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
    if (sqlStatement("update `" . escape_table_name($tableName) . "` set activity = '0' where id=? and pid=?", [$id, $pid])) {
        return true;
    }

    return false;
}

function formReappear($tableName, $id)
{
        // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
    if (sqlStatement("update `" . escape_table_name($tableName) . "` set activity = '1' where id=? and pid=?", [$id, $pid])) {
        return true;
    }

    return false;
}
?>
