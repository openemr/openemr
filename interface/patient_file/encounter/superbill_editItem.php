<?php

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/formdata.inc.php");

ini_set("display_errors","1");

$cD = array("id"=>'','code_text'=>'','code'=>'','units'=>'','fee'=>'','modifier'=>'','active'=>'1', 'financial_reporting'=>0, 'reportable'=>0);
        
if($_GET['code_external']){
//print_r($_GET); 
        $ct_id = $_GET['code_type_name_external'];
        $code_type_name_external = $_GET['code_type_name_external'];
        $code_external = $_GET['code'];
        $codeID = $_GET['code'];
        $cDRes = return_code_information($code_type_name_external,$code_external,false); // only will return one item
        $cD = sqlFetchArray($cDRes);
        //print_r($cD);
        echo '<h1>WORKING ON EXTERNAL DATA EDIT, it is saving to codes table, but not reflecting in the results or edit screen</h1>';
}else{
        $codeID = intval(trim($_GET['id']));     
        $ct_id = (isset($_GET['ct_id']) ? trim($_GET['ct_id']) : '');
        
        if($codeID != ''){
          $cD = sqlQuery("SELECT * FROM codes WHERE id = ?",array($codeID));
        }
}

$code_types = array();
        $res = sqlStatement("SELECT ct_id,ct_key,ct_label FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key");
        while($row = sqlFetchArray($res)){
                if(trim($row['ct_label']) == '') $row['ct_label'] = $row['ct_key'];
                $code_types[] = $row;
              }



//print_r($cD);
?>
<html>
   <head>
        <script type="text/javascript">
           $(document).ready(function(){
             $("#saveMe").click(function(){
               newDets = $("#editItem").serialize();  
               $.get("superbill_saveItem.php?"+newDets).done(function(data) {  
                  if(data.trim() == "1"){
                    successfuleSave();
                  }else{
                    alert(data)
                  } 
                });
               return false;
             })
           })
           
           function successfuleSave(){
              $("#searchForm").submit();
              alert('Item Saved Successfully');
              $('#fancy_outer').hide();
              $('#fancy_overlay').hide();
              $('#fancy_title').hide();
              $('#fancy_loading').hide();
              $('#fancy_ajax').remove();
              
           }
        </script>
        <style>
               td{
                        cursor: default!important;
               }
        </style>
   </head>
   <body>
        <h2 style="text-align:center"> <?php echo xlt(($codeID == '' ? 'NEW ITEM' : 'EDIT ITEM')); ?></h2>
        <?php if($codeID != ''){ ?>
        <h3 style="text-align: center;"><?= text($cD['code']) ?> - <?= text($cD['code_text']) ?></h3>
        <?php } ?>
        <form id="editItem">
        <table class="tableSorter" width="100%" cellspacing="10">
          <!--<tr>
            <td><?php echo xlt('Code ID'); ?></td>
            <td><?php echo text($cD['id']); ?></td>
          </tr>-->   
          <tr>
            <td><?php echo xlt('Code Type'); ?></td>
            <td>
                <select name="code_type">
                  <?php 
                    foreach($code_types as $ct){
                      echo '<option value="'.attr($ct['ct_id']).'" '.($ct_id == $ct['ct_id'] ? 'selected' : '').'>'.attr($ct['ct_label']).'</option>';
                    }
                  ?>
                </select>
            </td>
          </tr>
          <tr>
            <td><?php echo xlt('Description'); ?></td>
            <td><textarea style="width:100%" name="code_text"><?php echo attr(trim($cD['code_text'])); ?></textarea></td>
          </tr>
          <tr>
            <td><?php echo xlt('Code'); ?></td>
            <td><input style="width:100%" type="text" value="<?php echo attr(trim($cD['code'])); ?>" name="code" /></td>
          </tr>
          <!--
          <tr>
            <td><?php echo xlt('Units'); ?></td>
            <td><input style="width:100%" type="text" value="<?php echo attr(trim($cD['units'])); ?>" name="units" /></td>
          </tr>
          -->
          <tr>
            <td><?php echo xlt('Modifier'); ?></td>
            <td><input style="width:100%" type="text" value="<?php echo attr(trim($cD['modifier'])); ?>" name="modifier" /></td>
          </tr>
          <!-- PRICES HANDELED BY PRICE LISTS NOW -->
          <tr style="display:none">
            <td><?php echo xlt('Standard Price'); ?></td>
            <td><input style="width:100%" type="text" value="<?php echo attr(trim($cD['fee'])); ?>" name="fee" /></td>
          </tr>  
          <tr>
            <td><?php echo xlt('Diagnosis Reporting'); ?></td>
            <td><input type="checkbox" <?php echo (trim($cD['reportable']) == 1 ? 'checked="checked"' : ''); ?> name="reportable" /></td>
          </tr>
          <tr>
            <td><?php echo xlt('Service Reporting'); ?></td>
            <td><input type="checkbox" <?php echo (trim($cD['financial_reporting']) == 1 ? 'checked="checked"' : ''); ?> name="financial_reporting" /></td>
          </tr>
          <tr>
            <td><?php echo xlt('Active'); ?></td>
            <td><input type="checkbox" <?php echo (trim($cD['active']) == 1 ? 'checked="checked"' : ''); ?> name="active" /></td>
          </tr>
          <tr>
            <td colspan="2"><button style="width:100%" id="saveMe">Save</button>
          </tr>
        
        
        </table>                                            
        <input type="hidden" value="<?php echo attr($cD['id'])?>" name="code_id" />
        <input type="hidden" value="<?php echo ($codeID != '' ? 'UpdateItem' : 'AddItem') ?>" name="action" />
        </form>
   </body>
</html>