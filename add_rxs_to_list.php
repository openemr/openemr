<?php
/**
 * Created by IntelliJ IDEA.
 * User: SCorpio
 * Date: 10/31/2017
 * Time: 3:07 PM
 */

require_once("interface/globals.php");
session_start();

if(isset($_REQUEST['check_list'])){

    //do:add to prescti_va_list
    $prescriptions= $_REQUEST['check_list'];


    echo "<form name='form1' id='form1' method='post'>
            <label for='listname'>Favorite List Name</label>
            <input id='listname' name='listname' placeholder='Write your List Name' type='text'> </input>";


    foreach ($prescriptions as $pre_id){
       echo "<input type='hidden' name='favlist[]' value='$pre_id'> </input>";
    }

    echo "</form>";

echo "<button type=\"submit\" form=\"form1\" value=\"Submit\">ADD</button>";

    
}else if (isset($_REQUEST['favlist'])&&!empty($_REQUEST['favlist'])){

    if(isset($_REQUEST['listname'])){
        $prescriptions= $_REQUEST['favlist'];

        if (! isset($_SESSION['authUserID'])) {
            die('login proplem');
        }

        sqlStatement("DELETE FROM `prescription_fav_list` WHERE `list_name` LIKE '%".$_REQUEST['listname']."%' ");
        foreach ($prescriptions as $pre_id) {
            sqlStatement("INSERT INTO `prescription_fav_list` (`list_id`, `presc_id`, `provider_id`, `list_name`)  VALUES (NULL , ?, ?, ?)", array($pre_id, $_SESSION['authUserID'],$_REQUEST['listname']));
        }

    }else{
        die('Please Write name for your List'); // need to go back to list
    }

}
else{

    echo "please select the prescriptions first"; // need to go back to list
}
