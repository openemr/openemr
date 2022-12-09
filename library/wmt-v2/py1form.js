function toggleROStoNo()
{
  var i;
  var l = document.py1_form.elements.length;
  for (i=0; i<l; i++) {
    if(document.py1_form.elements[i].type.indexOf('select') != -1) {
      if(document.py1_form.elements[i].name.indexOf('py1_rs_') != -1) {
        document.py1_form.elements[i].selectedIndex = '2';
      }
    }
    if(document.py1_form.elements[i].type.indexOf('check') != -1) {
      if(document.py1_form.elements[i].name.indexOf('_hpi') != -1) {
        document.py1_form.elements[i].checked= false;
      }
		}
  }
}

function toggleROStoNull()
{
  var i;
  var l = document.py1_form.elements.length;
  for (i=0; i<l; i++) {
    if(document.py1_form.elements[i].type.indexOf('select') != -1) {
      if(document.py1_form.elements[i].name.indexOf('py1_rs_') != -1) {
        document.py1_form.elements[i].selectedIndex = '0';
      }
    }
  }
}

function ClearExam()
{
  var i;
  var l = document.py1_form.elements.length;
  for (i=0; i<l; i++) {
    if(document.py1_form.elements[i].name.indexOf('py1_ge_') != -1) {
    	if(document.py1_form.elements[i].type.indexOf('select') != -1) {
        document.py1_form.elements[i].selectedIndex = '0';
      }
    	if(document.py1_form.elements[i].type.indexOf('check') != -1) {
        document.py1_form.elements[i].checked=false;
      }
    	if(document.py1_form.elements[i].type.indexOf('text') != -1) {
        if(document.py1_form.elements[i].name.indexOf('dictate') == -1) {
					document.py1_form.elements[i].value='';
				}
      }
    }
  }
}

function ClearSME()
{
  var i;
  var l = document.py1_form.elements.length;
  for (i=0; i<l; i++) {
    if(document.py1_form.elements[i].name.indexOf('py1_dem_') != -1) {
    	if(document.py1_form.elements[i].type.indexOf('select') != -1) {
        document.py1_form.elements[i].selectedIndex = '0';
      }
    	if(document.py1_form.elements[i].type.indexOf('check') != -1) {
        document.py1_form.elements[i].checked=false;
      }
    	if(document.py1_form.elements[i].type.indexOf('text') != -1) {
				document.py1_form.elements[i].value='';
      }
    }
  }
}

function ClearDepression()
{
  var i;
  var l = document.py1_form.elements.length;
  for (i=0; i<l; i++) {
    if(document.py1_form.elements[i].name.indexOf('py1_dep_') != -1) {
    	if(document.py1_form.elements[i].type.indexOf('select') != -1) {
        document.py1_form.elements[i].selectedIndex = '0';
      }
    	if(document.py1_form.elements[i].type.indexOf('check') != -1) {
        document.py1_form.elements[i].checked=false;
      }
    	if(document.py1_form.elements[i].type.indexOf('text') != -1) {
				document.py1_form.elements[i].value='';
      }
    }
  }
}

function ClearAnxiety()
{
  var i;
  var l = document.py1_form.elements.length;
  for (i=0; i<l; i++) {
    if(document.py1_form.elements[i].name.indexOf('py1_anx_') != -1) {
    	if(document.py1_form.elements[i].type.indexOf('select') != -1) {
        document.py1_form.elements[i].selectedIndex = '0';
      }
    	if(document.py1_form.elements[i].type.indexOf('check') != -1) {
        document.py1_form.elements[i].checked=false;
      }
    	if(document.py1_form.elements[i].type.indexOf('text') != -1) {
				document.py1_form.elements[i].value='';
      }
    }
  }
}

