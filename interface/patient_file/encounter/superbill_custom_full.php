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
    <link rel="stylesheet" href="../../../public/assets/font-awesome-4-6-3/css/font-awesome.css" type="text/css">      
    <script type="text/javascript" src="../../../public/assets/jquery-min-1-7-2/index.js"></script> 
    <script type="text/javascript" src="../../../public/assets/jquery-fancybox-1-2-6/index.js"></script>
    <link rel="stylesheet" type="text/css" href="../../../public/assets/jquery-fancybox-1-2-6/index.css" media="screen" />
    
      
    <style type="text/css">
	    td.itemEdit{padding: 10px 5px; margin:0;}
        .tableSorter tbody tr{cursor:pointer}
        .tableSorter tbody tr:hover .itemEdit{background-color:rgb(66,66,66); color:white;}
		th{ padding: 15px 0; }
    </style>
    
    <script type="text/javascript">
	 
      var $start = 0;         
      var $limit = 20;
	  var $currentPage = 1;
	  var $pages = 0;
	  var $lastSearchData;
	 
      $(document).ready(function(){
       
	   $("body").on("click",".nextPage",function(){
		  $currentPage += 1;
		  return search();
		  
	   });
	   
	   $("body").on("click",".prevPage",function(){
		  $currentPage -= 1;
		  return search();
		  
	   });
	   
	  
      $("#searchForm").submit(function(e){
	    e.preventDefault();
	    $lastSearchData = $("#searchForm").serialize();
	    return search();
      }) ;
         
        
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
        });
        
        $("body").on("click",".itemEdit",function(){
		    $parent = $(this).parent();
		    $ct_id = $parent.attr("ct_id");
		    $itemID = $parent.attr("itemID");
		    $code_external = $parent.attr("code_external");
			$code_type_name_external = $parent.attr("code_type_name_external");
		    $code = $parent.attr("code");
            $("#triggerLink").attr("href","superbill_editItem.php?ct_id="+$ct_id+"&id="+$itemID+"&code_external="+$code_external+"&code="+$code+"&code_type_name_external="+$code_type_name_external); 
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
        });
        
        $("body").on("click",".itemPrice",function(){ 
		    $parent = $(this).parent();
		    $ct_id = $parent.attr("ct_id");
		    $itemID = $parent.attr("itemID");
		    $code_external = $parent.attr("code_external");
		    $code = $parent.attr("code");
            $("#triggerLink").attr("href","superbill_editPrice.php?ct_id="+$ct_id+"&id="+$itemID+"&code_external="+$code_external+"&code="+$code);
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
        });
        
        
        $("#checkbox_all_codes").click(function(){
		  if ($(this).is(":checked")) {
            $(".ct_checkbox").attr('checked',true);
          }else{
		   $(".ct_checkbox").removeAttr('checked');
		  }
		});
		$(".ct_checkbox").click(function(){
		 var allChecked = true;
		 $.each($(".ct_checkbox"),function(){
		   if (!$(this).is(":checked")) {
			 allChecked = false;
		   }
		 });
		 if (allChecked) {
            $("#checkbox_all_codes").attr('checked',true);
         }else{
			$("#checkbox_all_codes").removeAttr('checked');
		 }
		});
        
      
     // $("#searchForm").submit(); 
        $("#sTerm").focus();        
      });
	  
	  function search(){
	    $start = (($currentPage-1) * $limit) ;
		console.log("searching...");
		console.log("current Page : "+$currentPage);
		console.log("start :"+$start);
		console.log($lastSearchData);
        $("#resultsContainer").html('<tr><td colspan="7"><p style="margin:50px;" align="center"><img src="../../../interface/pic/ajax-loader.gif" /></p></td></tr>'); 
        $.post("superbill_custom_fetch_codes.php?start="+$start+"&limit="+$limit, $lastSearchData ).done(function(data) {  
			if (data.trim() == '999') {
			  $(".paging").html('');
              $("#resultsContainer").html("<tr><td colspan='9' class='warning' style='padding-top:50px; text-align:center'><b>PLEASE SELECT A CODE TYPE TO SEARCH</td></tr>");
            }else{
				data = $.parseJSON(data);
				console.log(data);
				$("#resultsContainer").html("");
				if (data.items.length == 0) {
				    $(".paging").html('');
					$("#resultsContainer").html("<tr><td colspan='9' class='warning' style='padding-top:50px; text-align:center'><b>NO RESULTS FOUND</td></tr>");
				}else{
				 $pages = Math.ceil(data.count / $limit);
				 var $pagingHtml = "";
				 $pagingHtml += ($currentPage > 1 ? "<a href='#' class='prevPage'><span class='fa fa-arrow-left'></span></a> " : "");
				 $pagingHtml += "Page "+$currentPage+" of "+$pages;
				 $pagingHtml += ($currentPage < $pages ? " <a href='#' class='nextPage'><span class='fa fa-arrow-right'></span></a> " : "");
				  $(".paging").html($pagingHtml);
				  $.each(data.items,function(i,e){
				     //console.log(e)
					 $("#resultsContainer").append(''+
					   '<tr code="'+e.code+'" code_type_name_external="'+e.code_type_name+'" code_external="'+e.code_external+'" itemID="'+e.id+'" ct_id="'+e.code_type+'">'+
						   '<td style="text-align:center" class="itemEdit">'+e.code+'</td>'+
						   '<td class="itemEdit">'+e.code_text+'</td>'+
						   '<td style="text-align:center" class="itemEdit">'+(e.modifier == '' ? '&nbsp;' : e.modifier)+'</td>'+
						   '<td style="text-align:center" class="itemEdit">'+(e.active == 1 ? '&#10004;' : '&#10008;')+'</td>'+
						   
						   '<td style="text-align:center" class="itemEdit">'+(e.reportable == 1 ? '&#10004;' : '&#10008;')+'</td>'+
						   '<td style="text-align:center" class="itemEdit">'+(e.financial_reporting == 1 ? '&#10004;' : '&#10008;')+'</td>'+
						   
						   
						   '<td style="text-align:center" class="itemEdit">'+e.code_type_name+'</td>'+
						   '<td style="text-align:center" class="itemEdit" style="text-align:center"><img title="edit this item" style="width:16px" src="../../../images/b_edit.png" /></td>'+
						   '<td style="text-align:center" class="itemPrice" style="text-align:center"><img title="edit prices" style="width:16px" src="../../../images/fee.png" /></td>'+
						 '</tr>'
					 )
				  });
				} 
				//$(".tableSorter").tablesorter({sortList:[[0,1]], widgets: ['zebra']});
			}
		    
		    
        }).error(function(){
		 $("#resultsContainer").html('<tr><td colspan="7"><p style="margin:50px;" align="center">ERROR, PLEASE TRY AGAIN</p></td></tr>'); 
        
		});
        return false;
	  }
    </script>
    
    
  </head>
  <body style="padding:20px;"> 
  <form id="searchForm""> 
   <h4><?= xlt('SEARCH') ?> : </h4>
    <!--<select multiple id="itemTypes" name="itemTypes">-->
      <input placeholder="<?= xla('Type your search here, leave blank to show all') ?>" size="60" name="sTerm" id="sTerm" type="text" /> ( <?php echo xlt('By code or description') ?> )
	  <input type="checkbox" name="serRepOnly" id="serRepOnly" />
	 <label for="serRepOnly"><?= xlt('Service Reporting') ?></label>
	 <input type="checkbox" name="diagRepOnly" id="diagRepOnly" />
	 <label for="diagRepOnly"><?= xlt('Diagnosis Reporting') ?></label>
	 <br /><br />
	  <input checked id="checkbox_all_codes" type="checkbox" name="itemTypes[]" value="0" /><label style="margin-right:25px" for="checkbox_all_codes"><?= xla('ALL') ?></label>
	  <?php
		foreach($itemTypesArray as $itemType){
		  //echo '<option value="'.attr($itemType['ct_id']).'">'.attr($itemType['ct_key']).'</option>';
		  echo '<input checked class="ct_checkbox" type="checkbox" name="itemTypes[]" value="'.attr($itemType['ct_id']).'" id="checkbox_'.attr($itemType['ct_id']).'" /><label style="margin-right:25px" for="checkbox_'.attr($itemType['ct_id']).'" >'.attr($itemType['ct_label']).'</label>';
		}
	  ?>
    <!--</select>-->
    
    <br />
	
	<br />
	<input type="submit" id="searchNow" name="searchNow" value="<?= xla('Search') ?>" style="width:100%;" />
	<br />
  </form> 
  <hr />
  <button id="addNew" style="width:100%"><?php echo xlt('Add New') ?></button>
  <a id="triggerLink" style="display:none;">.</a>
  <div id="codeDiv">
    <div style="clear:both; font-size: 1.5em; font-weight: bold; padding:10px; text-align: right;" class="paging"></div>
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
    <div style="clear:both; font-size: 1.5em; font-weight: bold; padding:10px; text-align: right;" class="paging"></div>
  </div>
