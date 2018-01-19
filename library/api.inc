<?php
//our api for 3rd party developers
require_once(dirname(__FILE__) . "/../interface/globals.php");
require_once("{$GLOBALS['srcdir']}/billing.inc");

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

function formSubmit($tableName, $values, $id, $authorized = "0")
{
        global $attendant_type;

    $sql = "insert into " . escape_table_name($tableName) . " set " .  $attendant_type . "='".add_escape_custom($_SESSION['pid'])."',groupname='".add_escape_custom($_SESSION['authProvider'])."',user='".add_escape_custom($_SESSION['authUser'])."',authorized='".add_escape_custom($authorized)."',activity=1, date = NOW(),";
    foreach ($values as $key => $value) {
        if (strpos($key, "openemr_net_cpt") === 0) {
            //code to auto add cpt code
            if (!empty($value)) {
                $code_array = explode(" ", $value, 2);

                addBilling(date("Ymd"), 'CPT4', $code_array[0], $code_array[1], $_SESSION['pid'], $authorized, $_SESSION['authUserID']);
            }
        } //case where key looks like "[a-zA-Z]*diagnosis[0-9]" which is special, it is used to auto add ICD codes

        elseif (strpos($key, "diagnosis") == (strlen($key) -10) && !(strpos($key, "diagnosis")=== false )) {
            //icd auto add ICD9-CM
            if (!empty($value)) {
                $code_array = explode(" ", $value, 2);
                addBilling(date("Ymd"), 'ICD9-M', $code_array[0], $code_array[1], $_SESSION['pid'], $authorized, $_SESSION['authUserID']);
            }
        } else {
            $sql .= " " . escape_sql_column_name($key, array($tableName)) . " = '" . add_escape_custom($value) . "',";
        }
    }

    $sql = substr($sql, 0, -1);
    return sqlInsert($sql);
}


function formUpdate($tableName, $values, $id, $authorized = "0")
{
    $sql = "update " . escape_table_name($tableName) . " set pid ='".add_escape_custom($_SESSION['pid'])."',groupname='".add_escape_custom($_SESSION['authProvider'])."',user='".add_escape_custom($_SESSION['authUser'])."',authorized='".add_escape_custom($authorized)."',activity=1, date = NOW(),";
    foreach ($values as $key => $value) {
        $sql .= " " . escape_sql_column_name($key, array($tableName)) . " = '" . add_escape_custom($value) . "',";
    }

    $sql = substr($sql, 0, -1);
    $sql .= " where id='".add_escape_custom($id)."'";

    return sqlInsert($sql);
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
    return sqlQuery("select $cols from `" . escape_table_name($tableName) . "` where id=? and pid = ? and activity like ? order by date DESC LIMIT 0,1", array($id,$GLOBALS['pid'],$activity)) ;
}

function formGetIds($tableName, $cols = "*", $limit = 'all', $start = 0, $activity = "1")
{
    if ($limit == "all") {
                // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
                $sql = "select $cols from `" . escape_table_name($tableName) . "` where pid like '$pid' ";
        if ($activity != "all") {
                $sql .= "and activity like '$activity' ";
        }

                $sql .= "order by date DESC";
    } else {
        $sql = "select $cols from pnotes where pid like '$pid' ";
        $sql .= " AND deleted != 1 "; // exclude ALL deleted notes
        if ($activity != "all") {
                $sql .= "and activity like '$activity' ";
        }

        $sql .= "order by date DESC LIMIT $start, $limit";
    }

        $res = sqlStatement($sql);

    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
    }

        return $all;
}

function formDisappear($tableName, $id)
{
        // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
    if (sqlStatement("update `" . escape_table_name($tableName) . "` set activity = '0' where id='$id' and pid='$pid'")) {
        return true;
    }

    return false;
}

function formReappear($tableName, $id)
{
        // Run through escape_table_name() function to support dynamic form names in addition to mitigate sql table casing issues.
    if (sqlStatement("update `" . escape_table_name($tableName) . "` set activity = '1' where id='$id' and pid='$pid'")) {
        return true;
    }

    return false;
}
?>