function ClearConstitutional()
{
  if(document.py1_form.elements['py1_rs_const_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_fev'].selectedIndex=0;
		document.py1_form.elements['py1_rs_fev_nt'].value='';
		document.py1_form.elements['py1_rs_loss'].selectedIndex=0;
		document.py1_form.elements['py1_rs_loss_nt'].value='';
		document.py1_form.elements['py1_rs_gain'].selectedIndex=0;
		document.py1_form.elements['py1_rs_gain_nt'].value='';
		document.py1_form.elements['py1_rs_fatigue'].selectedIndex=0;
		document.py1_form.elements['py1_rs_fatigue_nt'].value='';
	}
}

function ClearSkin()
{
  if(document.py1_form.elements['py1_rs_skin_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_lmp'].selectedIndex=0;
		document.py1_form.elements['py1_rs_lmp_nt'].value='';
		document.py1_form.elements['py1_rs_rash'].selectedIndex=0;
		document.py1_form.elements['py1_rs_rash_nt'].value='';
		document.py1_form.elements['py1_rs_ml'].selectedIndex=0;
		document.py1_form.elements['py1_rs_ml_nt'].value='';
		document.py1_form.elements['py1_rs_skn'].selectedIndex=0;
		document.py1_form.elements['py1_rs_skn_nt'].value='';
	}
}

function ClearBreast()
{
  if(document.py1_form.elements['py1_rs_breast_hpi'].checked==true) {
	}
}

function ClearNeurologic()
{
  if(document.py1_form.elements['py1_rs_neuro_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_diz'].selectedIndex=0;
		document.py1_form.elements['py1_rs_diz_nt'].value='';
		document.py1_form.elements['py1_rs_sz'].selectedIndex=0;
		document.py1_form.elements['py1_rs_sz_nt'].value='';
		document.py1_form.elements['py1_rs_numb'].selectedIndex=0;
		document.py1_form.elements['py1_rs_numb_nt'].value='';
		document.py1_form.elements['py1_rs_strength'].selectedIndex=0;
		document.py1_form.elements['py1_rs_strength_nt'].value='';
	}
}

function ClearMusculoskeletal()
{
  if(document.py1_form.elements['py1_rs_msk_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_jnt'].selectedIndex=0;
		document.py1_form.elements['py1_rs_jnt_nt'].value='';
		document.py1_form.elements['py1_rs_stiff'].selectedIndex=0;
		document.py1_form.elements['py1_rs_stiff_nt'].value='';
		document.py1_form.elements['py1_rs_wk'].selectedIndex=0;
		document.py1_form.elements['py1_rs_wk_nt'].value='';
		document.py1_form.elements['py1_rs_mpain'].selectedIndex=0;
		document.py1_form.elements['py1_rs_mpain_nt'].value='';
	}
}

function ClearEndocrine()
{
  if(document.py1_form.elements['py1_rs_endocrine_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_hair'].selectedIndex=0;
		document.py1_form.elements['py1_rs_hair_nt'].value='';
		document.py1_form.elements['py1_rs_acne'].selectedIndex=0;
		document.py1_form.elements['py1_rs_acne_nt'].value='';
		document.py1_form.elements['py1_rs_hotcold'].selectedIndex=0;
		document.py1_form.elements['py1_rs_hotcold_nt'].value='';
		document.py1_form.elements['py1_rs_nightswt'].selectedIndex=0;
		document.py1_form.elements['py1_rs_nightswt_nt'].value='';
		document.py1_form.elements['py1_rs_flash'].selectedIndex=0;
		document.py1_form.elements['py1_rs_flash_nt'].value='';
		document.py1_form.elements['py1_rs_hb'].selectedIndex=0;
		document.py1_form.elements['py1_rs_hb_nt'].value='';
	}
}

