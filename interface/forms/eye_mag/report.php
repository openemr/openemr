<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");


if ($_REQUEST['target']) {
    //we are printing something.
      $table_name = "form_eye_mag";

    $count = 0;
    $data = formFetch($table_name, $id);
   
    if ($data) {
 
        foreach($data as $key => $value) {
            $$key=$value;
        }
    }
    if ($target =="W") {
        //we are printing the current RX
       
?>
 <table id="SpectacleRx">
                                                <th colspan="9"><?=$fname?><?=$lname?></th>
                                                <tr style="font-style:bold;">
                                                    <td></td>
                                                    <td></td>
                                                    <td>sph</td>
                                                    <td>cyl</td>
                                                    <td>axis</td>
                                                    <td>Prism</td>
                                                    <td>Acuity</td>
                                                    <td rowspan="7" class="right">
                                                        <b style="font-weight:bold;text-decoration:none;">Rx Type</b><br />
                                                        <b id="SingleVision_span">Single<input type=radio value="0" id="RX1" name="RX" class="input-helper--radio input-helper--radio" check="checked" /></b><br />
                                                        <b id="Bifocal_span">Bifocal<input type=radio value="1" id="RX1" name="RX" /></b><br />
                                                        <b id="Trifocal_span" name="Trifocal_span">Trifocal
                                                            <input type=radio value="2" id="RX1" name="RX" /></b><br />
                                                        <b id="Progressive_span">Prog.<input type=radio value="3" id="RX1" name="RX" /></b><br />

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td rowspan="2">Distance</td>    
                                                    <td><b>OD</b></td>
                                                    <td><input type=text id="ODSph" name=="ODSph" /></td>
                                                    <td><input type=text id="ODCyl" name="ODCyl" /></td>
                                                    <td><input type=text id="ODAxis" name="ODAxis" /></td>
                                                    <td><input type=text id="ODPrism" name="ODPrism" /></td>
                                                    <td><input type=text id="ODVA" name="ODVA" /></td>
                                                </tr>
                                                <tr>
                                                    <td><b>OS</b></td>
                                                    <td><input type=text id="OSSph" name="OSSph" /></td>
                                                    <td><input type=text id="OSCyl" name="OSCyl" /></td>
                                                    <td><input type=text id="OSAxis" name="OSAxis" /></td>
                                                    <td><input type=text id="OSPrism" name="OSPrism" /></td>
                                                    <td><input type=text id="OSVA" name="OSVA" /></td>
                                                </tr>
                                                <tr class="NEAR">
                                                    <td rowspan=2><span style="text-decoration:none;">Mid/<br />Near</span></td>    
                                                    <td><b>OD</b></td>
                                                    <td class="Mid nodisplay"><input type=text id="ODADD1" name="ODADD1" value=""></td>
                                                    <td class="Add2"><input type=text id="ODADD2" name="ODADD2" value=""></td>
                                                    <td class="HIDECYL"><input type=text id="ODCYLNEAR" name="ODCYLNEAR" value=""></td>
                                                    <td><input type=text id="ODAXISNEAR" name="ODAXISNEAR" value=""></td>
                                                    <td><input type=text id="ODPRISMNEAR" name="ODPRISMNEAR" value=""></td>
                                                    <td><input type=text id="ODVANear" name="ODVANear" value=""></td>
                                                </tr>
                                                <tr class="NEAR">
                                                    <td><b>OS</b></td>
                                                    <td class="Mid nodisplay"><input type=text id="OSADD1" name="OSADD1" value=""></td>
                                                    <td class="Add2"><input type=text id="OSADD2" name="OSADD2" value=""></td>
                                                    <td class="HIDECYL"><input type=text id="OSCYLNEAR" name"OSCYLNEAR" value=""></td>
                                                    <td><input type=text id="OSAXISNEAR" name="OSAXISNEAR" value=""></td>
                                                    <td><input type=text id="OSPRISMNEAR" name="OSPRISMNEAR" value=""></td>
                                                    <td><input type=text id="OSVANear" name="OSVANear" value=""></td>

                                                </tr>
                                                <tr style="">
                                                    <td colspan="2" class="up" style="text-align:right;vertical-align:top;top:0px;"><b>Comments:</b>
                                                    </td>
                                                    <td colspan="5" class="up" style="text-align:left;vertical-align:middle;top:0px;">
                                                        <textarea style="idth:100%;height:2.1em;" id="COMMENTS" name="COMMENTS"></textarea>     
                                                    </td>
                                                    <td> 
                                                        <span class="ui-icon ui-icon-clock" >&nbsp; </span>
                                                        <span href="print.php?target=W" class="ui-icon ui-icon-cancel" onclick="indow.print(); return false;" style="display:inline-block"></span><span>Print</span> 
                                                    </td>
                                                </tr>
                                            </table>


<?
        }
}
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
