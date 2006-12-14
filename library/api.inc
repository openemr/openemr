<?php
//our api for 3rd party developers
include_once("../../globals.php");
include_once("{$GLOBALS['srcdir']}/sql.inc");
include_once("{$GLOBALS['srcdir']}/billing.inc");

$GLOBALS['form_exit_url'] = $GLOBALS['concurrent_layout'] ?
	"$rootdir/patient_file/encounter/encounter_top.php" :
	"$rootdir/patient_file/encounter/patient_encounter.php";

function formHeader ($title = "My Form")
{
	?>
	<html>
	<head>
	<link rel=stylesheet href="<?=$GLOBALS['css_header']?>" type="text/css">
	<title><?=$title?></title>
	</head>
	<body background="<?=$GLOBALS['backpic']?>" topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
	<?php
}

function formFooter ()
{
	?>
	</body>
	</form>
	<?php
}

function formSubmit ($tableName, $values, $id, $authorized = "0")
{
	$sql = "insert into $tableName set pid = {$_SESSION['pid']},groupname='".$_SESSION['authProvider']."',user='".$_SESSION['authUser']."',authorized=$authorized,activity=1, date = NOW(),";
	while(list($key, $value) = each($values))
		if (strpos($key,"openemr_net_cpt") === 0) {
			//code to auto add cpt code
			if (!empty($value)) {
				$code_array = split(" ",$value,2);
				
				addBilling(date("Ymd"), 'CPT4', $code_array[0], $code_array[1], $_SESSION['pid'], $authorized, $_SESSION['authUserID']);
			}
					
		}
		//case where key looks like "[a-zA-Z]*diagnosis[0-9]" which is special, it is used to auto add ICD codes
		
		elseif (strpos($key,"diagnosis") == (strlen($key) -10) && !(strpos($key,"diagnosis")=== false )) {
			//icd auto add ICD9-CM
			if (!empty($value)) {
				$code_array = split(" ",$value,2);
				addBilling(date("Ymd"), 'ICD9-M', $code_array[0], $code_array[1], $_SESSION['pid'], $authorized, $_SESSION['authUserID']);
			}
		}
		else {
			$sql .= " $key = '$value',";
		}
	$sql = substr($sql, 0, -1);
	return sqlInsert($sql);
}


function formUpdate ($tableName, $values, $id, $authorized = "0")
{
	$sql = "update $tableName set pid = {$_SESSION['pid']},groupname='".$_SESSION['authProvider']."',user='".$_SESSION['authUser']."',authorized=$authorized,activity=1, date = NOW(),";
	while(list($key, $value) = each($values))
		$sql .= " $key = '$value',";
	$sql = substr($sql, 0, -1);
	$sql .= " where id=$id";
	
	return sqlInsert($sql);
}


function formJump ($address = "0")
{
	$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
	if ($address == "0")
		$address = "{$GLOBALS['rootdir']}/patient_file/encounter/$returnurl";
	echo "\n<script language='Javascript'>window.location='$address';</script>\n";
	exit;
}

function formFetch ($tableName, $id, $cols="*", $activity="1")
{
	return sqlQuery ( "select $cols from `$tableName` where id='$id' and pid = '{$GLOBALS['pid']}' and activity like '$activity' order by date DESC LIMIT 0,1" ) ;
}

function formGetIds ($tableName, $cols = "*", $limit='all', $start=0, $activity = "1")
{
	if($limit == "all")
        {

                $sql = "select $cols from `$tableName` where pid like '$pid' ";
                if ($activity != "all")
                        $sql .= "and activity like '$activity' ";
                $sql .= "order by date DESC";
        }
        else
        {
                $sql = "select $cols from pnotes where pid like '$pid' ";
                if ($activity != "all")
                        $sql .= "and activity like '$activity' ";
                $sql .= "order by date DESC LIMIT $start, $limit";
        }

        $res = sqlStatement($sql);
        
        for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                $all[$iter] = $row;
        return $all;
}

function formDisappear ($tableName, $id)
{
	if (sqlStatement("update `$tableName` set activity = '0' where id='$id' and pid='$pid'")) return true;
	return false;
}

function formReappear ($tableName, $id)
{
        if (sqlStatement("update `$tableName` set activity = '1' where id='$id' and pid='$pid'")) return true;
	return false;
}
?>
