<?php


include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");


if ($_REQUEST['target']) {
    //we are printing something.
      $table_name = "form_eye_mag";
//echo "<pre>hello";
if (!$_POST['encounter']) $encounter = $_SESSION['encounter'];
//var_dump(($_SESSION));
//the formid is found in table "forms".  Here all forms associated with an "$encounter" are listed.
//to get the correct data from the visit, 
//get all the data where encounter = $encounter and forms.formid=f0rm_eye_mag.id
$query = "SELECT * FROM form_eye_mag JOIN forms on forms.form_id = form_eye_mag.id where forms.encounter='".$encounter."'";

//echo $query."<br />";
$data =  sqlQuery($query);
//var_dump($data);
@extract($data);
    if ($target =="W") {
        //we are printing the current RX
        //WODSPH    WODCYL  WODAXIS     WODADD1     WODADD2     WODPRISM    WODBASE 
        //WOSSPH    WOSCYL  WOSAXIS     WOSADD1     WOSADD2     WOSPRISM    WOSBASE     
        //WODCYLNEAR  WODAXISNEAR     WODPRISMNEAR    WODBASENEAR     
        //WOSCYLNEAR  WOSAXISNEAR     WOSPRISMNEAR    WOSBASENEAR     
        //WCOMMENTS
        $ODSPH = $WODSPH;
        $ODAXIS = $WODAXIS;
        $ODCYL = $WODCYL;
        $ODPRISM = $WODPRISM;
        $OSSPH = $WOSSPH;
        $OSCYL = $WOSCYL;
        $OSAXIS = $WOSAXIS;
        $OSPRISM = $WOSPRISM;
        $COMMENTS = $WCOMMENTS; 
        $ODADD1 = $WODADD1;
        $ODADD2 = $WODADD2;
        $OSADD1 = $WODADD1;
        $OSADD2 = $WODADD2;
        if ($ODADD1) {
            //this is a trifocal
            $trifocal ='checked="checked"';
        } else if ($ODADD2){
//            bifocal or prog 
            $bifocal ='checked="checked"';
        } else {
           // single vision;
            $single='checked="checked"';
        }

} else if ($target =="AR") {
        $ODSPH = $ARODSPH;
        $ODAXIS = $ARODAXIS;
        $ODCYL = $ARODCYL;
        $ODPRISM = $ARODPRISM;
        $OSSPH = $AROSSPH;
        $OSCYL = $AROSCYL;
        $OSAXIS = $AROSAXIS;
        $OSPRISM = $AROSPRISM;
        $COMMENTS = $ARCOMMENTS; 
}else if ($target =="MR") {
        $ODSPH = $MRODSPH;
        $ODAXIS = $MRODAXIS;
        $ODCYL = $MRODCYL;
        $ODPRISM = $MRODPRISM;
        $OSSPH = $MROSSPH;
        $OSCYL = $MROSCYL;
        $OSAXIS = $MROSAXIS;
        $OSPRISM = $MROSPRISM;
        $COMMENTS = $MRCOMMENTS; 
}else if ($target =="CR") {
        $ODSPH = $CRODSPH;
        $ODAXIS = $CRODAXIS;
        $ODCYL = $CRODCYL;
        $ODPRISM = $CRODPRISM;
        $OSSPH = $CROSSPH;
        $OSCYL = $CROSCYL;
        $OSAXIS = $CROSAXIS;
        $OSPRISM = $CROSPRISM;
        $COMMENTS = $CRCOMMENTS; 
}else if ($target =="CR") {
        $ODSPH = $CTLODSPH;
        $ODAXIS = $CTLODAXIS;
        $ODCYL = $CTLODCYL;
        $ODPRISM = $CTLODPRISM;
        $OSSPH = $CTLOSSPH;
        $OSCYL = $CTLOSCYL;
        $OSAXIS = $CTLOSAXIS;
        $OSPRISM = $CTLOSPRISM;
        $COMMENTS = $CTLCOMMENTS; 
}
}
$form_name = "eye_mag";

/** CHANGE THIS to match the folder you created for this form **/
$form_folder = "eye_mag";

