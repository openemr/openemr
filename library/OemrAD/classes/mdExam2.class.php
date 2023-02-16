<?php

namespace OpenEMR\OemrAd;

class Exam2 {
	
	function __construct(){
	}

	//Fetch Encounter Data
	function fetchExtExamData($encounterId, $pid, $id) {
		$res = "";

		if(!empty($encounterId)) {
			$sql = "SELECT fr.encounter, fr.pid, fr.pid, ex2.*  FROM forms As fr ".
			"LEFT JOIN form_ext_exam2 AS ex2 ON fr.form_id = ex2.id ".
			"WHERE fr.encounter = ? AND fr.pid = ? AND fr.formDir = ? ";

			$fres=sqlStatement($sql, array($encounterId, $pid, 'ext_exam2'));
  			$dt = sqlFetchArray($fres);
  			$img=GetImageHistory($pid, $encounterId);

  			$res = array(
  				'dt' => $dt,
  				'img' => $img
  			);
  			$resModule = array();
  			$modulesList = LoadList('ext_exam2_modules','active', 'seq');
  			foreach ($modulesList as $key => $module) {
  				if($module['option_id'] == $id) {
  					$resModule[] = $module;
  				}
  			}

  			$res = array(
  				'dt' => $dt,
  				'img' => $img,
  				'modules' => $resModule
  			);
  			return $res;
		}

		return false;
	}

