<?php

namespace OpenEMR\OemrAd;

class Exam2 {
	
	function __construct(){
	}

	/*
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
	*/

	/*
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

	}*/

	/*
	function ext_exam_before_process_form($k, &$var) {
		global $data;

		$ge_sections = array('gen', 'head', 'eyes', 'ears', 'nose', 'mouth', 'throat', 'neck', 'thyroid', 'lymph', 'breast', 'cardio', 'pulmo', 'gastro', 'neuro', 'musc', 'ext', 'dia', 'test', 'rectal', 'skin', 'psych');

		foreach($ge_sections as $section) {
			if('tmp_ge_'.$section == $k) {
				$data[$k] = $var;
			}
		}
	}*/

	/*
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
	*/

	/*
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
	}*/

	/*
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
	}*/

	/*
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
	*/
}