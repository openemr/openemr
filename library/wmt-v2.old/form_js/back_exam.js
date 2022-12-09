
function setSpineGeneralNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_spg_', 'norm_exam');
		document.getElementById('be_spg_or').checked = true;
		document.getElementById('be_spg_bh').selectedIndex = 1;
		document.getElementById('be_spg_gait').selectedIndex = 1;
		document.getElementById('be_spg_amb').selectedIndex = 1;
		document.getElementById('be_spg_t_l').selectedIndex = 1;
		document.getElementById('be_spg_t_r').selectedIndex = 1;
		document.getElementById('be_spg_h_l').selectedIndex = 1;
		document.getElementById('be_spg_h_r').selectedIndex = 1;
	} else {
		clearExamSection(practice, 'be_spg_');
	}
}

function setSpineCervicalNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_spc_', 'norm_exam');
		document.getElementById('be_spc_align').selectedIndex = 1;
		document.getElementById('be_spc_r_l').selectedIndex = 1;
		document.getElementById('be_spc_r_r').selectedIndex = 1;
		document.getElementById('be_spc_flex').selectedIndex = 1;
		document.getElementById('be_spc_ext').selectedIndex = 1;
		document.getElementById('be_spc_tender').selectedIndex = 1;
		document.getElementById('be_spc_mass').selectedIndex = 1;
		document.getElementById('be_spc_sp_l').selectedIndex = 1;
		document.getElementById('be_spc_sp_r').selectedIndex = 1;
	} else {
		clearExamSection(practice, 'be_spc_');
	}
}

function setSpineThoracicNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_spt_', 'norm_exam');
		document.getElementById('be_spt_align').selectedIndex = 1;
		document.getElementById('be_spt_tender').selectedIndex = 1;
		document.getElementById('be_spt_mass').selectedIndex = 1;
	} else {
		clearExamSection(practice, 'be_spt_');
	}
}

function setSpineLumbarNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_spl_', 'norm_exam');
		document.getElementById('be_spl_align').selectedIndex = 1;
		document.getElementById('be_spl_flex_rom').selectedIndex = 1;
		document.getElementById('be_spl_flex_pain').selectedIndex = 1;
		document.getElementById('be_spl_ext_pain').selectedIndex = 1;
		document.getElementById('be_spl_tender').selectedIndex = 1;
		document.getElementById('be_spl_mass').selectedIndex = 1;
	} else {
		clearExamSection(practice, 'be_spl_');
	}
}

function setSpineMotorNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_mtr_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('be_mtr_') != 0) continue;
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
				document.forms[0].elements[i].selectedIndex = 5;
			}
		}
	} else {
		clearExamSection(practice, 'be_mtr_');
	}
}

function setSpineSensoryNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_sns_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('be_sns_') != 0) continue;
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
				document.forms[0].elements[i].selectedIndex = 2;
			}
		}
	} else {
		clearExamSection(practice, 'be_sns_');
	}
}

function setSpineReflexNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_rfx_', 'norm_exam');
		document.getElementById('be_rfx_coor_l').selectedIndex = 1;
		document.getElementById('be_rfx_coor_r').selectedIndex = 1;
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('be_rfx_dtr_') == 0) {
				if(document.forms[0].elements[i].type.indexOf('select') != -1) {
					document.forms[0].elements[i].selectedIndex = 2;
				}
			}
			if(document.forms[0].elements[i].name.indexOf('be_rfx_path_') == 0) {
				if(document.forms[0].elements[i].type.indexOf('select') != -1) {
					document.forms[0].elements[i].selectedIndex = 1;
				}
			}
		}
	} else {
		clearExamSection(practice, 'be_rfx_');
	}
}

function setSpineSkinNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_skin_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('be_skin_') != 0) continue;
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
				document.forms[0].elements[i].selectedIndex = 1;
			}
		}
	} else {
		clearExamSection(practice, 'be_skin_');
	}
}

function setSpineMiscNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_misc_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('be_misc_') != 0) continue;
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
				document.forms[0].elements[i].selectedIndex = 1;
			}
		}
	} else {
		clearExamSection(practice, 'be_misc_');
	}
}

function setSpineVascularNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_vascular_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('be_vasc') != 0) continue;
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
				document.forms[0].elements[i].selectedIndex = 1;
			}
		}
	} else {
		clearExamSection(practice, 'be_vascular_');
	}
}

function setSpineLymphadenopathyNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_lymph_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('be_lymph') != 0) continue;
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
				document.forms[0].elements[i].selectedIndex = 1;
			}
		}
	} else {
		clearExamSection(practice, 'be_vascular_');
	}
}

function setSpineWaddellNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'be_waddell_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('be_wadd') != 0) continue;
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
				document.forms[0].elements[i].selectedIndex = 1;
			}
		}
	} else {
		clearExamSection(practice, 'be_waddell_');
	}
}

