
function setOrthoGeneralNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_ge_', 'norm_exam');
		document.getElementById('oe_ge_distress').selectedIndex = 1;
	} else {
		clearExamSection(practice, 'oe_spg_');
	}
}

function setOrthoPostureNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_spc_', 'norm_exam');
	} else {
		clearExamSection(practice, 'oe_spc_');
	}
}

function setOrthoNeuroNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_spt_', 'norm_exam');
	} else {
		clearExamSection(practice, 'oe_spt_');
	}
}

function setOrthoOrthoNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_spl_', 'norm_exam');
	} else {
		clearExamSection(practice, 'oe_spl_');
	}
}

function setOrthoPalpationNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_mtr_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
			if(document.forms[0].elements[i].name.indexOf('oe_mtr_') != 0) continue;
		}
	} else {
		clearExamSection(practice, 'oe_mtr_');
	}
}

function setOrthoRomNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_sns_', 'norm_exam');
	} else {
		clearExamSection(practice, 'oe_sns_');
	}
}

function setOrthoMuscleNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_rfx_', 'norm_exam');
	} else {
		clearExamSection(practice, 'oe_rfx_');
	}
}

function setOrthoTendonNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_skin_', 'norm_exam');
		var l = document.forms[0].elements.length;
		for (i=0; i<l; i++) {
		}
	} else {
		clearExamSection(practice, 'oe_skin_');
	}
}

function setOrthoMyofascialNormal(practice, chk)
{
	if(chk.checked == true) {
		clearExamSection(practice, 'oe_myo_', 'norm_exam');
	} else {
		clearExamSection(practice, 'oe_myo_');
	}
}

function SetOrthoExamNormal(practiceId)
{
	var sex = '';
	if(length.arguments > 1) sex = arguments[1];
}

function ClearOrthoExam(practiceId)
{
	var sex = '';
	if(length.arguments > 1) sex = arguments[1];
	for(var key in ortho_sections) {
		clearExamSection(practiceId, 'oe_'+key);
	}
}

function showAllOrthoExamSections() {
	for(var key in ortho_sections) {
		showExamSection('tmp_oe_'+key+'_disp', 'tmp_oe_'+key+'_button_disp');
		document.getElementById('tmp_oe_'+key).checked = true;
	}
}

function hideAllOrthoExamSections() {
	for(var key in ortho_sections) {
		hideExamSection('tmp_oe_'+key+'_disp', 'tmp_oe_'+key+'_button_disp');
		document.getElementById('tmp_oe_'+key).checked = false;
	}
}