	/*
	function ext_exam_script($values, $pid) {
		global $frmdir;

		?>
		<style type="text/css">
			.wmtColorBar .wmtRight {
				width: 140px;
				max-width: 140px;
			}
			.configLink {
				text-transform: none!important;
				margin-right: 10px;
			}
			.global_copy_container {
				display: inline-block;
    			float: right;
			}
		</style>
		<script type="text/javascript">
			$(document).ready(function(){
				var eleList = [
					'dg_begdt',
					'cp_dg_begdt',
					'dg_enddt',
					'cp_dg_enddt',
					'hosp_dt',
					'ps_begdate',
					'img_dt'
				];

				$.each(eleList, function( index, id ) {
					var ele = $("[name^='"+id+"']");
					if(ele.length > 0) {
						$.each(ele, function(inx, c_ele){
							$(c_ele).addClass('datepicker');
						});
					}
				});

				$('.datepicker').datetimepicker({
					yearStart: '1900',
				    scrollInput: false,
				    scrollMonth: false,
					format: '<?php echo self::getCurrentDateFormat(); ?>',
					timepicker:false
		        });
			});


			// This invokes the find-addressbook popup.
			function add_doc_popup(bar_id = '', tmp_barId = '', frmdir = '', encounter = '') {

				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/forms/ext_exam2/select_encounter.php?pid=". $pid; ?>'+'&bar_id='+bar_id+'&tmp_barId='+tmp_barId+'&frmdir='+frmdir+'&encounter='+encounter;
			  	let title = '<?php echo xlt('Select Encounter'); ?>';
			  	dlgopen(url, 'selectEncounter', 600, 400, '', title);
			}

			async function globalCopy(event, frmdir, encounter) {
				event.preventDefault();
            	event.stopPropagation();

            	add_doc_popup('global', 'global', frmdir, encounter);
			}

			async function copyConfig(event, tmp_barId, bar_id, frmdir, encounter) {
				event.preventDefault();
            	event.stopPropagation();

            	add_doc_popup(bar_id, tmp_barId, frmdir, encounter);
			}

			async function setEncounter(tmp_barId, bar_id, encounter_id, form_id, c_action) {
				//console.log(form_id+':'+encounter_id);
				
				await fetchExtExam(encounter_id, form_id, '<?php echo $pid; ?>', bar_id, c_action);

				if(tmp_barId != 'global' && tmp_barId != '') {
					var ele = $('#'+tmp_barId);
					if(ele.length > 0) {
						if($(ele).hasClass('wmtBarClosed') || !$('#'+bar_id+'Box').is(':visible')) {
							$(ele).click();
						}
					}
				}
			}

			async function fetchExtExam(encounterId, id, pid, bar_id, c_action) {
				var msg = "Load data from selected encounter exam form into this form? \n\n Current Data in the form will be overwritten.";

				if(bar_id != 'global') {
					var msg = "Load data from selected encounter exam form into this form section? \n\n Current Data in this form section will be overwritten.";
				}

				var confirmBox = confirm(msg);

				if(confirmBox != true) {
					return false;
				}

				if(bar_id != 'global') {
					var inputVals = $('#'+bar_id+'_request_data').val();
				} else {
					var inputVals = $('#global_request_data').val();
				}

				var valObj = {};
				if(inputVals != '') {
					valObj = JSON.parse(inputVals);
				}

				if(bar_id == 'global') {
					valObj['bar_id'] = 'global';
				}

				valObj['encounter_id'] = encounterId;
				valObj['e_id'] = id;
				valObj['pid'] = pid;
				valObj['c_action'] = c_action;

				const result = await $.ajax({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'].'/library/OemrAD/interface/forms/ext_exam2/ajax/fetch_ext_exam2.php'; ?>",
					datatype: "json",
					data: valObj
				});

				if(result != '' && confirmBox == true) {
					var resultObj = JSON.parse(result);
					
					if(bar_id != 'global') {
						extexam[bar_id](resultObj['formData'], bar_id);
					} else {
						var sectionList = [
							'cc',
							'hpi',
							'ros2',
							'ortho_exam',
							'general_exam2',
							'gyn_exam',
							'instruct',
							'assess',
							'diag'
						];
						$.each(extexam, function(i, fun){
							if(sectionList.includes(i)) {
								fun(resultObj['formData'], i);
							}
						});

						alert('Global Copy Done, Please Save');
					}
				}

				$('.datepicker').datetimepicker({
					yearStart: '1900',
				    scrollInput: false,
				    scrollMonth: false,
					format: '<?php echo self::getCurrentDateFormat(); ?>',
					timepicker:false
		        });
			}

			var extexam = {};

			extexam.cc = function(data, id) {
				setFielValue(data, id);
			}

			extexam.hpi = function(data, id) {
				setFielValue(data, id);
			}

			extexam.img = function(data, id) {

				if(data[id]['deleted_list']) {
					deleteList(id, data[id]['deleted_list']);
				}

				if(data[id]['items']) {
					$.each(data[id]['items'], function(ind, item){
						var ele = $('#'+id+'Box [name=img_dt]:last-child');
						if(ele.length > 0) {
							var row = ele.parent().parent();
							appendElement(row, row, item, id, (ind + 1));
						}
					});
				}
				
				setFielValue(data, id);
			}

			extexam.sh = function(data, id) {
				setFielValue(data, id);
			}

			extexam.all = function(data, id) {

				if(data[id]['deleted_list']) {
					deleteList(id, data[id]['deleted_list']);
				}

				if(data[id]['items']) {
					$.each(data[id]['items'], function(ind, item){
						var ele = $('#'+id+'Box span.all_title');
						if(ele.length > 0) {
							$.each(ele, function(inx, titleEle){
								var titleText = $(titleEle).html();
								var cnt = $(titleEle).data('id');
								if(item['all_title'] == titleText) {
									$.each(item, function(item_field, field_value){
										var setEle = $('#'+item_field+'_'+cnt);
										setInputVal(setEle, field_value);
									});
								}
							});
						}
					});
				}

				setFielValue(data, id);
			}

			extexam.ps = function(data, id) {

				if(data[id]['deleted_list']) {
					deleteList(id, data[id]['deleted_list']);
				}

				if(data[id]['items']) {
					$.each(data[id]['items'], function(ind, item){
						var ele = $('#'+id+'Box [name=ps_begdate]:last-child');
						if(ele.length > 0) {
							var row = ele.parent().parent();
							appendElement(row, row, item, id, (ind + 1));
						}
					});
				}
				setFielValue(data, id);
			}

			extexam.meds = function(data, id) {
				setFielValue(data, id);
			}

			extexam.med_hist = function(data, id) {
				setFielValue(data, id);
			}

			extexam.imm = function(data, id) {

				if(data[id]['deleted_list']) {
					deleteList(id, data[id]['deleted_list']);
				}

				if(data[id]['items']) {
					$.each(data[id]['items'], function(ind, item){
						var ele = $('#'+id+'Box span.cvx_code');
						if(ele.length > 0) {
							$.each(ele, function(inx, cEle){
								var cvxText = $(cEle).data('cvx');
								var cnt = $(cEle).data('id');
								if(item['imm_cvx_code'] == cvxText) {
									$.each(item, function(item_field, field_value){
										var setEle = $('#'+item_field+'_'+cnt);
										setInputVal(setEle, field_value);
									});
								}
							});
						}
					});
				}

				setFielValue(data, id);
			}

			extexam.well_full = function(data, id) {
				setFielValue(data, id);
			}

			extexam.hosp = function(data, id) {

				if(data[id]['deleted_list']) {
					deleteList(id, data[id]['deleted_list']);
				}

				if(data[id]['items']) {
					$.each(data[id]['items'], function(ind, item){
						var ele = $('#'+id+'Box [name=hosp_dt]:last-child');
						if(ele.length > 0) {
							var row = ele.parent().parent();
							appendElement(row, row, item, id, (ind + 1));
						}
					});
				}

				setFielValue(data, id);
			}

			extexam.pmh = function(data, id) {

				if(data[id]['deleted_list']) {
					deleteList(id, data[id]['deleted_list']);
				}

				if(data[id]['items']) {
					$.each(data[id]['items'], function(ind, item){
						var ele = $('#'+id+'Box [name=pmh_type]:last-child');
						if(ele.length > 0) {
							var row = ele.parent().parent();
							appendElement(row, row, item, id, (ind + 1));
						}
					});
				}
				
				setFielValue(data, id);
			}

			extexam.fh = function(data, id) {

				if(data[id]['deleted_list']) {
					deleteList(id, data[id]['deleted_list']);
				}

				if(data[id]['items']) {
					$.each(data[id]['items'], function(ind, item){
						var ele = $('#'+id+'Box [name=fh_who]:last-child');
						if(ele.length > 0) {
							var row = ele.parent().parent();
							appendElement(row, row, item, id, (ind + 1));
						}
					});
				}
				
				setFielValue(data, id);
			}

			extexam.ros2 = function(data, id) {
				setFielValue(data, id);
			}

			extexam.ortho_exam = function(data, id) {
				setFielValue(data, id);
				if(data[id]['multi_list']) {
					var multi_list = data[id]['multi_list'];
					$.each(multi_list, function(field, values){
						var ele = $('#'+field);
						if(ele.length > 0) {
							ele.val(values);
						}
					});

					$(".select-picker").multiselect('destroy');
					$(".select-picker").multiselect({ maxWidth: '200px'});
				}
			}

			extexam.review_nt = function(data, id) {
				setFielValue(data, id);
			}

			extexam.general_exam2 = function(data, id) {
				if(data && data[id]) {
					$.each(data[id], function(field, value){
						if(field.startsWith("tmp_ge_")) {
							if(value == '1') {
								$('#'+field+'_disp').css("display", "block");
							} else {
								$('#'+field+'_disp').css("display", "none");
							}
						}
					});
				}
				setFielValue(data, id);
			}

			extexam.instruct = function(data, id) {
				setFielValue(data, id);
			}

			extexam.assess = function(data, id) {
				setFielValue(data, id);
			}

			extexam.diag = function(data, id, estatus = false) {

				if(data[id]['deleted_list']) {
					deleteList(id, data[id]['deleted_list']);
				}

				if(data[id]['items']) {
					var totalFields = $('#'+id+'Box .dg_code_field');
					$.each(data[id]['items'], function(ind, item){
						var cnt = ind + 1;
						if(estatus === true && totalFields.length > 0) {
							cnt = totalFields.length + cnt;
						}

						var ele = $('#'+id+'Box [name=dg_code]:last-child');
						if(ele.length > 0) {
							var row = ele.parent().parent();
							appendElement(row, row, item, id, cnt, true);
						}

						var ele1 = $('#'+id+'Box [name=dg_plan]:last-child');
						if(ele1.length > 0) {
							var row1 = ele1.parent().parent();
							appendElement(row1, row, item, id, cnt);
						}

						var ele2 = $('#'+id+'Box [name=dg_goal]:last-child');
						if(ele2.length > 0) {
							var row2 = ele2.parent().parent();
							appendElement(row2, row, item, id, cnt);
						}
					});
				}

				var tableEle = $('#'+id+'Box [name^="dg_code"], #'+id+'Box [name^="cp_dg_code"]');
				$.each(tableEle, function(ti, tr) {
					$(tr).parent().prev().html("&nbsp;"+(ti+1)+"&nbsp;).&nbsp;");
				});

				setFielValue(data, id);
			}

			function deleteList(bar_id, delList) {
				$.each(delList, function(i, item){
					$.each(item, function(ik, id){
						var ele = $("#"+bar_id+"Box [name^='"+i+"_'][value='"+id+"']");
						if(ele.length > 0) {
							if(bar_id == 'diag') {
								var row = $(ele).parent().parent();
								var row1 = $(row).next();
								var row2 = $(row1).next();

								$(row).remove();
								$(row1).remove();
								$(row2).remove();
							} else {
								$(ele).parent().parent().remove();
							}
						}
					});
				});
			}

			var setFielValue = function(data, id) {
				if(data && data[id]) {
					$.each(data[id], function(field, value){
						var ele = $('#'+id+'Box [name='+field+']');
						setInputVal(ele, value);
					});
				}
			}

			var appendElement = function(ele, insert, item, id = '', cnt = '1', seq = true) {
				var row = ele;
				var rowClone = row.clone();

				var cloneEles = rowClone.find('[name]');
				$.each(cloneEles, function(inx, cEle){
					var cName = $(cEle).attr('name');
					$(cEle).attr('name', 'cp_'+cName+'_'+(cnt));

					var cId = $(cEle).attr('id');
					$(cEle).attr('id', 'cp_'+cId+'_'+(cnt));

					if(cName == "dg_code") {
						$(cEle).attr("onclick",'get_diagnosis1("cp_dg_code_'+cnt+'","cp_tmp_dg_desc_'+cnt+'","cp_dg_begdt_'+cnt+'","cp_dg_title_'+cnt+'","cp_dg_type_'+cnt+'")');
						$(cEle).addClass("dg_code_field");
					}

					if(item.hasOwnProperty(cName)) {
						setInputVal($(cEle), item[cName]);
					}
				});

				if(id == 'diag') {
					var ele1 = rowClone.find('a.css_button_small');
					$.each(ele1, function(inx, cele1){
						if($(cele1).text() == "Save Plan" || $(cele1).text() == "Save Goal") {
							$(cele1).hide();
						}
					});
				}

				rowClone.insertBefore(insert);
			}

			var setInputVal = function(ele, eValue) {
				var value = eValue != "" ? decodeHtmlspecialChars(eValue) : "";

				if(ele.length > 0) {
					if($(ele).is("input:text")) {
						ele.val(value);
					} else if($(ele).is("select")) {
						$(ele).val(value);
						//$(ele).val(value).change();
					} else if($(ele).is("textarea")) {
						$(ele).val(value);
					} else if($(ele).is("input:checkbox")) {
						$.each(ele, function(inx, c_ele){
							if($(c_ele).val() == value) {
								$(c_ele).prop( "checked", true );
							} else {
								$(c_ele).prop( "checked", false );
							}
						});

						var eleName = $(ele).attr('name');
						if(eleName == 'db_sex_active') {
							TogglePair('sex_active_yes','sex_active_no');
						}
					}
				}
			}

			function set_selected_diag(items) {
				var frmdir = "<?php echo $frmdir; ?>";
				var boxId = "diag";

				if(frmdir == "dashboard") {
					boxId = "DBDiag";
				}

				var generateList = [];
				$.each(items, function(index, item) {
				    generateList.push({
				    	dg_begdt: "<?php echo date("m/d/Y") ?>",
						dg_code: item.itercode,
						dg_enddt: "",
						dg_id: "",
						dg_plan: "",
						dg_seq: "",
						tmp_dg_desc : item.title,
						dg_title: item.itercode+' - '+item.title,
				    });
				});
				

				if(generateList.length > 0) {
					var finalList = {};
					finalList[boxId] = {items : generateList};
					extexam.diag(finalList, boxId, true);
				}
			}

			function decodeHtmlspecialChars(text) {
				if(typeof text != "string") {
					return text;
				}

			    var map = {
			        '&amp;': '&',
			        '&#038;': "&",
			        '&lt;': '<',
			        '&gt;': '>',
			        '&quot;': '"',
			        '&#039;': "'",
			        '&#8217;': "’",
			        '&#8216;': "‘",
			        '&#8211;': "–",
			        '&#8212;': "—",
			        '&#8230;': "…",
			        '&#8221;': '”'
			    };

			    return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
			};
		</script>
		<?php
	}
	*/