function SetBackExamNormal(practiceId)
{
	var sex = '';
	if(length.arguments > 1) sex = arguments[1];
	chk = document.getElementById('be_spg_norm_exam');
	chk.checked = true;
	setSpineGeneralNormal(practiceId, chk)
	chk = document.getElementById('be_spc_norm_exam');
	chk.checked = true;
	setSpineCervicalNormal(practiceId, chk)
	chk = document.getElementById('be_spt_norm_exam');
	chk.checked = true;
	setSpineThoracicNormal(practiceId, chk)
	chk = document.getElementById('be_spl_norm_exam');
	chk.checked = true;
	setSpineLumbarNormal(practiceId, chk)
	chk = document.getElementById('be_motor_norm_exam');
	chk.checked = true;
	setSpineMotorNormal(practiceId, chk)
	chk = document.getElementById('be_sns_norm_exam');
	chk.checked = true;
	setSpineSensoryNormal(practiceId, chk)
	chk = document.getElementById('be_rfx_norm_exam');
	chk.checked = true;
	setSpineReflexNormal(practiceId, chk)
	chk = document.getElementById('be_skin_norm_exam');
	chk.checked = true;
	setSpineSkinNormal(practiceId, chk)
	chk = document.getElementById('be_misc_norm_exam');
	chk.checked = true;
	setSpineMiscNormal(practiceId, chk)
	chk = document.getElementById('be_vascular_norm_exam');
	chk.checked = true;
	setSpineVascularNormal(practiceId, chk)
	chk = document.getElementById('be_lymph_norm_exam');
	chk.checked = true;
	setSpineLymphadenopathyNormal(practiceId, chk)
	chk = document.getElementById('be_waddell_norm_exam');
	chk.checked = true;
	setSpineWaddellNormal(practiceId, chk)
}

function ClearBackExam(practiceId)
{
	var sex = '';
	if(length.arguments > 1) sex = arguments[1];
	clearExamSection(practiceId, 'be_spg');
	clearExamSection(practiceId, 'be_spc');
	clearExamSection(practiceId, 'be_spt');
	clearExamSection(practiceId, 'be_spl');
	clearExamSection(practiceId, 'be_mtr');
	clearExamSection(practiceId, 'be_sns');
	clearExamSection(practiceId, 'be_rfx');
	clearExamSection(practiceId, 'be_skin');
	clearExamSection(practiceId, 'be_misc');
	clearExamSection(practiceId, 'be_vascular');
	clearExamSection(practiceId, 'be_lymph');
	clearExamSection(practiceId, 'be_waddell');
}

function showAllBackExamSections(patSex) {
	showExamSection('tmp_be_spg_disp', 'tmp_be_spg_button_disp');
	showExamSection('tmp_be_spc_disp', 'tmp_be_spc_button_disp');
	showExamSection('tmp_be_spt_disp', 'tmp_be_spt_button_disp');
	showExamSection('tmp_be_spl_disp', 'tmp_be_spl_button_disp');
	showExamSection('tmp_be_motor_disp', 'tmp_be_motor_button_disp');
	showExamSection('tmp_be_sns_disp', 'tmp_be_sns_button_disp');
	showExamSection('tmp_be_rfx_disp', 'tmp_be_rfx_button_disp');
	showExamSection('tmp_be_skin_disp', 'tmp_be_skin_button_disp');
	showExamSection('tmp_be_misc_disp', 'tmp_be_misc_button_disp');
	showExamSection('tmp_be_vascular_disp', 'tmp_be_vascular_button_disp');
	showExamSection('tmp_be_lymph_disp', 'tmp_be_lymph_button_disp');
	showExamSection('tmp_be_waddell_disp', 'tmp_be_waddell_button_disp');
	document.getElementById('tmp_be_spg').checked = true;
	document.getElementById('tmp_be_spc').checked = true;
	document.getElementById('tmp_be_spt').checked = true;
	document.getElementById('tmp_be_spl').checked = true;
	document.getElementById('tmp_be_motor').checked = true;
	document.getElementById('tmp_be_sns').checked = true;
	document.getElementById('tmp_be_rfx').checked = true;
	document.getElementById('tmp_be_skin').checked = true;
	document.getElementById('tmp_be_misc').checked = true;
	document.getElementById('tmp_be_vascular').checked = true;
	document.getElementById('tmp_be_lymph').checked = true;
	document.getElementById('tmp_be_waddell').checked = true;
}

function hideAllBackExamSections(patSex) {
	hideExamSection('tmp_be_spg_disp', 'tmp_be_spg_button_disp');
	hideExamSection('tmp_be_spc_disp', 'tmp_be_spc_button_disp');
	hideExamSection('tmp_be_spt_disp', 'tmp_be_spt_button_disp');
	hideExamSection('tmp_be_spl_disp', 'tmp_be_spl_button_disp');
	hideExamSection('tmp_be_motor_disp', 'tmp_be_motor_button_disp');
	hideExamSection('tmp_be_sns_disp', 'tmp_be_sns_button_disp');
	hideExamSection('tmp_be_rfx_disp', 'tmp_be_rfx_button_disp');
	hideExamSection('tmp_be_skin_disp', 'tmp_be_skin_button_disp');
	hideExamSection('tmp_be_misc_disp', 'tmp_be_misc_button_disp');
	hideExamSection('tmp_be_vascular_disp', 'tmp_be_misc_button_disp');
	hideExamSection('tmp_be_lymph_disp', 'tmp_be_misc_button_disp');
	hideExamSection('tmp_be_waddell_disp', 'tmp_be_waddell_button_disp');
	document.getElementById('tmp_be_spg').checked = false;
	document.getElementById('tmp_be_spc').checked = false;
	document.getElementById('tmp_be_spt').checked = false;
	document.getElementById('tmp_be_spl').checked = false;
	document.getElementById('tmp_be_motor').checked = false;
	document.getElementById('tmp_be_sns').checked = false;
	document.getElementById('tmp_be_rfx').checked = false;
	document.getElementById('tmp_be_skin').checked = false;
	document.getElementById('tmp_be_misc').checked = false;
	document.getElementById('tmp_be_vascular').checked = false;
	document.getElementById('tmp_be_lymph').checked = false;
	document.getElementById('tmp_be_waddell').checked = false;
}
