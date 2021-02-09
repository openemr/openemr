<?php //Templates List
use OpenEMR\Core\Header;

require_once("../../globals.php");
	require_once("$srcdir/options.inc.php");

	if(isset($_POST['delete_order_list']) && $_POST['delete_order_list'] > 0){
		$delete_order_list = $_POST['delete_order_list'];
		$queResult = sqlStatement("DELETE FROM order_for_test WHERE id = ?", array($delete_order_list));

		exit;
	}

	if(isset($_POST['add_new_order_list']) && $_POST['add_new_order_list'] == 1){
		$list_name = $_POST['list_name'];
		$list_type = $_POST['list_type'];
		$comments = $_POST['comments'];

		$queResult = sqlStatement("INSERT INTO order_for_test (list_name, list_type, comments) VALUES (?, ?, ?)", array($list_name, $list_type, $comments));

		exit;
	}
	$laborder = $_GET['orderdetails'];
	$allorder = json_decode($laborder);
?>
<html>
	<head>
		<TITLE><?php echo xl('Demographics Form Option'); ?></TITLE>
		<?php Header::setupHeader(['opener', 'datetime-picker', 'jquery-ui']);?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/interface/forms/assessment_form/templates.css" type="text/css">
    <style>
        .body_top{
            background: #FAFAFA;
        }
        .order_for_test_left{
            float: left;
            width: 60%;
        }
        .order_for_test_right{
            float: right;
            width: 38%;
            padding-left: 10px;
        }
        .reason_for_orders_table_wrap{
            height: 120px;
            overflow-y: auto;
            background: #FFF;
            margin-bottom: 20px;
        }
        .reason_for_orders_table, .tab_table, .order_queue_table{
            width: 100%;
            border-left: 1px solid #DDD;
            border-top: 1px solid #DDD;
            margin-bottom: 10px;
        }
        .reason_for_orders_table th, .tab_table th, .order_queue_table th,
        .reason_for_orders_table td, .tab_table td, .order_queue_table td{
            border-right: 1px solid #DDD;
            border-bottom: 1px solid #DDD;
            background: #FFF;
            font-size: 13px;
        }
        .tab_heaedr, .tab_sub_header{
            list-style: none;
            margin: 0px;
            padding: 0px;
        }

        .tab_sub_header{
            margin-bottom: -10px;
            margin-top: 10px;
        }
        .tab_heaedr li, .tab_sub_header li{
            display: inline-block;
            list-style: none;
            margin: 0px;
            margin-right: 2px;
            padding: 10px 6px;
            background: #DDD;
            line-height: 10px;
            font-size: 14px;
            cursor: pointer;
        }
        .tab_heaedr li.active, .tab_sub_header li.active{
            background: #2672EC;
            color: #FFF;
        }
        .tab_sub_header li.active{
            background: #676767;
        }
        .quick_btn_wrap{
            margin: 10px 0px;
            text-align: right;
        }
        .quick_btn_wrap button{
            float: none;
            display: inline-block;
        }
        .tab_body, .tab_sub_body{
            display: none;
            border: 1px solid #DDD;
            padding: 10px;
            background: #FFF;
        }
        .tab_body.active, .tab_sub_body.active{
            display: block;
        }
        .order_queue_table_wrap{
            min-height: 50%;
            overflow-y: auto;
            background: #FFF;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .tab_table_wrap{
            height: 50%;
            overflow-y: auto;
            background: #FFF;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .order_queue_btn_wrap{
                text-align: right;
        }
        .order_queue_btn_wrap button{
            float: none;
            display: inline-block;
        }
        .quick_order_entery{
            display: none;
            position: fixed;
            left: 35%;
            top: 30%;
            width: 300px;
            background: #FFF;
            border: 2px solid #DDD;
            padding: 10px;
            z-index: 2;
        }
        .quick_order_entery_back{
            display: none;
            position: fixed;
            left: 0px;
            right: 0px;
            top: 0px;
            bottom: 0px;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        #quick_order_name, #quick_order_comments{
            display: block;
            width: 100%;
            border: 1px solid #DDD;
            margin-bottom: 10px;
        }
    </style>
        <script>
            $(document).ready(function() {
                //Calendar.setup({inputField:"order_date", ifFormat:"%Y-%m-%d", button:"order_date"});
                $(".datepicker").datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = false; ?>
                    <?php $datetimepicker_formatInput = false; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                });
            });
        </script>
	</head>
	<body class="body_top">
		<div class="order_for_test_wrap">
			<div class="order_for_test_left">
				<h3>Reason For Orders</h3>
				<div class="reason_for_orders_table_wrap">
					<table class="reason_for_orders_table">
						<thead>
							<tr>
								<th style="width:30px;">&nbsp;</th>
								<th style="width:50px;">ICD</th>
								<th>Descriptions</th>
							</tr>
						</thead>
						<tbody>
							<div id="selcodes">
								<?php
								//$codeord = $order['icdcodes'];
								$src = sqlStatement("SELECT code, code_text FROM billing WHERE pid='".$_SESSION['pid']."' AND encounter='".$_SESSION['encounter']."' AND activity=1 AND code_type in('ICD9','ICD10')");
								while($res = sqlFetchArray($src)){
									echo "<tr>";
									echo "<td class='reason_for_orders_check'><input type='checkbox' checked name='reason_for_orders_check[]' value='".$res['code']."'/></td>";
									echo "<td class='reason_for_orders_icd'>".$res['code']."</td>";
									echo "<td class='reason_for_orders_desc'>".$res['code_text']."</td>";
									echo "</tr>";
								}
								?>
							</div>
						</tbody>
					</table>
				</div>

				<ul class="tab_heaedr">
					<li data-ref="1" class="active">Nursing</li>
					<li data-ref="2" class="">Labs</li>
					<li data-ref="3" class="">Imaging</li>
					<li data-ref="4" class="">Referrals</li>
				</ul>
				<div id="tab_body_1" class="tab_body active">
					<ul class="tab_sub_header">
						<li data-ref="1" class="active">General</li>
						<li data-ref="2">Immunizations</li>
					</ul>
					<div class="tab_table_wrap active tab_sub_body" id="tab_sub_body_1">
						<div class="quick_btn_wrap">
							<button type="button" onclick="addNewOrderListing(1);">Quick Add</button>
							<button type="button" onclick="printForm();">Print Orders</button>
						</div>
						<table class="tab_nursing_1_table tab_table">
							<thead>
								<tr>
									<th style="width:30px;">&nbsp;</th>
									<th style="width:100px;">&nbsp;</th>
									<th>Comments</th>
									<th style="width:20px;">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tab_nursing = sqlStatement("SELECT * FROM order_for_test WHERE list_type = ?", array(1));
								while ($tab_nursingRow = sqlFetchArray($tab_nursing)) {
									$checked = '';
									if (in_array($tab_nursingRow['id'],$allorder->order)) {
										$checked = " checked";
									}
							?>
								<tr>
									<td class="tab_check"><input onclick="changeTabCheck($(this));" type="checkbox" <?php echo $checked; ?> name="tab_nursing_<?php echo $tab_nursingRow['id']; ?>" id="tab_nursing_<?php echo $tab_nursingRow['id']; ?>" value="<?php echo $tab_nursingRow['id']; ?>"/></td>
									<td class="tab_name"><?php echo $tab_nursingRow['list_name']; ?></td>
									<td class="tab_desc"><?php echo $tab_nursingRow['comments']; ?></td>
									<td class="tab_del"><span  onclick="deleteTabCheck($(this), <?php echo $tab_nursingRow['id']; ?>);"><i class="fa fa-trash"></i></span></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="tab_table_wrap tab_sub_body" id="tab_sub_body_2">
						<div class="quick_btn_wrap">
							<button type="button" onclick="addNewOrderListing(2);">Quick Add</button>
							<button type="button" onclick="printForm();">Print Orders</button>
						</div>
						<table class="tab_nursing_2_table tab_table">
							<thead>
								<tr>
									<th style="width:30px;">&nbsp;</th>
									<th style="width:100px;">&nbsp;</th>
									<th>Comments</th>
									<th style="width:20px;">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tab_nursing = sqlStatement("SELECT * FROM order_for_test WHERE list_type = ?", array(2));
								while ($tab_nursingRow = sqlFetchArray($tab_nursing)) {
									$checked = '';
									if (in_array($tab_nursingRow['id'],$allorder->order)) {
										$checked = " checked";
									}
							?>
								<tr>
									<td class="tab_check"><input onclick="changeTabCheck($(this));" type="checkbox" <?php echo $checked; ?> name="tab_nursing_<?php echo $tab_nursingRow['id']; ?>" id="tab_nursing_<?php echo $tab_nursingRow['id']; ?>" value="<?php echo $tab_nursingRow['id']; ?>"/></td>
									<td class="tab_name"><?php echo $tab_nursingRow['list_name']; ?></td>
									<td class="tab_desc"><?php echo $tab_nursingRow['comments']; ?></td>
									<td class="tab_del"><span  onclick="deleteTabCheck($(this), <?php echo $tab_nursingRow['id']; ?>);"><i class="fa fa-trash"></i></span></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<div id="tab_body_2" class="tab_body ">
					<div class="quick_btn_wrap">
						<button type="button" onclick="addNewOrderListing(3);">Quick Add</button>
						<button type="button" onclick="printForm();">Print Orders</button>
					</div>
					<div class="tab_table_wrap">
						<table class="tab_labs_table tab_table">
							<thead>
								<tr>
									<th style="width:30px;">&nbsp;</th>
									<th style="width:100px;">&nbsp;</th>
									<th>Comments</th>
									<th style="width:20px;">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tab_labs = sqlStatement("SELECT * FROM order_for_test WHERE list_type = ?", array(3));
								while ($tab_labsRow = sqlFetchArray($tab_labs)) {
									$checked = '';
									if (in_array($tab_labsRow['id'],$allorder->order)) {
										$checked = " checked";
									}
							?>
								<tr>
									<td class="tab_check"><input onclick="changeTabCheck($(this));" type="checkbox" <?php echo $checked; ?> name="tab_labs_<?php echo $tab_labsRow['id']; ?>" id="tab_labs_<?php echo $tab_labsRow['id']; ?>" value="<?php echo $tab_labsRow['id']; ?>"/></td>
									<td class="tab_name"><?php echo $tab_labsRow['list_name']; ?></td>
									<td class="tab_desc"><?php echo $tab_labsRow['comments']; ?></td>
									<td class="tab_del"><span  onclick="deleteTabCheck($(this), <?php echo $tab_labsRow['id']; ?>);"><i class="fa fa-trash"></i></span></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<div id="tab_body_3" class="tab_body ">
					<div class="quick_btn_wrap">
						<button type="button" onclick="addNewOrderListing(4);">Quick Add</button>
						<button type="button" onclick="printForm();">Print Orders</button>
					</div>
					<div class="tab_table_wrap">
						<table class="tab_imaging_table tab_table">
							<thead>
								<tr>
									<th style="width:30px;">&nbsp;</th>
									<th style="width:100px;">&nbsp;</th>
									<th>Comments</th>
									<th style="width:20px;">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tab_imaging = sqlStatement("SELECT * FROM order_for_test WHERE list_type = ?", array(4));
								while ($tab_imagingRow = sqlFetchArray($tab_imaging)) {
									$checked = '';
									if (in_array($tab_imagingRow['id'],$allorder->order)) {
										$checked = " checked";
									}
							?>
								<tr>
									<td class="tab_check"><input onclick="changeTabCheck($(this));" type="checkbox" <?php echo $checked; ?> name="tab_imaging_<?php echo $tab_imagingRow['id']; ?>" id="tab_imaging_<?php echo $tab_imagingRow['id']; ?>" value="<?php echo $tab_imagingRow['id']; ?>"/></td>
									<td class="tab_name"><?php echo $tab_imagingRow['list_name']; ?></td>
									<td class="tab_desc"><?php echo $tab_imagingRow['comments']; ?></td>
									<td class="tab_del"><span  onclick="deleteTabCheck($(this), <?php echo $tab_imagingRow['id']; ?>);"><i class="fa fa-trash"></i></span></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<div id="tab_body_4" class="tab_body ">
					<div class="quick_btn_wrap">
						<button type="button" onclick="addNewOrderListing(5);">Quick Add</button>
						<button type="button" onclick="printForm();">Print Orders</button>
					</div>
					<div class="tab_table_wrap">
						<table class="tab_referrals_table tab_table">
							<thead>
								<tr>
									<th style="width:30px;">&nbsp;</th>
									<th style="width:100px;">&nbsp;</th>
									<th>Comments</th>
									<th style="width:20px;">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$tab_referrals = sqlStatement("SELECT * FROM order_for_test WHERE list_type = ?", array(5));
								while ($tab_referralsRow = sqlFetchArray($tab_referrals)) {
									$checked = '';
									if (in_array($tab_referralsRow['id'],$allorder->order)) {
										$checked = " checked";
									}
							?>
								<tr>
									<td class="tab_check"><input onclick="changeTabCheck($(this));" type="checkbox" <?php echo $checked; ?> name="tab_referrals_<?php echo $tab_referralsRow['id']; ?>" id="tab_referrals_<?php echo $tab_referralsRow['id']; ?>" value="<?php echo $tab_referralsRow['id']; ?>"/></td>
									<td class="tab_name"><?php echo $tab_referralsRow['list_name']; ?></td>
									<td class="tab_desc"><?php echo $tab_referralsRow['comments']; ?></td>
									<td class="tab_del"><span  onclick="deleteTabCheck($(this), <?php echo $tab_referralsRow['id']; ?>);"><i class="fa fa-trash"></i></span></td>
								</tr>
							</tbody>
							<?php } ?>
						</table>
					</div>
				</div>
			</div>
			<div class="order_for_test_right">
				<h3>Order Queue</h3>
				<div class="order_queue_field_wrap">
					<div>
						<label>Order Date</label>
						<input class="datepicker" type="text" name="order_date" id="order_date" value="<?php echo date('Y-m-d'); ?>"/>
					</div>
					<div>
						<label>Ordering Provider</label>
						<select name="ordering_provider" id="ordering_provider">
						<?php
						$usql = sqlStatement("SELECT id, fname, lname FROM users WHERE active=1");
						while($ures = sqlFetchArray($usql)){
							echo "<option value='".$ures['id']."'";
							if($ures['id'] == $_SESSION['id']) echo " selected";
							echo ">". $ures['fname']." ".$ures['lname'];
							if($allorder->ordprovider == $ures['id']){
								$ordprovider = $ures['fname']." ".$ures['lname'];
							}
						}
						?>
						</select>
					</div>
				</div>
				<div class="order_queue_table_wrap">
					<table class="order_queue_table">
						<thead>
							<tr>
								<th>Order</th>
								<th style="width:70px;">To</th>
								<th style="width:50px;">From</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$lorder = $allorder->order;
							$listtypes = array("1"=>"tab_nursing","2"=>"tab_nursing","3"=>"tab_labs","4"=>"tab_imaging","5"=>"tab_referrals");
							foreach($lorder as $ord){
								$tsql = sqlQuery("SELECT list_name,list_type FROM order_for_test WHERE id='$ord'");
								echo "<tr class=".$listtypes[$tsql['list_type']]."_".$ord.">";
								echo "<input type='hidden' name=selOrders[] value='".$ord."'>";
								echo "<td class='selected_orders'>".$tsql['list_name']."</td>";
								if($listtypes[$tsql['list_type']] == "tab_nursing"){
									$toorder = "To Nursing";
								}else if($listtypes[$tsql['list_type']] == "tab_labs"){
									$toorder = "To Laboratory";
								}else if($listtypes[$tsql['list_type']] == "tab_imaging"){
									$toorder = "To Radiology";
								}else if($listtypes[$tsql['list_type']] == "tab_referrals"){
									$toorder = "To Referring Doctor";
								}
								echo "<td>".$toorder."</td>";
								echo "<td>".$ordprovider."</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
				<div class="order_queue_btn_wrap">
					<button type="button" onclick="saveForm();">Save</button>
					<button type="button" onclick="closeForm();">Close</button>
				</div>
			</div>
		</div>
		<div class="quick_order_entery_back"></div>
		<div class="quick_order_entery">
			<div>
				<label>Name: </label><input type="text" name="quick_order_name" id="quick_order_name" />
			</div>
			<div>
				<label>Comments: </label><textarea type="text" name="quick_order_comments" id="quick_order_comments" ></textarea>
			</div>
			<div>
				<button type="button" id="quick_order_save">Save</button>
				<button type="button" id="quick_order_cancel">Cancel</button>
			</div>
		</div>
		<script>
			var quick_order_list_type;
			$(document).ready(function(){
				//Calendar.setup({inputField:"order_date", ifFormat:"%Y-%m-%d", button:"order_date"});
				$('#quick_order_save').on("click", function(){
					var list_name = $('#quick_order_name').val();
					var comments = $('#quick_order_comments').val();
					sendAddRequest(quick_order_list_type, list_name, comments);
					$('.quick_order_entery, .quick_order_entery_back').hide();
					$('#quick_order_name, #quick_order_comments').val("");
				});

				$('#quick_order_cancel').on("click", function(){
					$('.quick_order_entery, .quick_order_entery_back').hide();
				});

				$('.tab_heaedr li').on("click", function(){
					$('.tab_heaedr li, .tab_body').removeClass('active');
					$(this).addClass('active');
					let dataRef = $(this).attr('data-ref');
					$('#tab_body_' + dataRef).addClass('active');
				});
				$('.tab_sub_header li').on("click", function(){
					$('.tab_sub_header li, .tab_sub_body').removeClass('active');
					$(this).addClass('active');
					let dataRef = $(this).attr('data-ref');
					$('#tab_sub_body_' + dataRef).addClass('active');
				});
				var temp = decodeURIComponent(window.opener.document.getElementById("laborders").value);
				if(temp){
					var labords = JSON.parse(temp);
					$("#order_date").val(labords.orddate);
					$('#ordering_provider').val(labords.ordprovider);
					/*for (i in labords.order) {
					    var ordid = labords.order[i];
					    $('#tab_nursing_'+ordid).trigger('click');
					    $('#tab_labs_'+ordid).trigger('click');
					    $('#tab_imaging_'+ordid).trigger('click');
					    $('#tab_referrals_'+ordid).trigger('click');
					}*/
				var docarr = $("input[name='reason_for_orders_check[]']").map(function(){return $(this).val();}).get();
				var selicdarr = labords.icdcodes;
				for (j in docarr){
					if(jQuery.inArray(docarr[j],selicdarr) == -1){
						$('input:checkbox[value="' + docarr[j] + '"]').prop('checked', false);
					}
				}
				}
			});

			function changeTabCheck(obj){
				var thisName = obj.attr('name');
				var parentTr = obj.closest('tr');
				var ordid = obj.attr('value');
				var ordprov = $("#ordering_provider :selected").text();
				var toorder;
				if(thisName.indexOf("tab_nursing") != -1){
				    toorder = "To Nursing";
				}else if(thisName.indexOf("tab_labs") != -1){
					toorder = "To Laboratory";
				}else if(thisName.indexOf("tab_imaging") != -1){
					toorder = "To Radiology";
				}else if(thisName.indexOf("tab_referrals") != -1){
					toorder = "To Referring Doctor";
				}
				if(obj.prop('checked') == true){
					var appendVal = '';
					appendVal += "<tr class="+thisName+">";
					appendVal += "<input type='hidden' name=selOrders[] value='"+ordid+"'>";
					appendVal += "<td class='selected_orders'>"+parentTr.find('.tab_name').text()+"</td>";
					appendVal += "<td>"+toorder+"</td><td>"+ordprov+"</td>";
					appendVal += "</tr>";
					$('.order_queue_table tbody').append(appendVal);
				}else{
					$('.order_queue_table tbody tr.' + thisName).remove();
				}
			}

			function addNewOrderListing(list_type){
				quick_order_list_type = list_type;
				$('.quick_order_entery, .quick_order_entery_back').show();
			}

			function sendAddRequest(list_type, list_name, comments){
				if(list_name != null && list_name != undefined && list_name != "")	{
					$.ajax({
						url: "./orders_for_test.php",
						type: "POST",
						data: {
							add_new_order_list: 1,
							list_type: list_type,
							list_name: list_name,
							comments: comments
						},
						success: function(data){
							if(data.trim() == ""){
								var tableId = 'tab_nursing_1';
								if( list_type == 2 ) tableId = 'tab_nursing_2';
								if( list_type == 3 ) tableId = 'tab_labs';
								if( list_type == 4 ) tableId = 'tab_imaging';
								if( list_type == 5 ) tableId = 'tab_referrals';

								var appendVal = '';
								appendVal += '<tr><td class="tab_check"><input onclick="changeTabCheck($(this));" type="checkbox" name="'+tableId+'_'+( $('.'+tableId+'_table tbody tr').length + 1 )+'" /></td>';
								appendVal += '<td class="tab_name">'+list_name+'</td>';
								appendVal += '<td class="tab_desc">'+comments+'</td>';
appendVal += '<td class="tab_del"><span  onclick="deleteTabCheck($(this), '+data.trim()+');"><i class="fa fa-trash"></i></span></td></tr>';

								$('.'+tableId+'_table tbody').append(appendVal);
							}
						},
						error: function(err){
							console.log(err);
						}
					});
				}
			}

			function deleteTabCheck(obj, deleteId){
				if(parseFloat(deleteId) > 0)	{
					var conf = confirm("Do you want to remove this record?");
					if(conf){
						$.ajax({
							url: "./orders_for_test.php",
							type: "POST",
							data: {
								delete_order_list: deleteId
							},
							success: function(data){
								if(data.trim() == ""){
									obj.closest('tr').remove();
								}
							},
							error: function(err){
								console.log(err);
							}
						});
					}
				}
			}

			function closeForm(){
				dlgclose();
			}

			function saveForm(){
				var json = {};
				ordarr = $("input[name='selOrders[]']").map(function(){return $(this).val();}).get();
				ordicd = $("input[name='reason_for_orders_check[]']:checkbox:checked").map(function(){return $(this).val();}).get();
				orddate = $("#order_date").val();
				ordprov = $("#ordering_provider").val();
				json.order = ordarr;
				json.orddate = orddate;
				json.ordprovider = ordprov;
				json.icdcodes = ordicd;
				var myJson = encodeURIComponent(JSON.stringify(json));
				var parentId = "laborders";
				window.opener.document.getElementById(parentId).value = myJson;
				dlgclose();
			}

			function printForm(){
				 var icd_codes = ''; var selected_orders = '';
				 var ordering_provider = $('#ordering_provider').val();
				 var order_date = $('#order_date').val();

				 $('.reason_for_orders_table tbody tr').each(function(){
					 if($(this).find('.reason_for_orders_check input').prop("checked") == true){
						 if(icd_codes != "") icd_codes += ','
						 icd_codes += $(this).find('.reason_for_orders_icd').text() + ": " + $(this).find('.reason_for_orders_desc').text();
					 }
				 });
				 $('.order_queue_table tbody tr').each(function(){
					if(selected_orders != "") selected_orders += ','
					selected_orders += $(this).find('.selected_orders').text();
				 });

				 window.open('./orders_for_test_print.php?icd_codes='+icd_codes+'&selected_orders='+selected_orders+'&ordering_provider='+ordering_provider+'&order_date='+order_date+'', '_blank', 'resize');
			}

			$(document).ready(function() {
				var codes = window.opener.document.getElementById("feecodes").value;
				if(codes){
					var icode = codes.split("#");
					var astring ='';
					for(i=0; i<icode.length; i++){
						if(icode[i].indexOf("CPT4") == -1){
							var cdesc = icode[i].split("~");
							astring += "<tr>";
							astring += "<td class='reason_for_orders_check'><input type='checkbox' checked name='reason_for_orders_check[]' value='"+cdesc[0]+"'/></td>";
							astring += "<td class='reason_for_orders_icd'>"+cdesc[0]+"</td>";
							astring += "<td class='reason_for_orders_desc'>"+cdesc[1]+"</td>";
							astring += "</tr>";
						}
					}
					$("table.reason_for_orders_table tbody").append(astring);
				}
			});
		</script>
	</body>
</html>