	/*
	function ext_process_after_fetch($pid) {
		global $dt, $img, $surg, $hosp, $pmh, $fh, $diag, $allergies, $meds, $imm, $meds, $diag, $med_hist;


		//Date Pre Processing
		$dateFormat = self::getCurrentDateFormat();

		$filedList = array(
			'img_dt',
			'ps_begdate',
			'dg_begdt',
			'hosp_dt',
			'ps_begdate',
			'dg_enddt'
		);

		$sections = array(
			'img' => array(
				'img_dt',
			),
			'surg' => array(
				'begdate',
				'enddate'
			),
			'hosp' => array(
				'begdate',
				'enddate'
			),
			'fh' => array(
				'begdate',
				'enddate'
			),
			'imm' => array(
				'begdate',
				'enddate',
				'administered_date'
			),
			'pmh' => array(
				'begdate',
				'enddate'
			),
			'allergies' => array(
				'begdate',
				'enddate'
			),
			'meds' => array(
				'begdate',
				'enddate'
			),
			'med_hist' => array(
				'begdate',
				'enddate'
			),
			'diag' => array(
				'begdate',
				'enddate'
			)
		);

		foreach ($sections as $sk => $sItem) {
			if(isset(${$sk})) {
				foreach (${$sk} as $k => $value) {
					foreach ($sItem as $fk => $field) {
						if(isset(${$sk}[$k][$field])) {
							${$sk}[$k][$field] = self::getFormatedDate($dateFormat, $value[$field]);
						}
					}
				}
			}
		}

		foreach ($dt as $k => $value) {
			foreach ($filedList as $f) {
				if(substr($k,0,strlen($f)) == $f) {
					$dt[$k] = self::getFormatedDate($dateFormat, $value);
				}
			}
		}
	}*/

