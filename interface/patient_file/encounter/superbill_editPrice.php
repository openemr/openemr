<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/formdata.inc.php");

//print_r($_GET);

$_GET['id'] = intval($_GET['id']);


$priceLists = array();

$codeDefaultsRes = sqlStatement("SELECT * FROM codes WHERE id = ?",array($_GET['id']));
$codeDefaults = sqlFetchArray($codeDefaultsRes);

//print_r($codeDefaults);

  $priceListNames = array();
  $allPricelistsRes = sqlStatement("SELECT title, option_id FROM list_options WHERE list_id = 'pricelevel' ORDER BY title");
  while($allPricelists = sqlFetchArray($allPricelistsRes)){
    $priceLists[$allPricelists['option_id']] = $codeDefaults['fee'];
    $priceListNames[$allPricelists['option_id']] = $allPricelists['title'];
  }
  $plRes = sqlStatement("SELECT lo.option_id, p.* FROM list_options lo JOIN prices p ON p.pr_level = lo.option_id WHERE lo.list_id = 'pricelevel' AND p.pr_id = ? ORDER BY title",array(intval(trim($_GET['id']))));


  while($row = sqlFetchArray($plRes)){
//   $priceLists[$row['option_id']] = '';
//   $thisPriceRes = sqlStatement('SELECT * FROM prices WHERE pr_id = ? AND pr_level = ?',array($_GET['id'],$row['option_id']));
//   while($r = sqlFetchArray($thisPriceRes)){
//     $priceLists[$row['option_id']] = $r['pr_price'];
//   }
//   if($priceLists[$row['option_id']] == ''){
//     $priceLists[$row['option_id']] = $codeDefaults['fee'];
//   }         
    $priceLists[$row['option_id']] = $row['pr_price'];
  }
?>    
 <div style="height:60px; width:900px; background-color:white; border-bottom:1px solid black; position:fixed">
    <?= xlt('Filter By Price Level') ?> : <input id="priceLevelSearch" /> (<?= xlt('Type the beginning of the name') ?>)
    <br /><button style="margin-top:10px;width:900px;" id="savePrices"><?= xlt('Save Changes') ?></button>
 </div>
<form id="prices">
  <input type="hidden" value="UpdatePrices" name="action" />     
  <input type="hidden" value="<?php echo intval(trim($_GET['id'])); ?>" name="pr_id" />
  <table style="margin-top:70px; width:100%" class="tableSorter">
    <thead>
      <tr>
        <th><?= xlt('Price Level') ?></th>
        <th><?= ('Price') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php 
         foreach($priceLists as $option_id=>$price){
         
          echo '
                <tr class="optionPrice" name="'.attr(strtolower($option_id)).'">
                  <td>'.text(preg_replace('/_/',' ',$priceListNames[$option_id])).'</td>
                  <td><input name="'.attr($option_id).'" class="price" medicalAid="'.attr($option_id).'" value="'.attr($price).'" /></td>
                </tr>
                ';
         }
        
      ?>
      </tbody>
    </table> 
</form>
 <script type="text/javascript">
    var timeout;
    $("#priceLevelSearch").keydown(function (e) { 
//       alert(e) 
      if(timeout) {
        clearTimeout(timeout);
        timeout = null;
      } 
      timeout = setTimeout(function(){
        clearTimeout(timeout); // this way will not run infinitely  
        if($("#priceLevelSearch").val().length > 2){
          $(".optionPrice").hide();  
//           alert($("#medAids").val());                                                
          $( "tr[name^='"+$("#priceLevelSearch").val().toLowerCase()+"']" ).show();
        }else{
          $(".optionPrice").show();             
        }
      },300); 
    })
              
    $(document).ready(function(){
     
     $("#savePrices").click(function(){
       newDets = $("#prices").serialize();  
//        alert(newDets);
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