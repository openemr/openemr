<?php
/**
 * Add/Edit/Delete snapshot details of a patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
 // It receives data via a GET request from snapshot_detail_1.php and also POST data from itself
 // For the form to function it MUST receive the GET request data once, the form is designed to close after it is submitted, i.e. it cannot be consecutively POSTed twice
 // Form selectively un-hides the relevant divs to give the appearance of multiple unique forms
 // IMPORTANT: Designed to allow only one snapshot of a particular type per day, if second entry is allowed logic WILL FAIL
require_once("../../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.js.php");
    
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;
?>


<!DOCTYPE html>
<html>
<head>
    <title></title>
    <?php Header::setupHeader(['common','jquery-ui', 'jquery-ui-base']); ?>
    <style>
        .hide_div {
            display:none;
        }
    </style>

<?php
if (isset($_GET['type'])) {//arrive here from the snapshot_1_detail.php page when Add/Edit button is clicked
    //Remember this script gets both GET and POST data, need to verify CSRF token appropriately for each type of request
    if (!verifyCsrfToken($_GET["csrf_token_form"])) {
        csrfNotVerified();
    }
    $get_type = $_GET['type'];
    $get_pid = $_GET['pid'];
    $get_date = $_GET['type'] == 'edit'? $_GET['ss_date'] : $get_date = date('Y-m-d'); // add mode only for current date
    $get_author = $_GET['author'];
    $get_snapshot = $_GET['snapshot'];
    if (strpos($get_snapshot, "~") > 0) {
        $array_snapshots = explode('~', $get_snapshot);
        $array_show_div = $array_snapshots; // to be used in jQuery to show relevant divs
        $snapshot_elem = count($array_snapshots);
        $array_query = trim(str_repeat("? ", $snapshot_elem));// trims last blank space
        $array_query = str_replace(" ", ",", $array_query);// to get the correct number of question marks for the array
        array_push($array_snapshots, $get_date, $get_pid, 1); // to get he correct number and values for the parametets in the array
    } else {
        $array_snapshots = array();
        array_push($array_snapshots, $get_snapshot, $get_date, $get_pid, 1);
        $array_show_div = array($get_snapshot);// to be used in jQuery to show relevant divs
        $array_query = "?";
    }
    $value_array = array(); // used to store retrieved data from the database
    $value_array_post = array(); // used to post an array containing retrieved data from the database as a serialzed and base64_encoded string
    $sql = "SELECT
                pbd.id,
                pbd.date AS exist_date,
				CASE
                    WHEN STRCMP(pbd.loinc_ans_code, 'SCORE') = 0 THEN pbd.loinc_ans_value
                    WHEN STRCMP(pbd.loinc_ans_code, 'OEA') = 0 THEN pbd.loinc_ans_value
                    ELSE CONCAT(
                        pbd.loinc_ans_code,
                        '~',
                        pbd.loinc_ans_value
					)
					END AS option_value,
                IF(
					STRCMP(pbd.loinc_ans_code, 'SCORE') = 0,
					pbc.form_link,
					CONCAT(
						pbc.form_link,
						'_',
						'a'
					)
				) AS form_link_a,
                pbd.loinc_answer_list_id,
				pbd.loinc_ans_code,
				pbd.loinc_ans_value,
				pbd.loinc_que_code,
				pbc.form_link
			FROM
				psychosocial_behavior_data AS pbd
			INNER JOIN psychosocial_behavior_codes AS pbc
			ON
				pbd.loinc_que_code = pbc.loinc_que_code
			WHERE
                SUBSTRING(pbc.form_link, 1, 5) IN($array_query) AND
				pbd.date = ? AND pbd.pid = ? AND pbd.active = ?";
    $values = sqlStatement($sql, $array_snapshots);
    if ($_GET['type'] == 'add') { // there should be no prior entries in db for that date
        if (sqlNumRows($values)) {
            $alert_msg = xlt('Cannot create a new snapshot as a snapshot already exists for') . " " . $get_date . ". "
            . xlt('Please edit that snapshot instead as only one snapshot allowed per date') . "." ;
                
            echo "<script language='JavaScript'>\n";
            echo "alert('$alert_msg');";
            echo "parent.dlgclose();\n";
            echo "</script></body></html>\n";
            exit();
        }
    } elseif ($_GET['type'] == 'edit') {
        if (sqlNumRows($values)) {
            $numrows = sqlNumRows($values);
            while ($value = sqlFetchArray($values)) {
                $value_array [$value['form_link']] = $value['option_value']; // used to populate form with the existing values from the DB
                $value_array_post [$value['form_link_a']] = $value['option_value']; //used to post this array containing retrieved data from the database as a serialzed and base64_encoded string
                $form_link_q = $value['form_link'] . "_q";
                $exist_val_array = array("exist_id"=>$value['id'], "exist_date"=>$value['exist_date'], "exist_que"=>$value['loinc_que_code'], "exist_loinc_answer_list_id"=>$value['loinc_answer_list_id'], "exist_ans"=>$value['loinc_ans_code'], "exist_val"=>$value['loinc_ans_value']);
                $value_array_post_q [$value['form_link']] = $exist_val_array;
            }
        }
    }
}
    
    // CRUD functions on POST
if (isset($_POST['form_save'])) {
    //Remember this script gets both GET and POST data, need to verify CSRF token appropriately for each type of request
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }
    $crud = $_POST['crud'];
    $author = $_POST['author'];
    $post_pid = $_POST['form_pid'];
    $post_date = $_POST['post_date'];
    $date = date('Y-m-d');
    $psy_ans_array = array("financial", "education", "stress", "depression_1" , "depression_2", "depression_3",
    "physical_activity_1", "physical_activity_2", "alcohol_1", "alcohol_2", "alcohol_3", "alcohol_4",
    "social_1", "social_2", "social_3", "social_4", "social_5", "violence_1", "violence_2", "violence_3", "violence_4");
        
    $psy_score_array = array("depression_score", "alcohol_score", "social_score", "violence_score");
        
    $psy_combo_array = array($psy_ans_array, $psy_score_array);
        
    if ($crud == 'add') {
        foreach ($psy_combo_array as $psy_arrays) {
            foreach ($psy_arrays as $psy_element) {
                $psy_ans = strstr($psy_element, "score")? $psy_element : $psy_element ."_a";
                $psy_que = $psy_element . "_q";
                $psy_al = $psy_element . "_al";
                $loinc_que_code = null;
                $loinc_answer_list_id = null;
                $loinc_ans_code = null;
                $loinc_ans_value = null;
                if (!empty($_POST[$psy_ans]) || $_POST[$psy_ans] === '0') {// only update if answer present
                    $loinc_que_code = $_POST[$psy_que];// always present
                    $loinc_answer_list_id = $_POST[$psy_al];// always present
                    if (strstr($psy_element, "score")) {
                        $loinc_ans_code = 'SCORE';
                        $loinc_ans_value = $_POST[$psy_ans];
                    } else {
                        if (strstr($_POST[$psy_ans], "~")) {
                            $loinc_ans_code_array = explode("~", $_POST[$psy_ans]) ;
                            $loinc_ans_code = $loinc_ans_code_array [0];
                            $loinc_ans_value = $loinc_ans_code_array [1];
                        } else {// to accommodate text box values with no answer codes, will use OEA suffix
                            $loinc_ans_code = 'OEA'; // generating fake answer code
                            $loinc_ans_value = $_POST[$psy_ans];
                        }
                    }
                    
                    $sql = "INSERT INTO psychosocial_behavior_data
                            (
                            date,
                            pid,
                            loinc_que_code,
                            loinc_answer_list_id,
                            loinc_ans_code,
                            loinc_ans_value,
                            author
                            )
                            VALUES
                            (?,?,?,?,?,?,?)";
                    sqlStatement($sql, array($post_date, $post_pid, $loinc_que_code, $loinc_answer_list_id, $loinc_ans_code, $loinc_ans_value, $author));
                }
            } // inner foreachloop
        } // outer foreach loop
    } elseif ($crud == 'edit') {
        //the values from the db table are passed in $_POST["str_value_array"] as a serialzed and base64_encoded string
        $str_value_array = $_POST["str_value_array"];
        $value_array_array = unserialize(base64_decode($str_value_array));//creates a 2-dimensional array that has the data that exists in the database
                    
        //Define the SQL statements
            
        $sql_insert = "INSERT INTO psychosocial_behavior_data
                    (
                    date,
                    pid,
                    loinc_que_code,
                    loinc_answer_list_id,
                    loinc_ans_code,
                    loinc_ans_value,
                    author
                    )
                    VALUES
                    (?,?,?,?,?,?,?)";
            
        $sql_update = "UPDATE psychosocial_behavior_data
                    SET
                    date = ?,
                    pid = ?,
                    loinc_que_code = ?,
                    loinc_answer_list_id = ?,
                    loinc_ans_code = ?,
                    loinc_ans_value = ?,
                    author = ?
                    WHERE
                    id = ?";
            
        $sql_inactivate = "UPDATE psychosocial_behavior_data
                    SET 
                    active = ?
                    WHERE
                    id = ?";
            
        $sql_delete = "DELETE psychosocial_behavior_data
                    WHERE
                    id = ?";
            
        foreach ($psy_combo_array as $psy_arrays) {
            foreach ($psy_arrays as $psy_element) {
                $exist_id = $value_array_array[$psy_element][exist_id];
                $psy_ans = strstr($psy_element, "score")? $psy_element : $psy_element ."_a";
                $psy_que = $psy_element . "_q";
                $psy_al = $psy_element . "_al";
                $loinc_que_code = null;
                $loinc_answer_list_id = null;
                $loinc_ans_code = null;
                $loinc_ans_value = null;
                $loinc_que_code = $_POST[$psy_que];// always present
                $loinc_answer_list_id = $_POST[$psy_al];// always present
                // 4 scenarios
                //		- POST ans present existing DB answer same as POST - do nothing - needs id
                //		- POST ans present existing DB answer different - update existing DB answer - needs id
                //		- POST ans present existing DB answer not present - insert new answer
                //		- POST ans absent existing DB answer present - delete (inactivate) existing DB answer - needs id
                                    
                if (!empty($_POST[$psy_ans]) || $_POST[$psy_ans] === '0') {// for first 3 scenarios
                        
                    if (strstr($psy_element, "score")) {
                        $loinc_ans_code = 'SCORE';
                        $loinc_ans_value = $_POST[$psy_ans];
                    } elseif (strstr($psy_element, "physical")) {
                        $loinc_ans_code = 'OEA';
                        $loinc_ans_value = $_POST[$psy_ans];
                    } else {
                        $loinc_ans_code_array = explode("~", $_POST[$psy_ans]) ;
                        $loinc_ans_code = $loinc_ans_code_array [0];
                        $loinc_ans_value = $loinc_ans_code_array [1];
                    }
                    
                    if ($loinc_que_code == $value_array_array[$psy_element]['exist_que']) {//question exists in both POST and db
                        if ($loinc_ans_code == 'SCORE' || $loinc_ans_code == 'OEA') {//to account for score values, check answer values
                                
                            if ($loinc_ans_value != $value_array_array[$psy_element]['exist_val']) {//score has changed, POST ans present existing DB answer different - update existing DB answer
                                sqlStatement($sql_update, array($post_date, $post_pid, $loinc_que_code, $loinc_answer_list_id,
                                $loinc_ans_code, $loinc_ans_value, $author, $exist_id));
                            }
                        } elseif ($loinc_ans_code != $value_array_array[$psy_element]['exist_ans']) { //POST ans present existing DB answer different - update existing DB answer
                            sqlStatement($sql_update, array($post_date, $post_pid, $loinc_que_code, $loinc_answer_list_id,
                            $loinc_ans_code, $loinc_ans_value, $author, $exist_id));
                        }
                    } elseif (is_null($value_array_array[$psy_element]['exist_que'])) {//POST ans present existing DB answer not present - insert new answer
                        sqlStatement($sql_insert, array($post_date, $post_pid, $loinc_que_code, $loinc_answer_list_id,
                        $loinc_ans_code, $loinc_ans_value, $author));
                    }
                } elseif ($value_array_array[$psy_element]['exist_ans']) {//post ans absent existing DB answer present - delete existing DB answer
                    sqlStatement($sql_inactivate, array(0, $exist_id));
                    //sqlStatement($sql_delete, array($exist_id)); //Which one??
                }
            } // inner foreachloop
        } // outer foreach loop
    }
} elseif (isset($_POST['form_delete'])) {
 //Remember this script gets both GET and POST data, need to verify CSRF token appropriately for each type of request
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }
    $psy_ans_array = array("financial", "education", "stress", "depression_1" , "depression_2", "depression_3",
    "physical_activity_1", "physical_activity_2", "alcohol_1", "alcohol_2", "alcohol_3", "alcohol_4",
    "social_1", "social_2", "social_3", "social_4", "social_5", "violence_1", "violence_2", "violence_3", "violence_4");
        
    $psy_score_array = array("depression_score", "alcohol_score", "social_score", "violence_score");
        
    $psy_combo_array = array($psy_ans_array, $psy_score_array);
        
    $str_value_array = $_POST["str_value_array"];
    $value_array_array = unserialize(base64_decode($str_value_array));//creates a 2-dimensional array that has the data that exists in the database
    // will remove entry by inactivating rather than deleting
    $sql_inactivate = "UPDATE psychosocial_behavior_data
						SET 
						active = ?
						WHERE
						id = ?";
        
    foreach ($psy_combo_array as $psy_arrays) {
        foreach ($psy_arrays as $psy_element) {
            $exist_id = $value_array_array[$psy_element][exist_id];
            if ($exist_id) {
                sqlStatement($sql_inactivate, array(0, $exist_id));
            }
        }
    }
}
    
if ($_POST['form_save'] || $_POST['form_delete']) {// once posted form HAS to close, always needs to open with $_GET data, NOT allowed to re-POST
    echo "<script language='JavaScript'>\n";//RP_Uncomment to close form in production
    echo "console.log(document.referrer);\n";
    echo "parent.dlgclose();\n";
        
    echo "</script></body></html>\n";
    exit();
}
?>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <form name="psychosocial-behavior" id="psychosocial-behavior" action="snapshot_detail_1_add.php" method="post">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />
                    <input type='hidden' name='form_pid' id='form_pid' value='<?php echo $get_pid; ?>'>
                    <br>
                    <fieldset>
                    <legend><?php echo  xlt("Snapshot") . " - " . getPatientNameFirstLast($_SESSION['pid']) . " - " . $get_date ?></legend>
                        <div class="col-sm-12 hide_div" id="finan">
                            <h4><?php echo xlt("Financial resource strain"); ?></h4>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("How hard is it for you to pay for the very basics like food, housing, medical care, and heating"); ?>?
                                <input type='hidden' name='financial_q' id='financial_q' value='76513-1'>
                                <input type='hidden' name='financial_al' id='financial_al' value='LL3266-5'>
                            </div>
                            <div class="col-sm-4">
                                <select name='financial_a' id='financial_a' class='form-control'>
                                    <?php
                                    $key_name = 'financial'; // used to get value in edit mode
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%financial%' 
                                    ORDER BY seq DESC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 hide_div" id="educa">
                            <h4> <?php echo xlt("Education"); ?></h4>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("What is the highest grade or level of school you have completed or the highest degree you have received"); ?>?
                                <input type='hidden' name='education_q' id='education_q' value='63504-5'>
                                <input type='hidden' name='education_al' id='education_al' value='LL1069-5'>
                            </div>
                            <div class="col-sm-4">
                                <select name='education_a' id='education_a' class='form-control'>
                                    <?php
                                    $key_name = 'education'; // used to get value in edit mode
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%education%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 hide_div" id="stres">
                            <h4> <?php echo xlt("Stress"); ?></h4>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("These days do you feel stress - tense, restless, nervous, or anxious, or 
                                    unable to sleep at night because your mind is troubled all the time");
                                    ?>?
                                <input type='hidden' name='stress_q' id='stress_q' value='76542-0'>
                                <input type='hidden' name='stress_al' id='stress_al' value='LL3267-3'>
                            </div>
                            <div class="col-sm-4">
                                <select name='stress_a' id='stress_a' class='form-control'>
                                    <?php
                                    $key_name = 'stress'; // used to get value in edit mode
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%stress%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 hide_div" id="depre">
                            <h4> <?php echo xlt("Depression"); ?></h4>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Little interest or pleasure in doing things in last 2 week"); ?>?
                                <input type='hidden' name='depression_1_q' id='depression_1_q' value='44250-9'>
                                <input type='hidden' name='depression_1_al' id='depression_1_al' value='LL358-3'>
                            </div>
                            <div class="col-sm-4">
                                <select name='depression_1_a' id='depression_1_a' class='form-control depression calculate_score'>
                                    <?php
                                    $key_name = 'depression_1';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%depression%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Feeling down, depressed, or hopeless in last 2 weeks"); ?>?
                                <input type='hidden' name='depression_2_q' id='depression_2_q' value='44255-8'>
                                <input type='hidden' name='depression_2_al' id='depression_2_al' value='LL358-3'>
                            </div>
                            <div class="col-sm-4">
                                <select name='depression_2_a' id='depression_2_a' class='form-control depression calculate_score'>
                                    <?php
                                    $key_name = 'depression_2';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%depression%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Total score reported"); ?>:
                                <input type='hidden' name='depression_score_q' id='depression_score_q' value='55758-7'>
                                <input type='hidden' name='depression_score_al' id='depression_score_al' value='OE-AL-DEP-01'>
                            </div>
                            <div class="col-sm-4">
                                <?php
                                    $key_name = 'depression_score';
                                    $score_val = null;
                                foreach ($value_array as $key => $val) {
                                    if ($key == $key_name) {
                                        $score_val = $val;
                                        break;
                                    }
                                }
                                ?>
                                <input type='text' name='depression_score' id='depression_score' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                                title='<?php echo xlt("Automatically calculated based on above selected values");?>' value='<?php echo $score_val;?>'>
                            </div>
                        </div>
                        <div class="col-sm-12 hide_div" id="physi">
                            <h4> <?php echo xlt("Physical activity"); ?></h4>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("How many days of moderate to strenuous exercise, like a brisk walk, did you do in the last 7 days"); ?>?
                                <input type='hidden' name='physical_activity_1_q' id='physical_activity_1_q' value='68515-6'>
                                <input type='hidden' name='physical_activity_1_al' id='physical_activity_1_al' value='OE-AL-PHY-01'>
                            </div>
                            <div class="col-sm-4">
                                <?php
                                    $key_name = 'physical_activity_1';
                                    $score_val = null;
                                foreach ($value_array as $key => $val) {
                                    if ($key == $key_name) {
                                        $score_val = $val;
                                        break;
                                    }
                                }
                                ?>
                                <input type='text' name='physical_activity_1_a' id='physical_activity_1_a' class='form-control week_only' value=<?php echo $score_val;?>>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("On those days that you engage in moderate to strenuous exercise, how many minutes, on average, do you exercise"); ?>?
                                <input type='hidden' name='physical_activity_2_q' id='physical_activity_2_q' value='68516-4'>
                                <input type='hidden' name='physical_activity_2_al' id='physical_activity_2_al' value='OE-AL-PHY-02'>
                            </div>
                            <div class="col-sm-4">
                                <?php
                                    $key_name = 'physical_activity_2';
                                    $score_val = null;
                                foreach ($value_array as $key => $val) {
                                    if ($key == $key_name) {
                                        $score_val = $val;
                                        break;
                                    }
                                }
                                ?>
                                <input type='text' name='physical_activity_2_a' id='physical_activity_2_a' class='form-control num_only' value=<?php echo $score_val;?>>
                            </div>
                        </div>
                        <div class="col-sm-12 hide_div" id="alcoh">
                            <h4> <?php echo xlt("Alcohol Use Disorder Identification Test"); ?></h4>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("How often do you have a drink containing alcohol"); ?>?
                                <input type='hidden' name='alcohol_1_q' id='alcohol_1_q' value='68518-0'>
                                <input type='hidden' name='alcohol_1_al' id='alcohol_1_al' value='LL2179-1'>
                            </div>
                            <div class="col-sm-4">
                                <select name='alcohol_1_a' id='alcohol_1_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'alcohol_1';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Alcohol_Use_Disorder_Identification_Test-1%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("How many standard drinks containing alcohol do you have on a typical day"); ?>?
                                <input type='hidden' name='alcohol_2_q' id='alcohol_2_q' value='68519-8'>
                                <input type='hidden' name='alcohol_2_al' id='alcohol_2_al' value='LL2180-9'>
                            </div>
                            <div class="col-sm-4">
                                <select name='alcohol_2_a' id='alcohol_2_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'alcohol_2';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Alcohol_Use_Disorder_Identification_Test-2%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("How often do you have 6 or more drinks on 1 occasion"); ?>?
                                <input type='hidden' name='alcohol_3_q' id='alcohol_3_q' value='68520-6'>
                                <input type='hidden' name='alcohol_3_al' id='alcohol_3_al' value='LL2181-7'>
                            </div>
                            <div class="col-sm-4">
                                <select name='alcohol_3_a' id='alcohol_3_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'alcohol_3';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Alcohol_Use_Disorder_Identification_Test-3%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Total score"); ?>:
                                <input type='hidden' name='alcohol_score_q' id='alcohol_score_q' value='75626-2'>
                                <input type='hidden' name='alcohol_score_al' id='alcohol_score_al' value='OE-AL-ALC-01'>
                            </div>
                            <div class="col-sm-4">
                                <?php
                                    $key_name = 'alcohol_score';
                                    $score_val = null;
                                foreach ($value_array as $key => $val) {
                                    if ($key == $key_name) {
                                        $score_val = $val;
                                        break;
                                    }
                                }
                                ?>
                                <input type='text' name='alcohol_score' id='alcohol_score' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                                title='<?php echo xlt("Automatically calculated based on above selected values");?>' value='<?php echo $score_val;?>'>
                            </div>
                        </div>
                        <div class="col-sm-12 hide_div" id="socia">
                            <h4> <?php echo xlt("Social connection and isolation panel"); ?></h4>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Are you now married, widowed, divorced, separated, never married or living with a partner"); ?>?
                                <input type='hidden' name='social_1_q' id='social_1_q' value='63503-7'>
                                <input type='hidden' name='social_1_al' id='social_1_al' value='LL1068-7'>
                            </div>
                            <div class="col-sm-4">
                                <select name='social_1_a' id='social_1_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'social_1';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', notes) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Social_connection_and_isolation_panel%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("In a typical week, how many times do you talk on the telephone with family, friends, or neighbors"); ?>?
                                <input type='hidden' name='social_2_q' id='social_2_q' value='76508-1'>
                                <input type='hidden' name='social_2_al' id='social_2_al' value='OE-AL-SOC-01'>
                            </div>
                            <div class="col-sm-4">
                                <select name='social_2_a' id='social_2_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'social_2';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', notes) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Social_Contact%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("How often do you get together with friends or relatives"); ?>?
                                <input type='hidden' name='social_3_q' id='social_3_q' value='76509-9'>
                                <input type='hidden' name='social_3_al' id='social_3_al' value='OE-AL-SOC-02'>
                            </div>
                            <div class="col-sm-4">
                                <select name='social_3_a' id='social_3_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'social_3';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', notes) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Social_Contact%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("How often do you attend church or religious services per year"); ?>?
                                <input type='hidden' name='social_4_q' id='social_4_q' value='76510-7'>
                                <input type='hidden' name='social_4_al' id='social_4_al' value='OE-AL-SOC-03'>
                            </div>
                            <div class="col-sm-4">
                                <select name='social_4_a' id='social_4_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'social_4';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', notes) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Religious_Services%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Do you belong to any clubs or organizations such as church groups unions, fraternal or athletic groups, or school groups"); ?>?
                                <input type='hidden' name='social_5_q' id='social_5_q' value='76511-5'>
                                <input type='hidden' name='social_5_al' id='social_5_al' value='LL963-0'>
                            </div>
                            <div class="col-sm-4">
                                <select name='social_5_a' id='social_5_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'social_5';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Exposure_to_violence%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Social isolation score"); ?>:
                                <input type='hidden' name='social_score_q' id='social_score_q' value='76512-3'>
                                <input type='hidden' name='social_score_al' id='social_score_al' value='OE-AL-SOC-04'>
                            </div>
                            <div class="col-sm-4">
                                <?php
                                    $key_name = 'social_score';
                                    $score_val = null;
                                foreach ($value_array as $key => $val) {
                                    if ($key == $key_name) {
                                        $score_val = $val;
                                        break;
                                    }
                                }
                                ?>
                                <input type='text' name='social_score' id='social_score' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                                title='<?php echo xlt("Automatically calculated based on above selected values");?>' value='<?php echo $score_val;?>'>
                            </div>
                        </div>
                        <div class="col-sm-12 hide_div" id="viole">
                            <h4> <?php echo xlt("Exposure to violence") . " (" . xlt("intimate partner violence") . ")"; ?></h4>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Within the last year, have you been humiliated or emotionally abused in other ways by your partner or ex-partner"); ?>?
                                <input type='hidden' name='violence_1_q' id='violence_1_q' value='76500-8'>
                                <input type='hidden' name='violence_1_al' id='violence_1_al' value='LL963-0'>
                            </div>
                            <div class="col-sm-4">
                                <select name='violence_1_a' id='violence_1_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'violence_1';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Exposure_to_violence%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Within the last year, have you been afraid of your partner or ex-partner"); ?>?
                                <input type='hidden' name='violence_2_q' id='violence_2_q' value='76501-6'>
                                <input type='hidden' name='violence_2_al' id='violence_2_al' value='LL963-0'>
                            </div>
                            <div class="col-sm-4">
                                <select name='violence_2_a' id='violence_2_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'violence_2';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Exposure_to_violence%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Within the last year, have you been raped or forced to have any kind of sexual activity by your partner or ex-partner"); ?>?
                                <input type='hidden' name='violence_3_q' id='violence_3_q' value='76502-4'>
                                <input type='hidden' name='violence_3_al' id='violence_3_al' value='LL963-0'>
                            </div>
                            <div class="col-sm-4">
                                <select name='violence_3_a' id='violence_3_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'violence_3';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Exposure_to_violence%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Within the last year, have you been kicked, hit, slapped, or otherwise physically hurt by your partner or ex-partner"); ?>?
                                <input type='hidden' name='violence_4_q' id='violence_4_q' value='76503-2'>
                                <input type='hidden' name='violence_4_al' id='violence_4_al' value='LL963-0'>
                            </div>
                            <div class="col-sm-4">
                                <select name='violence_4_a' id='violence_4_a' class='form-control calculate_score'>
                                    <?php
                                    $key_name = 'violence_4';
                                    echo "<option value = ''> --- ". xlt("Select value") . " ---</option>";
                                    $result = sqlStatement(
                                        "SELECT title,
                                            CONCAT(codes, '~', seq) AS value,
                                            seq
                                    FROM list_options 
                                    WHERE list_id LIKE '%Exposure_to_violence%' 
                                    ORDER BY seq ASC"
                                    );
                                    while ($row = sqlFetchArray($result)) {
                                        echo "<option value='" . attr($row['value']) . "'";
                                        foreach ($value_array as $key => $val) {
                                            if ($val == $row['value'] && $key == $key_name) {
                                                echo " selected";
                                                break;
                                            }
                                        }
                                        echo ">" . text($row['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <p> <?php echo xlt("Total score"); ?>:
                                <input type='hidden' name='violence_score_q' id='violence_score_q' value='76504-0'>
                                <input type='hidden' name='violence_score_al' id='violence_score_al' value='OE-AL-VIO-01'>
                            </div>
                            <div class="col-sm-4">
                                <?php
                                    $key_name = 'violence_score';
                                    $score_val = null;
                                foreach ($value_array as $key => $val) {
                                    if ($key == $key_name) {
                                        $score_val = $val;
                                        break;
                                    }
                                }
                                ?>
                                <input type='text' name='violence_score' id='violence_score' class='form-control'  readonly='readonly' style='cursor: no-drop;' 
                                title='<?php echo xlt("Automatically calculated based on above selected values");?>' value='<?php echo $score_val;?>'>
                            </div>
                        </div>
                    </fieldset>
                    <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
                        <div class="form-group clearfix" id="button-container">
                            <div class="col-sm-12 text-left position-override">
                                <div class="btn-group btn-group-pinch" role="group">
                                    <button type='submit' name='form_save'  id='form_save' class="btn btn-default btn-save"   value='<?php echo xla('Save');?>'><?php echo xlt('Save'); ?></button>
                                    <button type="button" class="btn btn-link btn-cancel btn-separate-left" onclick='parent.dlgclose()';><?php echo xlt('Cancel');?></button>
                                    <?php if (acl_check('admin', 'practice') && $get_type == 'edit') {?>
                                        <button type='submit' name='form_delete' id='form_delete' class="btn btn-default btn-cancel btn-delete btn-separate-left" onclick='return confirm("<?php echo xla("Are you sure you want to delete?"); ?>")' value='<?php echo xla('Delete'); ?>'><?php echo xlt('Delete'); ?></button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <input type='hidden' name='crud' id='crud' value='<?php echo $get_type;?>'>
                    <input type='hidden' name='post_date' id='post_date' value='<?php echo $get_date;?>'>
                    <input type='hidden' name='author' id='author' value='<?php echo $get_author;?>'>
                    <input type="hidden" id="str_value_array" name="str_value_array"  value="<?php print base64_encode(serialize($value_array_post_q)); ?>">
                </form>
            </div>
        </div
    </div> <!--end of container div -->
<script>
    $(document).ready(function(){
    // to ensure 0 Total score if alcohol_1_a select is set to never with value of 0
    // the other two selects should not have any value
        var alcohol_1Value = $('#alcohol_1_a').val();
        alcohol_1Value = alcohol_1Value.split("~");
        alcohol_1Value = alcohol_1Value[1];
               
        if (Number(alcohol_1Value) == 0) {
            $("#alcohol_2_a option").prop("selected", false);
            $('#alcohol_2_a').prop('disabled', 'disabled');
            $("#alcohol_3_a option").prop("selected", false);
            $('#alcohol_3_a').prop('disabled', 'disabled');
            $('#alcohol_score').val('0');
        }
        $('#alcohol_1_a').on('change', function () {
            alcohol_1Value = $('#alcohol_1_a').val();
            alcohol_1Value = alcohol_1Value.split("~");
            alcohol_1Value = alcohol_1Value[1];
            if (Number(alcohol_1Value) == 0) {
                $("#alcohol_2_a option").prop("selected", false);
                $('#alcohol_2_a').prop('disabled', 'disabled');
                $("#alcohol_3_a option").prop("selected", false);
                $('#alcohol_3_a').prop('disabled', 'disabled');
                $('#alcohol_score').val('0');
            } else {
                $('#alcohol_2_a').prop('disabled', false);
                $('#alcohol_3_a').prop('disabled', false);
            }
        });
    });
    $(document).ready(function(){
        $('.calculate_score').on('change', function(){
            var str = $(this).attr('id');
            var res = str.substring(str.length - 2, str.length);
            if (res == '_a'){// only those for answers i.e. _a
                sel = str.substring(0, str.length - 4);// only up to 9
            }
            var score =  sel + "_score"; // id of input containing total score
            var elementsNumber = $('[id^=' + sel + '].calculate_score').length; //number of elements of this type to target
            var total_score = 0;
            var blank_score = 0;
            var total_score_string = '';
            var i;
            var j = 1;
            for (i = 1; i < elementsNumber + 1; i++){
                var ans = $('#' + sel + '_' + i + '_a').val(); // gets value of elements
                if(!ans){ 
                
                blank_score = parseInt(blank_score, 10) + 1;
                }
                var ansValue = ans.split('~')[1];
                
                if (typeof ansValue === "string"){
                    total_score = parseInt(total_score, 10) + parseInt(ansValue, 10);
                    if (parseInt(total_score, 10) == 5){//done as max score is 4 for Social isolation https://r.details.loinc.org/LOINC/76512-3.html?sections=Comprehensive
                       total_score = 4;
                    }
                }
            }
            
            if(blank_score == elementsNumber){
                $('#' + score).val(''); // done to account for when all select boxes have no value then score is blank
            } else {
                $('#' + score).val( <?php echo xlj('Total score');?>  + " = "); //To briefly (500 msec) show that a calculation is taking place
                setTimeout(function() {
                $('#' + score).val(total_score);
            }, 500);
            }
        });
    });
    $(document).ready(function(){
        $('.week_only'). on('change', function(){
            var numbers = /^[0-7]+$/;
            if(!(this.value.match(numbers))) {
            var message1 = <?php echo xlj("is not a whole number between 0 and 7");?>;
            var message2 = <?php echo xlj("Only whole numbers between 0 and 7 are allowed");?>;
            alert( this.value + " " + message1 + String.fromCharCode(10) + message2);
            this.value = '';
          }
        });
        $('.num_only'). on('change', function(){
            var numbers = /^[0-9]+$/;
            if(!(this.value.match(numbers))) {
            var message1 = <?php echo xlj("is not a whole number");?>;
            var message2 = <?php echo xlj("Please enter a whole number");?>;
            alert( this.value + " " + message1 + String.fromCharCode(10) + message2);
            this.value = '';
          }
        });
    });
    $(document).ready(function(){
        var arrShowDiv = <?php echo json_encode($array_show_div); ?>;
        $.each(arrShowDiv, function(index, chunk) {
            $('#' + chunk).removeClass('hide_div');
        })
    });
</script>
<script>
    //jqury-ui tooltip
        $(document).ready(function(){
        <?php
        if ($get_type == 'edit') {?>
           $('#form_save').prop( "title", "<?php echo xla('To show the updated edited values please click Refresh button once after saving this form'); ?>" ).tooltip({
            show: {
                delay: 700,
                duration: 250
            }
            });
           $('#form_delete').prop( "title", "<?php echo xla('Please click Refresh button twice after clicking Delete'); ?>" ).tooltip({
            show: {
                delay: 700,
                duration: 250
            }
           });
        <?php
        } elseif ($get_type == 'add') {?>
            $('#form_save').prop( "title", "<?php echo xla('To show the updated added values please click Refresh button twice after saving this form'); ?>" ).tooltip({
            show: {
                delay: 700,
                duration: 250
            }
           });
        <?php
        }?>
        });
    </script>
</body>
</html>