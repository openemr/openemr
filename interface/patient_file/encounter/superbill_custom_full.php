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
 
$itemsTypeRes = sqlStatement("SELECT * FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key");
$itemTypesArray = array();
 
$lastCodes = array();

while($row = sqlFetchArray($itemsTypeRes)){
 $itemTypesArray[] = $row; 
} 


?>
<html>
  <head>
    <?php html_header_show(); ?>
    <link rel="stylesheet" href="<?php echo attr($css_header);?>" type="text/css">     
    <script type="text/javascript" src="../../../library/dialog.js"></script>
    <script type="text/javascript" src="../../../library/textformat.js"></script>
    <script type="text/javascript" src="../../../library/js/jquery-1.6.4.min.js"></script>     
    <!--                         
    <link rel="stylesheet" href="../../../library/css/tableSorter/style.css" type="text/css">       
    <link rel="stylesheet" href="../../../library/css/tableSorter/theme.blue.css" type="text/css"> 
	<link class="ui-theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.jui.css">
  	<link class="theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.default.css">
  	<link class="theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.blue.css">
  	<link class="theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.green.css">
  	<link class="theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.grey.css">
  	<link class="theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.ice.css">
  	<link class="theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.black-ice.css">
  	<link class="theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.dark.css">
  	<link class="theme" rel="stylesheet" href="../../../library/css/tableSorter/theme.dropbox.css">   
    <script type="text/javascript" src="../../../library/js/tableSorter_2.14.0/jquery.tablesorter.js"></script>   
    <script type="text/javascript" src="../../../library/js/tableSorter_2.14.0/jquery.tablesorter.widgets.js"></script>
    -->
    <script type="text/javascript" src="../../../library/js/common.js"></script>   
    <script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
    <link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
    
      
    <style type="text/css">
	    td.itemEdit{padding: 10px 5px; margin:0;}
        .tableSorter tbody tr{cursor:pointer}
        .tableSorter tbody tr:hover .itemEdit{background-color:rgb(66,66,66); color:white;}
		th{ padding: 15px 0; }
    </style>
    
    <script type="text/javascript">
      $(document).ready(function(){
       
               
      
      $("#searchForm").submit(function(){
        $("#resultsContainer").html('<tr><td colspan="7"><p style="margin:50px;" align="center"><img src="../../../interface/pic/ajax-loader.gif" /></p></td></tr>'); 
        $.post("superbill_custom_fetch_codes.php", $("#searchForm").serialize() ).done(function(data) {  
           
			data = $.parseJSON(data);
			//console.log(data);
			$("#resultsContainer").html("");
			if (data.length == 0) {
                $("#resultsContainer").html("<tr><td colspan='9' class='warning' style='padding-top:50px; text-align:center'><b>NO RESULTS FOUND</td></tr>");
            }
			$.each(data,function(i,e){
			 //console.log(e)
			   $("#resultsContainer").append(''+
				 '<tr>'+
					 '<td style="text-align:center" class="itemEdit" itemID="'+e.id+'" ct_id="'+e.ct_id+'">'+e.code+'</td>'+
					 '<td class="itemEdit" itemID="'+e.id+'" ct_id="'+e.ct_id+'">'+e.code_text+'</td>'+
					 '<td style="text-align:center" class="itemEdit" itemID="'+e.id+'" ct_id="'+e.ct_id+'">'+(e.modifier == '' ? '&nbsp;' : e.modifier)+'</td>'+
					 '<td style="text-align:center" class="itemEdit" itemID="'+e.id+'" ct_id="'+e.ct_id+'">'+(e.active == 1 ? '&#10004;' : '&#10008;')+'</td>'+
					 
					 '<td style="text-align:center" class="itemEdit" itemID="'+e.id+'" ct_id="'+e.ct_id+'">'+(e.reportable == 1 ? '&#10004;' : '&#10008;')+'</td>'+
					 '<td style="text-align:center" class="itemEdit" itemID="'+e.id+'" ct_id="'+e.ct_id+'">'+(e.financial_reporting == 1 ? '&#10004;' : '&#10008;')+'</td>'+
					 
					 
					 '<td style="text-align:center" class="itemEdit" itemID="'+e.id+'" ct_id="'+e.ct_id+'">'+(e.ct_label.trim() == '' ? e.ct_key : e.ct_label )+'</td>'+
					 '<td style="text-align:center" class="itemEdit" itemID="'+e.id+'" ct_id="'+e.ct_id+'" style="text-align:center"><img title="edit this item" style="width:16px" src="../../../images/b_edit.png" /></td>'+
					 '<td style="text-align:center" class="itemPrice" itemID="'+e.id+'" ct_id="'+e.ct_id+'" style="text-align:center"><img title="edit prices" style="width:16px" src="../../../images/fee.png" /></td>'+
				   '</tr>'
			   )
			});
			  
		    //$(".tableSorter").tablesorter({sortList:[[0,1]], widgets: ['zebra']});
		    
		    
        }).error(function(){
		 $("#resultsContainer").html('<tr><td colspan="7"><p style="margin:50px;" align="center">ERROR, PLEASE TRY AGAIN</p></td></tr>'); 
        
		});
        return false;
      }) ;
        
        $("#itemTypes").change(function(){         
          $("#searchForm").submit();
        })  
        
        $("#addNew").click(function(){
            $("#triggerLink").attr("href","superbill_editItem.php?id=") 
            $("#triggerLink").fancybox( {
                                    'overlayOpacity' : 0.4,
                                    'showCloseButton' : true,
                                    'frameHeight' : 600,
                                    'frameWidth' : 900,
                                    'centerOnScroll' : false,
                                    'transitionIn' : 'elastic',
                                    'autoDimensions' : false,
                                    'hideOnContentClick':false
                                }).trigger('click');
            return false;                  
        })
        
        $(".itemEdit").live("click",function(){
            $("#triggerLink").attr("href","superbill_editItem.php?ct_id="+$(this).attr("ct_id")+"&id="+$(this).attr("itemID")) 
            $("#triggerLink").fancybox( {
                                    'overlayOpacity' : 0.4,
                                    'showCloseButton' : true,
                                    'frameHeight' : 600,
                                    'frameWidth' : 900,
                                    'centerOnScroll' : false,
                                    'transitionIn' : 'elastic',
                                    'autoDimensions' : false,
                                    'hideOnContentClick':false
                                }).trigger('click');
        })
        
        $(".itemPrice").live("click",function(){ 
            $("#triggerLink").attr("href","superbill_editPrice.php?id="+$(this).attr("itemID")) 
            $("#triggerLink").fancybox( {
                                    'overlayOpacity' : 0.4,
                                    'showCloseButton' : true,
                                    'frameHeight' : 600,
                                    'frameWidth' : 900,
                                    'centerOnScroll' : false,
                                    'transitionIn' : 'elastic',
                                    'autoDimensions' : false,
                                    'hideOnContentClick':false 
                                }).trigger('click');
        })
        
        
        
        
      
     // $("#searchForm").submit(); 
        $("#sTerm").focus();        
      })
    </script>
    
    
  </head>
  <body style="padding:20px;"> 
    <button id="addNew" style="display: inline; float: none"><?php echo xlt('Add New') ?></button>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?php echo xlt('OR') ?>    
  <form id="searchForm" style="display: inline; float: none">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <b><?= xlt('SEARCH') ?> : </b>
    <select id="itemTypes" name="itemTypes">
      <option value="0"><?= xla('ALL') ?></option>
	  <?php
		foreach($itemTypesArray as $itemType){
		  echo '<option value="'.attr($itemType['ct_id']).'">'.attr($itemType['ct_key']).'</option>';
		}
	  ?>
    </select>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php echo xlt('Search') ?> : <input placeholder="<?= xla('Type your search here, leave blank to show all') ?>" size="60" name="sTerm" id="sTerm" type="text" /> <input type="submit" id="searchNow" name="searchNow" value="<?= xla('Search') ?>" style="display: inline; float: none" />
    ( <?php echo xlt('By code or description') ?> )
	<input type="checkbox" name="serRepOnly" id="serRepOnly" />
	<label for="serRepOnly"><?= xlt('Service Reporting') ?></label>
	<input type="checkbox" name="diagRepOnly" id="diagRepOnly" />
	<label for="diagRepOnly"><?= xlt('Diagnosis Reporting') ?></label>
  </form> 
  <hr />
  <a id="triggerLink" style="display:none;">.</a>
  <div id="codeDiv">
	<table class="tableSorter" width="100%">
	  <thead>
		<tr>     
		  <th><?= xlt('Code') ?></th>  
		  <th><?= xlt('Description') ?></th>
		  <th><?= xlt('Modifier') ?></th>
		  <th><?= xlt('Active') ?></th>   
		  <th><?= xlt('DX Rep') ?></th>   
		  <th><?= xlt('Serv Rep') ?></th>   
		  <th><?= xlt('Code Type') ?></th> 
		  <th></th>  
		  <th></th>    
		</tr>
	  </thead>									  
	  <tbody id="resultsContainer"></tbody>
	</table>
  </div>
