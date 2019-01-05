<?php
//our api for 3rd party developers
require_once dirname(__FILE__) . "/../interface/globals.php";

use OpenEMR\Billing\BillingUtilities;

$GLOBALS['form_exit_url'] = "javascript:parent.closeTab(window.name, false)";

function formHeader($title = "My Form")
{
    ?>
    <html>
    <head>
    <?php html_header_show();?>
    <link rel=stylesheet href="<?php echo $GLOBALS['css_header']?>" type="text/css">
    <title><?php echo $title?></title>
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

/**
 * Original Query: "insert into " . escape_table_name($tableName) . " set " . $attendant_type . "='" . add_escape_custom($_SESSION['pid']) . "',groupname='" . add_escape_custom($_SESSION['authProvider']) . "',user='" . add_escape_custom($_SESSION['authUser']) . "',authorized='" . add_escape_custom($authorized) . "',activity=1, date = NOW(),";
 *
 * @param  $tableName
 * @param  $attendant_type
 * @return string
 */
function buildInsertFormResultsQuery($tableName, $params)
{
    $escapedTableName = escape_table_name($tableName);
    $boundColumns = implode('= ?,', array_keys($params)) . '= ?,';
    return <<<QUERY
INSERT INTO {$escapedTableName} SET date = NOW(), {$boundColumns};
QUERY;
}

/**
 * original update " . escape_table_name($tableName) . " set pid ='" . add_escape_custom($_SESSION['pid']) . "',groupname='" . add_escape_custom($_SESSION['authProvider']) . "',user='" . add_escape_custom($_SESSION['authUser']) . "',authorized='" . add_escape_custom($authorized) . "',activity=1, date = NOW(),
 *
 * @param  $tableName
 * @param  $inputParams
 * @return string
 */
function buildUpdateFormResultsQuery($tableName, $inputParams)
{
    $escapedTableName = escape_table_name($tableName);
    $boundColumns = implode('= ?,', array_keys($inputParams)) . '= ?,';
    return <<<QUERY
UPDATE {$escapedTableName} SET date = NOW(), {$boundColumns} WHERE id=?;
QUERY;
}

function formSubmit($tableName, $values, $id, $authorized = "0")
{
    global $attendant_type;
    $params = [
        $attendant_type => add_escape_custom($_SESSION['pid']),
        'groupname' => add_escape_custom($_SESSION['authProvider']),
        'user' => add_escape_custom($_SESSION['authUser']),
        'authorized' => add_escape_custom($authorized),
        'activity' => 1
    ];
    foreach ($values as $key => $value) {
        if (strpos($key, "openemr_net_cpt") === 0) {
            //code to auto add cpt code
            if (!empty($value)) {
                $code_array = explode(" ", $value, 2);

                BillingUtilities::addBilling(date("Ymd"), 'CPT4', $code_array[0], $code_array[1], $_SESSION['pid'], $authorized, $_SESSION['authUserID']);
            }
        } //case where key looks like "[a-zA-Z]*diagnosis[0-9]" which is special, it is used to auto add ICD codes
        elseif (strpos($key, "diagnosis") == (strlen($key) - 10) && !(strpos($key, "diagnosis") === false)) {
            //icd auto add ICD9-CM
            if (!empty($value)) {
                $code_array = explode(" ", $value, 2);
                BillingUtilities::addBilling(date("Ymd"), 'ICD9-M', $code_array[0], $code_array[1], $_SESSION['pid'], $authorized, $_SESSION['authUserID']);
            }
        } else {
            $params[escape_sql_column_name($key, array($tableName))] = add_escape_custom($value);
        }
    }

    $sql = buildInsertFormResultsQuery($tableName, $params);
    return sqlInsert($sql, array_values($params));
}


function formUpdate($tableName, $values, $id, $authorized = "0")
{
    $params = [
        'pid' =>  add_escape_custom($_SESSION['pid']),
        'groupname' => add_escape_custom($_SESSION['authProvider']),
        'user' => add_escape_custom($_SESSION['authUser']),
        'authorized' => add_escape_custom($authorized),
        'activity' => 1
    ];
    foreach ($values as $key => $value) {
        $params[escape_sql_column_name($key, array($tableName))] = add_escape_custom($value);
    }

    $sql = buildUpdateFormResultsQuery($tableName, $params);
    $params['id'] = $id; // set the 'final' value, which isn't column bound.
    return sqlInsert($sql, array_values($params));
}

function formJump($address = '')
{
    echo "<script>\n";
    if ($address) {
        echo "top.restoreSession();\n";
        echo "location.href = '" . addslashes($address) . "';\n";
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
    return sqlQuery("select $cols from `" . escape_table_name($tableName) . "` where id=? and pid = ? and activity like ? order by date DESC LIMIT 0,1", array($id, $GLOBALS['pid'], $activity));
}

function formGetIds($tableName, $cols = "*", $limit = 'all', $start = 0, $activity = "1")
{
    $params = [$_SESSION['pid']];
    if ($limit == "all") {
        // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
        $sql = "select $cols from `" . escape_table_name($tableName) . "` where pid like ?";
        if ($activity != "all") {
            $sql .= "and activity like ? ";
            $params[] = $activity;
        }

        $sql .= "order by date DESC";
    } else {
        $sql = "select $cols from pnotes where pid like ?";
        $sql .= " AND deleted != 1 "; // exclude ALL deleted notes
        if ($activity != "all") {
            $sql .= "and activity like ? ";
            $params[] = $activity;
        }

        $sql .= "order by date DESC LIMIT ?, ?";
        $params[] =  $start;
        $params[] = $limit;
    }

    $res = sqlStatement($sql, $params);

    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function formDisappear($tableName, $id)
{
    // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
    if (sqlStatement("update `" . escape_table_name($tableName) . "` set activity = '0' where id=? and pid=?", [$id, add_escape_custom($_SESSION['pid'])])) {
        return true;
    }

    return false;
}

function formReappear($tableName, $id)
{
    // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
    if (sqlStatement("update `" . escape_table_name($tableName) . "` set activity = '1' where id=? and pid=?", [$id, add_escape_custom($_SESSION['pid'])])) {
        return true;
    }

    return false;
}

?>
