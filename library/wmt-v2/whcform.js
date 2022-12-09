function toggleHistoryToNo()
{
  var i;
  var l = document.acog_a.elements.length;
  for (i=0; i<l; i++) {
    var tmp = document.acog_a.elements[i].name;
    if(document.acog_a.elements[i].name.indexOf("aa_mh_") != -1) {
      if(document.acog_a.elements[i].id.indexOf("_no") != -1) {
        document.acog_a.elements[i].checked = true;
        document.acog_a.elements[i].value = '1';
      }
      if(document.acog_a.elements[i].id.indexOf("_yes") != -1) {
        document.acog_a.elements[i].checked = false;
      }
    }
  }
}

function toggleHistoryToNull()
{
  var i;
  var l = document.acog_a.elements.length;
  for (i=0; i<l; i++) {
    if(document.acog_a.elements[i].name.indexOf("aa_mh_") != -1) {
      document.acog_a.elements[i].checked = false;
      document.acog_a.elements[i].value = '';
    }
  }
}

function toggleBreast(which, tag)
{
  var i=510;
  if(tag == "r") i=710;
  if(document.getElementById(which).checked == true) {
    document.getElementById('w6_'+tag+'size_note').tabIndex=i; 
    document.getElementById('w6_'+tag+'location').tabIndex=i+10; 
    document.getElementById('w6_'+tag+'duration').tabIndex=i+20; 
    document.getElementById('w6_'+tag+'consist_soft').tabIndex=i+30; 
    document.getElementById('w6_'+tag+'consist_soft_note').tabIndex=i+40; 
    document.getElementById('w6_'+tag+'consist_firm').tabIndex=i+50; 
    document.getElementById('w6_'+tag+'consist_firm_note').tabIndex=i+60; 
    document.getElementById('w6_'+tag+'consist_mobile').tabIndex=i+70; 
    document.getElementById('w6_'+tag+'consist_mobile_note').tabIndex=i+80; 
    document.getElementById('w6_'+tag+'pre_meno').tabIndex=i+90; 
    document.getElementById('w6_'+tag+'post_meno').tabIndex=i+100; 
  } else {
    document.getElementById('w6_'+tag+'size_note').tabIndex=-1; 
    document.getElementById('w6_'+tag+'location').tabIndex=-1; 
    document.getElementById('w6_'+tag+'duration').tabIndex=-1; 
    document.getElementById('w6_'+tag+'consist_soft').tabIndex=-1; 
    document.getElementById('w6_'+tag+'consist_soft_note').tabIndex=-1; 
    document.getElementById('w6_'+tag+'consist_firm').tabIndex=-1; 
    document.getElementById('w6_'+tag+'consist_firm_note').tabIndex=-1; 
    document.getElementById('w6_'+tag+'consist_mobile').tabIndex=-1; 
    document.getElementById('w6_'+tag+'consist_mobile_note').tabIndex=-1; 
    document.getElementById('w6_'+tag+'pre_meno').tabIndex=-1; 
    document.getElementById('w6_'+tag+'post_meno').tabIndex=-1; 
  }
}

function toggleROStoNo()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf("select") != -1) {
      if(document.forms[0].elements[i].name.indexOf("_ros_") != -1) {
        document.forms[0].elements[i].selectedIndex = "2";
      }
    }
  }
}

function toggleFamilyExtraNo()
{
  document.forms[0].elements['wc_fh_any_breast'].selectedIndex = "2";
  document.forms[0].elements['wc_fh_any_uterine'].selectedIndex = "2";
  document.forms[0].elements['wc_fh_any_cervix'].selectedIndex = "2";
  document.forms[0].elements['wc_fh_any_ovarian'].selectedIndex = "2";
  document.forms[0].elements['wc_fh_any_colon'].selectedIndex = "2";
}

function toggleROStoNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf("select") != -1) {
      if(document.forms[0].elements[i].name.indexOf("_ros_") != -1) {
        document.forms[0].elements[i].selectedIndex = "-1";
      }
    }
  }
}

function toggleROSTexttoNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf("text") != -1) {
      if(document.forms[0].elements[i].name.indexOf("_ros_") != -1) {
        document.forms[0].elements[i].value = "";
      }
    }
  }
}

function toggleROSConsttoNo()
{
   document.forms[0].elements['wc_ros_fever'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_weight'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_headache'].selectedIndex = '2';
}

function toggleROSEyestoNo()
{
   document.forms[0].elements['wc_ros_blur'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_double'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_vision'].selectedIndex = '2';
}