	function ext_general_exam2_module($pid) {
		global $dt;

		$px_sections = array(
			'gen' => 'ge_gen',
			'head' => 'ge_hd',
			'eyes' => 'ge_eye',
			'ears' => 'ge_earr',
			'nose' => 'ge_nose',
			'mouth' => 'ge_mouth',
			'throat' => 'ge_thrt',
			'neck' => 'ge_nk',
			'thyroid' => 'ge_thy',
			'lymph' => 'ge_lym',
			'breast' => 'ge_brr',
			'cardio' => 'ge_cr',
			'pulmo' => 'ge_pul',
			'gastro' => 'ge_gi',
			'neuro' => 'ge_neu',
			'musc' => 'ge_ms',
			'ext' => 'ge_ext',
			'dia' => 'ge_db',
			'test' => 'ge_te',
			'rectal' => 'ge_rc',
			'skin' => 'ge_skin',
			'psych' => 'ge_psych'
		);

		foreach ($px_sections as $px_section => $px_field) {
			$checked = 0;
			foreach ($dt as $dt_k => $dt_value) {
				if(substr($dt_k,0,strlen($px_field)) == $px_field) {
					if(!empty($dt_value)) {
						$checked = 1;
					}
				}
			}

			if($checked === 1) {
				$dt['tmp_ge_'.$px_section] = $checked;
				$dt['tmp_ge_'.$px_section.'_disp'] = 'block';
			} else if($checked === 0){
				$dt['tmp_ge_'.$px_section] = $checked;
				$dt['tmp_ge_'.$px_section.'_disp'] = 'none';
			}

		}

	}