function ClearGastrointestinal()
{
  if(document.py1_form.elements['py1_rs_gastro_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_naus'].selectedIndex=0;
		document.py1_form.elements['py1_rs_naus_nt'].value='';
		document.py1_form.elements['py1_rs_vomit'].selectedIndex=0;
		document.py1_form.elements['py1_rs_vomit_nt'].value='';
		document.py1_form.elements['py1_rs_app'].selectedIndex=0;
		document.py1_form.elements['py1_rs_app_nt'].value='';
		document.py1_form.elements['py1_rs_hb'].selectedIndex=0;
		document.py1_form.elements['py1_rs_hb_nt'].value='';
		document.py1_form.elements['py1_rs_dia'].selectedIndex=0;
		document.py1_form.elements['py1_rs_dia_nt'].value='';
		document.py1_form.elements['py1_rs_const'].selectedIndex=0;
		document.py1_form.elements['py1_rs_const_nt'].value='';
	}
}

function ClearCardiovascular()
{
  if(document.py1_form.elements['py1_rs_cardio_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_cpain'].selectedIndex=0;
		document.py1_form.elements['py1_rs_cpain_nt'].value='';
		document.py1_form.elements['py1_rs_breathe'].selectedIndex=0;
		document.py1_form.elements['py1_rs_breathe_nt'].value='';
		document.py1_form.elements['py1_rs_swell'].selectedIndex=0;
		document.py1_form.elements['py1_rs_swell_nt'].value='';
		document.py1_form.elements['py1_rs_palp'].selectedIndex=0;
		document.py1_form.elements['py1_rs_palp_nt'].value='';
	}
}

function ClearAllergic()
{
  if(document.py1_form.elements['py1_rs_imm_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_hay'].selectedIndex=0;
		document.py1_form.elements['py1_rs_hay_nt'].value='';
		document.py1_form.elements['py1_rs_med'].selectedIndex=0;
		document.py1_form.elements['py1_rs_med_nt'].value='';
	}
}

function ClearRespiratory()
{
  if(document.py1_form.elements['py1_rs_resp_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_whz'].selectedIndex=0;
		document.py1_form.elements['py1_rs_whz_nt'].value='';
		document.py1_form.elements['py1_rs_shrt'].selectedIndex=0;
		document.py1_form.elements['py1_rs_shrt_nt'].value='';
		document.py1_form.elements['py1_rs_slp'].selectedIndex=0;
		document.py1_form.elements['py1_rs_slp_nt'].value='';
		document.py1_form.elements['py1_rs_spu'].selectedIndex=0;
		document.py1_form.elements['py1_rs_spu_nt'].value='';
	}
}

function ClearEyes()
{
  if(document.py1_form.elements['py1_rs_eyes_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_blr'].selectedIndex=0;
		document.py1_form.elements['py1_rs_blr_nt'].value='';
		document.py1_form.elements['py1_rs_dbl'].selectedIndex=0;
		document.py1_form.elements['py1_rs_dbl_nt'].value='';
		document.py1_form.elements['py1_rs_blind'].selectedIndex=0;
		document.py1_form.elements['py1_rs_blind_nt'].value='';
		document.py1_form.elements['py1_rs_vpain'].selectedIndex=0;
		document.py1_form.elements['py1_rs_vpain_nt'].value='';
	}
}

function ClearENT()
{
  if(document.py1_form.elements['py1_rs_ent_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_sore'].selectedIndex=0;
		document.py1_form.elements['py1_rs_sore_nt'].value='';
		document.py1_form.elements['py1_rs_hear'].selectedIndex=0;
		document.py1_form.elements['py1_rs_hear_nt'].value='';
		document.py1_form.elements['py1_rs_hot'].selectedIndex=0;
		document.py1_form.elements['py1_rs_hot_nt'].value='';
		document.py1_form.elements['py1_rs_mass'].selectedIndex=0;
		document.py1_form.elements['py1_rs_mass_nt'].value='';
	}
}