formHeader("Form: ".$form_name);

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';


$objIns = formFetch("form_eye_mag", $_GET["id"]);  
//#Use the formFetch function from api.inc to get values for existing form. 
@extract($objIns);


//echo "<pre>";

$query = "SELECT * FROM patient_data where pid='$pid'";
$pat_data =  sqlQuery($query);
//var_dump($pat_data);
//@extract($pat_data);

$query = "SELECT * FROM users where id = '".$_SESSION['authUserID']."'";
$prov_data =  sqlQuery($query);
//var_dump($prov_data);
//@extract($prov_data);
//$providerID = $prov_data['fname']." ".$prov_data['lname'];

$query  = "SELECT * FROM dbSelectFindings where PEZONE='PREFS' AND id='".$_SESSION['authUserID']."' ORDER BY ZONE_ORDER,ordering";

                                           $result = sqlStatement($query);
                                           $number_rows=0;
                                           while ($prefs= mysql_fetch_array($result))   {
                                                @extract($prefs);
                                                $$LOCATION = $VALUE;
                                          // echo $LOCATION ." --- ". $$LOCATION."<BR />";
                                            }
?>

<html><head>
    <style>
    
    .refraction {
    top:1in;
    float:left;
    min-height:1.8in;
    border: 1.00pt solid #000000; 
    padding: 0.0in; 
    box-shadow: 10px 10px 5px #888888;
    border-radius: 8px;
    margin: 5 auto;
    margin-right: 4px; 
    width:5.5in;
}
.refraction td {
    text-align:center;
    font-size:8pt;
    
    width:0.35in;
    vertical-align: text-middle;
    text-decoration: none;
}
table {
    
    color:white;
    font-size: 0.8em;
    padding: 2px;
    color: black;
    width:5.5in;
    vertical-align: text-top;
}

input[type=text] {
    text-align: right;
    width:80px;
}
.refraction  b{
    text-decoration:bold;
}
.refraction td.right {
    text-align: right;
    text-decoration: none;
    width:0.7in;
    vertical-align: text-top;
}