	public static function getFormatedDate($format = 'd-m-Y', $value) {
		if($value == '0000-00-00') {
			return $value;
		}

		return (isset($value) && !empty($value)) ? date($format, strtotime($value)) : '';
	}

	public static function getCurrentDateFormat() {
		if ($GLOBALS['date_display_format'] == 1) {
		    $format = "m/d/Y";
		} elseif ($GLOBALS['date_display_format'] == 2) {
		    $format = "d/m/Y";
		} else {
		    $format = "Y-m-d";
		}

		return $format;
	}

	/*
	public static function ext_exam_process_request($pid) {
		//Date Pre Processing
		$fieldList = array(
			'img_dt',
			'cp_img_dt',
			'ps_begdate',
			'cp_ps_begdate',
			'dg_begdt',
			'cp_dg_begdt',
			'hosp_dt',
			'cp_hosp_dt',
			'ps_begdate',
			'cp_ps_begdate',
			'dg_enddt',
			'cp_dg_enddt'
		);

		foreach ($_POST as $k => $var) {
			foreach ($fieldList as $fv) {
				if(substr($k,0,strlen($fv)) == $fv) {
					$_POST[$k] = self::getFormatedDate('Y-m-d', $var);
				}
			}
		}
	}*/

	function isDateTime($x) {
		$dateFormat = self::getCurrentDateFormat();
    	return (date($dateFormat, strtotime($x)) == $x);
	}