function ClearLymph()
{
  if(document.py1_form.elements['py1_rs_lymph_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_swl'].selectedIndex=0;
		document.py1_form.elements['py1_rs_swl_nt'].value='';
		document.py1_form.elements['py1_rs_brse'].selectedIndex=0;
		document.py1_form.elements['py1_rs_brse_nt'].value='';
		document.py1_form.elements['py1_rs_nose'].selectedIndex=0;
		document.py1_form.elements['py1_rs_nose_nt'].value='';
	}
}

function ClearPsychiatric()
{
  if(document.py1_form.elements['py1_rs_psych_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_dep'].selectedIndex=0;
		document.py1_form.elements['py1_rs_dep_nt'].value='';
		document.py1_form.elements['py1_rs_anx'].selectedIndex=0;
		document.py1_form.elements['py1_rs_anx_nt'].value='';
		document.py1_form.elements['py1_rs_sui'].selectedIndex=0;
		document.py1_form.elements['py1_rs_sui_nt'].value='';
		document.py1_form.elements['py1_rs_hom'].selectedIndex=0;
		document.py1_form.elements['py1_rs_hom_nt'].value='';
	}
}

function ClearGenitourinary()
{
  if(document.py1_form.elements['py1_rs_gen_hpi'].checked==true) {
		document.py1_form.elements['py1_rs_leak'].selectedIndex=0;
		document.py1_form.elements['py1_rs_leak_nt'].value='';
		document.py1_form.elements['py1_rs_ret'].selectedIndex=0;
		document.py1_form.elements['py1_rs_ret_nt'].value='';
		document.py1_form.elements['py1_rs_low'].selectedIndex=0;
		document.py1_form.elements['py1_rs_low_nt'].value='';
		document.py1_form.elements['py1_rs_ed'].selectedIndex=0;
		document.py1_form.elements['py1_rs_ed_nt'].value='';
	}
}

function SetExamNormal()
{
	// There is really no way to loop this any more
  document.py1_form.elements['py1_ge_gen_norm'].checked=true;
  document.py1_form.elements['py1_ge_gen_dev'].checked=true;
  document.py1_form.elements['py1_ge_gen_groom'].checked=true;
  document.py1_form.elements['py1_ge_gen_dis'].checked=true;
  document.py1_form.elements['py1_ge_gen_jaun'].selectedIndex='2';
  document.py1_form.elements['py1_ge_gen_waste'].selectedIndex='2';
  document.py1_form.elements['py1_ge_gen_sleep'].selectedIndex='1';
  document.py1_form.elements['py1_ge_neu_ao'].selectedIndex='3';
  document.py1_form.elements['py1_ge_neu_cn'].selectedIndex='1';
  document.py1_form.elements['py1_ge_psych_judge'].checked=true;
  document.py1_form.elements['py1_ge_psych_orient'].checked=true;
  document.py1_form.elements['py1_ge_psych_memory'].checked=true;
  document.py1_form.elements['py1_ge_psych_mood'].checked=true;
}