.right {
    text-align:right;
    vertical-align: text-top;
    
}
.left {
    vertical-align: text-top;
}
.title {
    font-size: 0.9em;
    font-weight:normal;
}
</style>
</head><body>
<table class="title">
    <tr>
        <th colspan="3">
            <br><?=$prov_data[facility]?><br />
                            <?=$prov_data[street]?><br />
                <?=$prov_data[city]?>, <?=$prov_data[state]?> &nbsp;&nbsp;<?=$prov_data[zip]?><br />
            Phone: <?=$prov_data[phone]?><br />
            Fax: <?=$prov_data[fax]?><br />
        </th>
    </tr>
    <tr><td class="right">Name: 
        <td class="left"><?=$pat_data[fname]?> <?=$pat_data[lname]?>
        </td>
        <td class="right">Date: <u><? echo date('F jS\,  Y'); ?></u>
        </td>
    </tr>
    <tr>
        <td class="right">
            Address:
            </td>
            <td colspan="2"> <?=$pat_data[street]?><br /> <?=$pat_data[city]?>, <?=$pat_data[state]?>
        </td>
    </tr>
    <tr>
        <td colspan="8">
            <table id="SpectacleRx" class="refraction">
        
                <tr style="font-style:bold;">
                    <td></td>
                    <td></td>
                    <td>sph</td>
                    <td>cyl</td>
                    <td>axis</td>
                    <td>Prism</td>
                    
                    <td rowspan="5" class="right">
                        <b style="font-weight:bold;text-decoration:none;">Rx Type</b><br />
                        <b id="SingleVision_span">Single<input type=radio value="0" id="RX1" name="RX" class="input-helper--radio input-helper--radio" <?=$single?> /></b><br />
                        <b id="Bifocal_span">Bifocal<input type=radio value="1" id="RX1" name="RX" <?=$bifocal?>></b><br />
                        <b id="Trifocal_span" name="Trifocal_span">Trifocal
                            <input type=radio value="2" id="RX1" name="RX" <?=$trifocal?>></b><br />
                        <b id="Progressive_span">Prog.<input type=radio value="3" id="RX1" name="RX" /></b><br />

                    </td>
                </tr>
                <tr>
                    <td rowspan="2">Distance</td>    
                    <td><b>OD</b></td>
                    <td><input type=text id="ODSPH" name=="ODSPH" value="<?=$ODSPH?>" /></td>
                    <td><input type=text id="ODCYL" name="ODCYL" value="<?=$ODCYL?>" /></td>
                    <td><input type=text id="ODAXIS" name="ODAXIS" value="<?=$ODAXIS?>" /></td>
                    <td><input type=text id="ODPRISM" name="ODPRISM" value="<?=$ODPRISM?>" /></td>
                </tr>
                <tr>
                      
                    <td><b>OS</b></td>
                    <td><input type=text id="OSSPH" name=="OSSPH" value="<?=$OSSPH?>" /></td>
                    <td><input type=text id="OSCYL" name="OSCYL" value="<?=$OSCYL?>" /></td>
                    <td><input type=text id="OSAXIS" name="OSAXIS" value="<?=$OSAXIS?>" /></td>
                    <td><input type=text id="OSPRISM" name="OSPRISM" value="<?=$OSPRISM?>" /></td>
                </tr>

                <tr class="NEAR">
                    <td rowspan=2><span style="text-decoration:none;">Mid/<br />Near</span></td>    
                    <td><b>OD</b></td>
                    <td class="WMid nodisplay"><input type=text id="ODADD1" name="ODADD1" value="<?=$ODADD1?>"></td>
                    <td class="WAdd2"><input type=text id="ODADD2" name="ODADD2" value="<?=$ODADD2?>"></td>
                </tr>
                <tr class="NEAR">
                    <td><b>OS</b></td>
                    <td class="WMid nodisplay"><input type=text id="OSADD1" name="OSADD" value="<?=$OSADD1?>"></td>
                    <td class="WAdd2"><input type=text id="OSADD2" name="OSADD2" value="<?=$OSADD2?>"></td>
                    
                    
                </tr>

                <tr style="">
                    <td colspan="2" class="up" style="text-align:right;vertical-align:top;top:0px;"><b>Comments:</b>
                    </td>
                    <td colspan="4" class="up" style="text-align:left;vertical-align:middle;top:0px;">
                        <textarea style="width:100%;height:2.1em;" id="COMMENTS" name="COMMENTS"><?=$COMMENTS?></textarea>     
                    </td>
                    <td> 
                        <span class="ui-icon ui-icon-clock" >&nbsp; </span>
                        <span href="print.php?target=W" class="ui-icon ui-icon-cancel" onclick="indow.print(); return false;" style="display:inline-block"></span><span>Print</span> 
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="6" style="margin:25 auto;padding:20px;text-align:center;">Provider: <?=$pat_data[fname]?> <?=$pat_data[lname]?>, <?=$prov_data['title']?></br>
            <small>e-signed <input type="checkbox" checked="checked">
        </td>
    </tr>
</table>

<?

exit;

/** CHANGE THIS, the name of the function is significant and  **
 **              must be changed to match the folder name     **/
function eye_mag_report( $pid, $encounter, $cols, $id) {
    
    /** CHANGE THIS - name of the database table associated with this form **/
    $table_name = "form_eye_mag";

    $count = 0;
    $data = formFetch($table_name, $id);
   
    if ($data) {
 
        print "<table><tr>";
       
        foreach($data as $key => $value) {
            if ($key == "id" || $key == "pid" || $key == "user" || 
                $key == "groupname" || $key == "authorized" || 
                $key == "activity" || $key == "date" || 
                $value == "" || $value == "0000-00-00 00:00:00" || 
                $value == "n") 
            {
                // skip certain fields and blank data
            continue;
            }

            $key=ucwords(str_replace("_"," ",$key));
            print("<tr>\n");  
            print("<tr>\n");  
            print "<td><span class=bold>$key: </span><span class=text>$value</span></td>";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }
    print "</tr></table>";
}

?> 