	function ext_exam_before_process_form($k, &$var) {
		global $data;

		$ge_sections = array('gen', 'head', 'eyes', 'ears', 'nose', 'mouth', 'throat', 'neck', 'thyroid', 'lymph', 'breast', 'cardio', 'pulmo', 'gastro', 'neuro', 'musc', 'ext', 'dia', 'test', 'rectal', 'skin', 'psych');

		foreach($ge_sections as $section) {
			if('tmp_ge_'.$section == $k) {
				$data[$k] = $var;
			}
		}
	}

	/*
	function ext_exam_process_form($k, $var) {
		global $img, $data, $cp_img, $surg, $cp_surg, $hosp, $cp_pmh, $pmh, $cp_hosp, $fh, $cp_fh, $diag, $cp_diag;

		if(!isset($cp_img)) {
			$cp_img = array();
		}

		if(!isset($cp_surg)) {
			$cp_surg = array();
		}

		if(!isset($cp_hosp)) {
			$cp_hosp = array();
		}

		if(!isset($cp_pmh)) {
			$cp_pmh = array();
		}

		if(!isset($cp_fh)) {
			$cp_fh = array();
		}

		if(!isset($cp_diag)) {
			$cp_diag = array();
		}
		
		if(substr($k,0,7) == 'cp_img_') { 
			$img[$k] = $var;
			$cp_img[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,6) == 'cp_ps_') {
			$surg[$k] = $var;
			$cp_surg[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,8) == 'cp_hosp_') {
			$hosp[$k] = $var;
			$cp_hosp[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,7) == 'cp_pmh_') {
			$pmh[$k] = $var;
			$cp_pmh[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,6) == 'cp_fh_') {
			$fh[$k] = $var;
			$cp_fh[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,6) == 'cp_dg_') {
			$diag[$k] = $var;
			$cp_diag[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,7) == 'cp_tmp_') {
			unset($data[$k]);
		}
	}
	*/

	/*
	function ext_exam_form_action($pid) {
		global $img, $cp_img, $surg, $cp_surg, $hosp, $cp_hosp, $cp_pmh, $pmh, $fh, $cp_fh, $diag, $cp_diag, $dt, $encounter;

		$cp_img_list =  self::getSeqArray($cp_img);
		foreach ($cp_img_list as $cp_img_key => $cp_img_item) {
			$img_id=AddImageHistory($pid, $cp_img_item['cp_img_type'], $cp_img_item['cp_img_dt'], $cp_img_item['cp_img_nt']);
			if($img_id) LinkListEntry($pid, $img_id, $encounter, 'wmt_img_history');
		}

		$cp_surg_list =  self::getSeqArray($cp_surg);
		foreach ($cp_surg_list as $cp_surg_key => $cp_surg_item) {
			if(isset($cp_surg_item['cp_ps_title'])) {
				$surg_id=AddSurgery($pid,$cp_surg_item['cp_ps_begdate'],$cp_surg_item['cp_ps_title'], $cp_surg_item['cp_ps_comments'],$cp_surg_item['cp_ps_referredby'], $cp_surg_item['cp_ps_hospitalized']);
				if($surg_id) LinkListEntry($pid, $surg_id, $encounter, 'surgery');
			}
		}

		$cp_hosp_list =  self::getSeqArray($cp_hosp);
		foreach ($cp_hosp_list as $cp_hosp_key => $cp_hosp_item) {
			if(isset($cp_hosp_item['cp_hosp_why'])) {
  				$hosp_id=AddHospitalization($pid,$cp_hosp_item['cp_hosp_dt'], $cp_hosp_item['cp_hosp_why'],$cp_hosp_item['cp_hosp_type'], $cp_hosp_item['cp_hosp_nt']);
  				if($hosp_id) LinkListEntry($pid, $hosp_id, $encounter, 'hospitalization');
  			}
		}

		$cp_pmh_list =  self::getSeqArray($cp_pmh);
		foreach ($cp_pmh_list as $cp_pmh_key => $cp_pmh_item) {
			if(isset($cp_pmh_item['cp_pmh_type'])) {
		  		$mh_id=AddMedicalHistory($pid,$cp_pmh_item['cp_pmh_type'],'',$cp_pmh_item['cp_pmh_nt']);
				if($mh_id) LinkListEntry($pid, $mh_id, $encounter, 'wmt_med_history');
			}
		}

		$cp_fh_list =  self::getSeqArray($cp_fh);
		foreach ($cp_fh_list as $cp_fh_key => $cp_fh_item) {
			if(isset($cp_fh_item['cp_fh_who'])) {
		  		$fh_id=AddFamilyHistory($pid,$cp_fh_item['cp_fh_who'],$cp_fh_item['cp_fh_type'],$cp_fh_item['cp_fh_nt'], $cp_fh_item['cp_fh_dead'],$cp_fh_item['cp_fh_age'],$cp_fh_item['cp_fh_age_dead']);
				if($fh_id) LinkListEntry($pid, $fh_id, $encounter, 'wmt_family_history');
			}
		}

		$cp_dg_list =  self::getSeqArray($cp_diag);
		foreach ($cp_dg_list as $cp_dg_key => $cp_dg_item) {
			if(isset($cp_dg_item['cp_dg_code'])) {
				AddDiagnosis($pid,$encounter,$cp_dg_item['cp_dg_type'],$cp_dg_item['cp_dg_code'], $cp_dg_item['cp_dg_title'],$cp_dg_item['cp_dg_plan'],$cp_dg_item['cp_dg_begdt'], $cp_dg_item['cp_dg_enddt'],$cp_dg_item['cp_dg_seq']);
			}
		}
	}
	*/