function TotalDepression()
{
	var tot = new Number;
	tot = document.py1_form.elements['py1_dep_total'].value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
  t = parseInt(document.py1_form.elements['py1_dep_mood'].options[document.py1_form.elements['py1_dep_mood'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_guilt'].options[document.py1_form.elements['py1_dep_guilt'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_suicide'].options[document.py1_form.elements['py1_dep_suicide'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_in_early'].options[document.py1_form.elements['py1_dep_in_early'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_in_mid'].options[document.py1_form.elements['py1_dep_in_mid'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_in_late'].options[document.py1_form.elements['py1_dep_in_late'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_work'].options[document.py1_form.elements['py1_dep_work'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_retard'].options[document.py1_form.elements['py1_dep_retard'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_agitate'].options[document.py1_form.elements['py1_dep_agitate'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_anx_psych'].options[document.py1_form.elements['py1_dep_anx_psych'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_anx_som'].options[document.py1_form.elements['py1_dep_anx_som'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_som_gi'].options[document.py1_form.elements['py1_dep_som_gi'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_som_gen'].options[document.py1_form.elements['py1_dep_som_gen'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_genital'].options[document.py1_form.elements['py1_dep_genital'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_hypo'].options[document.py1_form.elements['py1_dep_hypo'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_weight'].options[document.py1_form.elements['py1_dep_weight'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
  t = parseInt(document.py1_form.elements['py1_dep_insight'].options[document.py1_form.elements['py1_dep_insight'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
	// alert("Our new total: "+new_tot);
	document.py1_form.elements['py1_dep_total'].value= new_tot;
}

function TotalAnxiety()
{
	var tot = new Number;
	tot = document.py1_form.elements['py1_anx_total'].value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
  t = parseInt(document.py1_form.elements['py1_anx_mood'].options[document.py1_form.elements['py1_anx_mood'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_tense'].options[document.py1_form.elements['py1_anx_tense'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_fear'].options[document.py1_form.elements['py1_anx_fear'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_insomnia'].options[document.py1_form.elements['py1_anx_insomnia'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_intellect'].options[document.py1_form.elements['py1_anx_intellect'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_somatic'].options[document.py1_form.elements['py1_anx_somatic'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_cardio'].options[document.py1_form.elements['py1_anx_cardio'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_resp'].options[document.py1_form.elements['py1_anx_resp'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_gastro'].options[document.py1_form.elements['py1_anx_gastro'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_anx_genito'].options[document.py1_form.elements['py1_anx_genito'].selectedIndex].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
	// alert("Our new total: "+new_tot);
	document.py1_form.elements['py1_anx_total'].value= new_tot;
}

function TotalSME()
{
	var tot = new Number;
	tot = document.py1_form.elements['py1_dem_tot'].value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
  t = parseInt(document.py1_form.elements['py1_dem_or_dt'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_or_loc'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_reg'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_att'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_rec'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_lang_name'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_lang_rep'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_lang_foll'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_lang_read'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_lang_write'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_dem_lang_copy'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
	// alert("Our new total: "+new_tot);
	document.py1_form.elements['py1_dem_tot'].value= new_tot;
}

function TotalAbnormalMovement()
{
	var tot = new Number;
	tot = document.py1_form.elements['py1_ge_ab_tot'].value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
  t = parseInt(document.py1_form.elements['py1_ge_ab_face'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_lips'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_jaw'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_tongue'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_upper'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_lower'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_neck'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_severe'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_incap'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_aware'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_prob'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
  t = parseInt(document.py1_form.elements['py1_ge_ab_denture'].value);
	if(!isNaN(t)) new_tot += t;
	new_tot= parseInt(new_tot);
	// alert("Our new total: "+new_tot);
	document.py1_form.elements['py1_ge_ab_tot'].value= new_tot;
}

function CheckEndTime(start, end, target)
{
	var startTime= document.getElementById(start).value;
	var endTime= document.getElementById(end).value;
	var totalTime= document.getElementById(target).value;
	// If the time was already set, just return
	if(totalTime != '') return true;
	if(endTime == '') {
		endTime= GetShortTimeStamp();
		document.getElementById(end).value= endTime;
	}
	var endMinute= parseInt(endTime.slice(-2));
	var endHour= parseInt(endTime.slice(0,2));
	var startMinute= parseInt(startTime.slice(-2));
	var startHour= parseInt(startTime.slice(0,2));
	// alert(startHour+'  '+startMinute+'  -  '+endHour+'  '+endMinute);
	if(isNaN(endHour) || isNaN(endMinute) || isNaN(startHour) || isNaN(startMinute)) return true;
	var endTotal= parseInt((endHour * 60) + endMinute);
	var startTotal= parseInt((startHour * 60) + startMinute);
	// alert(endTotal+'  ::  '+startTotal);
	var len= parseInt(endTotal - startTotal);
	if(isNaN(len)) return true;
	document.getElementById(target).value= len;
	return true;
}