function toggleROSImmtoNo()
{
   document.forms[0].elements['wc_ros_hay'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_meds'].selectedIndex = '2';
}

function toggleROSNeurotoNo()
{
   document.forms[0].elements['wc_ros_dizzy'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_seize'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_numb'].selectedIndex = '2';
}

function toggleROSEndotoNo()
{
   document.forms[0].elements['wc_ros_hair'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_heat'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_flash'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_thirst'].selectedIndex = '2';
}

function toggleROSGastrotoNo()
{
   document.forms[0].elements['wc_ros_nausea'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_diarrhea'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_abdomin'].selectedIndex = '2';
}

function toggleROSCardiotoNo()
{
   document.forms[0].elements['wc_ros_chest'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_breathe'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_swell'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_palp'].selectedIndex = '2';
}

function toggleROSResptoNo()
{
   document.forms[0].elements['wc_ros_wheeze'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_short'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_cough'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_apnea'].selectedIndex = '2';
}

function toggleROSMusctoNo()
{
   document.forms[0].elements['wc_ros_joint'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_weak'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_muscle'].selectedIndex = '2';
}

function toggleROSENTtoNo()
{
   document.forms[0].elements['wc_ros_throat'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_sinus'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_hear'].selectedIndex = '2';
}

function toggleROSHematoNo()
{
   document.forms[0].elements['wc_ros_gland'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_bruise'].selectedIndex = '2';
}

function toggleROSPsychtoNo()
{
   document.forms[0].elements['wc_ros_depress'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_anxiety'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_suicide'].selectedIndex = '2';
}

function toggleROSSkintoNo()
{
   document.forms[0].elements['wc_ros_rash'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_mole'].selectedIndex = '2';
}

function toggleROSBreasttoNo()
{
   document.forms[0].elements['wc_ros_nipple'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_lump'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_skin'].selectedIndex = '2';
}

function toggleROSGenitotoNo()
{
   document.forms[0].elements['wc_ros_leak'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_retain'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_burn'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_freq'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_vag'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_bleed'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_period'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_sex'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_fibroids'].selectedIndex = '2';
   document.forms[0].elements['wc_ros_inf'].selectedIndex = '2';
}

function ClearWcGenExam()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf('wc_ge_') != -1) {
    	if(document.forms[0].elements[i].type.indexOf('select') != -1) {
        document.forms[0].elements[i].selectedIndex = '0';
      }
    	if(document.forms[0].elements[i].type.indexOf('check') != -1) {
        document.forms[0].elements[i].checked=false;
      }
    	if(document.forms[0].elements[i].type.indexOf('text') != -1) {
        document.forms[0].elements[i].value='';
      }
    }
  }
}

function SetWcGenExamNormal()
{
	var client_id = "";
	var numargs = arguments.length;
	if(numargs) {
		client_id = arguments[0];
	}
	// There is really no way to loop this any more
  document.forms[0].elements['wc_ge_gen_norm'].checked=true;
  document.forms[0].elements['wc_ge_gen_dev'].checked=true;
  document.forms[0].elements['wc_ge_gen_groom'].checked=true;
  document.forms[0].elements['wc_ge_gen_dis'].checked=true;
  document.forms[0].elements['wc_ge_gen_jaun'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gen_waste'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gen_sleep'].selectedIndex='1';
	if(client_id == 'wcs') {
  	document.forms[0].elements['wc_ge_gen_sleep'].selectedIndex='0';
	}
  document.forms[0].elements['wc_ge_hd_atra'].checked=true;
  document.forms[0].elements['wc_ge_hd_norm'].checked=true;
	if(client_id == 'wcs') {
  	document.forms[0].elements['wc_ge_hd_atra'].checked=false;
  	document.forms[0].elements['wc_ge_hd_norm'].checked=false;
	}
  document.forms[0].elements['wc_ge_eyer_norm'].selectedIndex='1';
  document.forms[0].elements['wc_ge_eyel_norm'].selectedIndex='1';
	if(client_id == 'wcs') {
  	document.forms[0].elements['wc_ge_eyer_norm'].selectedIndex='0';
  	document.forms[0].elements['wc_ge_eyel_norm'].selectedIndex='0';
	}
  document.forms[0].elements['wc_ge_mouth_moist'].checked=true;
  document.forms[0].elements['wc_ge_thrt_ery'].checked=true;
  document.forms[0].elements['wc_ge_thrt_exu'].checked=true;
	if(client_id == 'wcs') {
  	document.forms[0].elements['wc_ge_mouth_moist'].checked=false;
  	document.forms[0].elements['wc_ge_thrt_ery'].checked=false;
  	document.forms[0].elements['wc_ge_thrt_exu'].checked=false;
	}
  document.forms[0].elements['wc_ge_nk_sup'].checked=true;
  document.forms[0].elements['wc_ge_nk_trach'].checked=true;
  document.forms[0].elements['wc_ge_thy_norm'].checked=true;
  document.forms[0].elements['wc_ge_thy_nod'].selectedIndex='2';

	if(client_id != 'wcs') {
  	document.forms[0].elements['wc_ge_thy_brit'].selectedIndex='2';
	}
  document.forms[0].elements['wc_ge_thy_tnd'].selectedIndex='2';
  document.forms[0].elements['wc_ge_brr_axil'].selectedIndex='2';
  document.forms[0].elements['wc_ge_brr_mass'].selectedIndex='2';
  document.forms[0].elements['wc_ge_nipr_ev'].selectedIndex='1';
  document.forms[0].elements['wc_ge_nipr_in'].selectedIndex='2';
  document.forms[0].elements['wc_ge_nipr_mass'].selectedIndex='2';
  document.forms[0].elements['wc_ge_nipr_dis'].selectedIndex='2';
  document.forms[0].elements['wc_ge_nipr_ret'].selectedIndex='2';
  document.forms[0].elements['wc_ge_brl_axil'].selectedIndex='2';
  document.forms[0].elements['wc_ge_brl_mass'].selectedIndex='2';
  document.forms[0].elements['wc_ge_nipl_ev'].selectedIndex='1';
  document.forms[0].elements['wc_ge_nipl_in'].selectedIndex='2';
  document.forms[0].elements['wc_ge_nipl_mass'].selectedIndex='2';
  document.forms[0].elements['wc_ge_nipl_dis'].selectedIndex='2';
  document.forms[0].elements['wc_ge_nipl_ret'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_norm'].selectedIndex='1';
  document.forms[0].elements['wc_ge_cr_mur'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_gall'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_click'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_rubs'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_extra'].selectedIndex='2';
  document.forms[0].elements['wc_ge_pul_clear'].selectedIndex='1';
  document.forms[0].elements['wc_ge_pul_rales'].selectedIndex='2';
  document.forms[0].elements['wc_ge_pul_whz'].selectedIndex='2';
  document.forms[0].elements['wc_ge_pul_ron'].selectedIndex='2';
  document.forms[0].elements['wc_ge_pul_dec'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gi_soft'].selectedIndex='1';
  document.forms[0].elements['wc_ge_gi_tend'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gi_dis'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gi_scar'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gi_hern'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gi_bowel'].selectedIndex='1';
  document.forms[0].elements['wc_ge_gi_hepa'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gi_spleno'].selectedIndex='2';
	if(client_id == 'wcs') {
  	document.forms[0].elements['wc_ge_gi_bowel'].selectedIndex='0';
  	document.forms[0].elements['wc_ge_gi_hepa'].selectedIndex='0';
  	document.forms[0].elements['wc_ge_gi_spleno'].selectedIndex='0';
	}
  document.forms[0].elements['wc_ge_neu_ao'].selectedIndex='3';
}

function SetWcGenExamAcute()
{
	var client_id = "";
	var numargs = arguments.length;
	if(numargs) {
		client_id = arguments[0];
	}
	// There is really no way to loop this any more
  document.forms[0].elements['wc_ge_gen_norm'].checked=true;
  document.forms[0].elements['wc_ge_gen_dev'].checked=true;
  document.forms[0].elements['wc_ge_gen_groom'].checked=true;
  document.forms[0].elements['wc_ge_gen_dis'].checked=true;
  document.forms[0].elements['wc_ge_thy_norm'].checked=true;
  document.forms[0].elements['wc_ge_cr_norm'].selectedIndex='1';
  document.forms[0].elements['wc_ge_cr_mur'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_gall'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_click'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_rubs'].selectedIndex='2';
  document.forms[0].elements['wc_ge_cr_extra'].selectedIndex='2';
  document.forms[0].elements['wc_ge_pul_clear'].selectedIndex='1';
  document.forms[0].elements['wc_ge_pul_rales'].selectedIndex='2';
  document.forms[0].elements['wc_ge_pul_whz'].selectedIndex='2';
  document.forms[0].elements['wc_ge_pul_ron'].selectedIndex='2';
  document.forms[0].elements['wc_ge_pul_dec'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gi_soft'].selectedIndex='1';
  document.forms[0].elements['wc_ge_gi_tend'].selectedIndex='2';
  document.forms[0].elements['wc_ge_gi_dis'].selectedIndex='2';
}

function SetIUDRemoveDate(thisDate, target)
{
	var fromDate = document.getElementById(thisDate).value;
	if(fromDate == 0 || fromDate == '') return false;	
	fromDate = new Date(fromDate);
	if(fromDate == 'Invalid Date') {
		alert("Not a Valid Date, Use 'YYYY-MM-DD' for Auto Calculations");
		return false;
	}
	var tNum = 0;
	if(document.getElementById('w9_skyla').checked == true) tNum = 3;
	if(document.getElementById('w9_mirena').checked == true) tNum = 5;
	if(document.getElementById('w9_paragard').checked == true) tNum = 10;
	if(!tNum) return false;
	tNum = parseInt(tNum * 365);
	var seconds = fromDate.getTime();
	seconds = seconds + (86400000 * tNum);
	var orderDate= new Date();
	orderDate.setTime(seconds);
  var myYear= orderDate.getFullYear();
  var myMonth= "00" + (orderDate.getMonth()+1);
  myMonth= myMonth.slice(-2);
  var myDays= "00" + orderDate.getDate();
  myDays= myDays.slice(-2);
	myYear= myYear + "-" + myMonth + "-" + myDays;
	document.getElementById(target).value= myYear;
}