	public function deleteAllList($pid) {
		$img=GetImageHistory($pid);
		$surg=GetList($pid, 'surgery');
		$allergies=GetList($pid, 'allergy');
		$hosp=GetList($pid, 'hospitalization');
		$pmh=GetMedicalHistory($pid);
		$fh=GetFamilyHistory($pid);
		$diag=GetProblemsWithDiags($pid);

		// foreach ($img as $k => $item) {
		// 	if(isset($item['id'])) {
		// 		DeleteListItem($pid, $item['id'], $item['img_num_links'],'wmt_img_history');
		// 	}
		// }

		// foreach ($allergies as $k => $item) {
		// 	DeleteListItem($pid, $item['id'], $item['num_links'],'allergy');
		// }

		foreach ($surg as $k => $item) {
			DeleteListItem($pid, $item['id'], $item['num_links'],'surgery');
		}

		// foreach ($hosp as $k => $item) {
		// 	DeleteListItem($pid, $item['id'], $item['num_links'], 'hospitalization');
		// }

		// foreach ($pmh as $k => $item) {
		// 	DeleteListItem($pid,$item['id'], $item['pmh_num_links'],'wmt_med_history');
		// }

		// foreach ($fh as $k => $item) {
		// 	DeleteListItem($pid,$item['id'],$item['fh_num_links'],'wmt_family_history');
		// }

		foreach ($diag as $k => $item) {
			DeleteListItem($pid,$item['id'],'','medical_problem');
		}
	}

	public static function deleteList() {
		global $encounter, $pid, $bar_id, $formData, $copy_action;

		if($copy_action != 'replace') {
			return true;
		}

		if(!isset($encounter) || $encounter == '') {
			return true;
		}

		if(!isset($pid) || $pid == '') {
			return true;
		}

		if($bar_id == 'img') {
			$img=GetImageHistory($pid, $encounter);
			foreach ($img as $k => $item) {
				if(isset($item['id'])) {
					//DeleteListItem($pid, $item['id'], $item['img_num_links'],'wmt_img_history');
					UnlinkListEntry($pid,$item['id'],$encounter,'wmt_img_history');
					$formData['img']['deleted_list']['img_id'][] = $item['id'];
				}
			}
		}

		if($bar_id == 'all' || $bar_id == 'global') {
			// $allergies=GetList($pid, 'allergy', $encounter);
			// foreach ($allergies as $k => $item) {
			// 	DeleteListItem($pid, $item['id'], $item['num_links'],'allergy');
			// 	$formData['all']['deleted_list']['all_id'][] = $item['id'];
			// }
		}

		if($bar_id == 'ps') {
			$surg=GetList($pid, 'surgery', $encounter);
			foreach ($surg as $k => $item) {
				//DeleteListItem($pid, $item['id'], $item['num_links'],'surgery');
				UnlinkListEntry($pid,$item['id'],$encounter,'surgery');
				$formData['ps']['deleted_list']['ps_id'][] = $item['id'];
			}
		}

		if($bar_id == 'hosp') {
			$hosp=GetList($pid, 'hospitalization', $encounter);
			foreach ($hosp as $k => $item) {
				//DeleteListItem($pid, $item['id'], $item['num_links'], 'hospitalization');
				UnlinkListEntry($pid,$item['id'],$encounter,'hospitalization');
				$formData['hosp']['deleted_list']['hosp_id'][] = $item['id'];
			}
		}

		if($bar_id == 'pmh') {
			$pmh=GetMedicalHistory($pid, $encounter);
			foreach ($pmh as $k => $item) {
				//DeleteListItem($pid,$item['id'], $item['pmh_num_links'],'wmt_med_history');
				UnlinkListEntry($pid,$item['id'],$encounter,'wmt_med_history');
				$formData['pmh']['deleted_list']['pmh_id'][] = $item['id'];
			}
		}

		if($bar_id == 'fh') {
			$fh=GetFamilyHistory($pid,$encounter);
			foreach ($fh as $k => $item) {
				//DeleteListItem($pid,$item['id'],$item['fh_num_links'],'wmt_family_history');
				UnlinkListEntry($pid,$item['id'],$encounter,'wmt_family_history');
				$formData['fh']['deleted_list']['fh_id'][] = $item['id'];
			}
		}

		if($bar_id == 'diag' || $bar_id == 'global') {
			$diag=GetProblemsWithDiags($pid, 'encounter', $encounter);
			foreach ($diag as $k => $item) {
				//DeleteListItem($pid,$item['id'],'','medical_problem');
				UnLinkDiagnosis($pid,$item['id'],$encounter);
				$formData['diag']['deleted_list']['dg_id'][] = $item['id'];
			}
		}
	}

	public static function getSeqArray($data = array()) {
		$tmp_array = array();
		foreach ($data as $field => $value) {
			$fieldTA = explode('_', $field);
			$tmpfieldTA = $fieldTA;
			$fieldTAI = end($fieldTA);
			$tmpI = '';
			$tmpfieldName = '';

			if(is_numeric($fieldTAI)) {
				$tmpI = ($fieldTAI - 1);
				array_pop($tmpfieldTA);
				$tmpfieldName = implode("_", $tmpfieldTA);
			}

			if($tmpI >= 0 && !empty($tmpfieldName)) {
				$tmp_array[$tmpI][$tmpfieldName] = $value;
			}
		}

		return $tmp_array;
	}

	function ext_exam_top_section($request, $pid) {
		global $frmn, $frmdir, $encounter, $id;

		$tmp_req['frmn'] = $frmn;
		$tmp_req['frmdir'] = $frmdir;
		$tmp_req['id'] = $id;
		$tmp_req['encounter'] = $encounter;

		$requestStr = json_encode($tmp_req);
		if($frmn == "form_ext_exam2") {
			?>
			<div class="global_copy_container">
				<input type="hidden" disabled="disabled" name="global_request_data" id="global_request_data" value='<?php echo $requestStr; ?>'>
				<a href="javascript: void(0)" class="globalConfigLink" onClick="globalCopy(event, '<?php echo $frmdir; ?>', '<?php echo $encounter; ?>')">Global Copy</a>
			</div>
			<?php
		}
	}

	function ext_exam_generateChapter($request, $encounter, $id, $bar_id, $bottom_bar, $title, $frmn, $frmdir) {
		$tmp_barId = $bar_id;

		$sectionList = array(
			'cc',
			'hpi',
			'ros2',
			'ortho_exam',
			'general_exam2',
			'gyn_exam',
			'instruct',
			'assess',
			'diag'
		);

		if($bottom_bar == 2) {
			$tmp_barId .= "Bottom";
		}
		$tmp_barId .= "Bar";
		
		$tmp_req = array();
		$tmp_req['bar_id'] = $bar_id;
		$tmp_req['tmp_barId'] = $tmp_barId;
		$tmp_req['frmn'] = $frmn;
		$tmp_req['frmdir'] = $frmdir;
		$tmp_req['id'] = $id;
		$tmp_req['encounter'] = $encounter;

		$requestStr = json_encode($tmp_req);
		if($frmn == "form_ext_exam2" && in_array($bar_id, $sectionList)) {
		?>
			<input type="hidden" disabled="disabled" name="<?php echo $bar_id.'_request_data'; ?>" id="<?php echo $bar_id.'_request_data'; ?>" value='<?php echo $requestStr; ?>'>
			<a href="javascript: void(0)" class="configLink" onClick="copyConfig(event, '<?php echo $tmp_barId; ?>', '<?php echo $bar_id; ?>', '<?php echo $frmdir; ?>', '<?php echo $encounter; ?>')">Section Copy</a>
		<?php
		}
	}
}