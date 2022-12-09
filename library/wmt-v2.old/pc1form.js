function toggleROStoNo()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      if(document.forms[0].elements[i].name.indexOf('ee1_rs_') != -1) {
        document.forms[0].elements[i].selectedIndex = '2';
      }
    }
    if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      if(document.forms[0].elements[i].name.indexOf('_hpi') != -1) {
        document.forms[0].elements[i].checked= false;
      }
		}
  }
}

function toggleROStoNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf('ee1_rs_') != -1) {
    	if(document.forms[0].elements[i].type.indexOf('select') != -1) {
        document.forms[0].elements[i].selectedIndex = '0';
      }
    	if(document.forms[0].elements[i].type.indexOf('check') != -1) {
    		if((document.forms[0].elements[i].name.indexOf('_rs_non_') != -1) &&
    				(document.forms[0].elements[i].name.indexOf('_rs_adopt') != -1)) {
        	document.forms[0].elements[i].checked=false;
				}
      }
    	if(document.forms[0].elements[i].type.indexOf('text') != -1) {
				document.forms[0].elements[i].value='';
      }
    }
  }
}

function ClearExam()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf('pc1_ge_') != -1) {
    	if(document.forms[0].elements[i].type.indexOf('select') != -1) {
        document.forms[0].elements[i].selectedIndex = '0';
      }
    	if(document.forms[0].elements[i].type.indexOf('check') != -1) {
        document.forms[0].elements[i].checked=false;
      }
    	if(document.forms[0].elements[i].type.indexOf('text') != -1) {
        if(document.forms[0].elements[i].name.indexOf('dictate') == -1) {
					document.forms[0].elements[i].value='';
				}
      }
    }
  }
}

function ClearConstitutional()
{
  if(document.forms[0].elements['ee1_rs_const_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_fev'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_fev_nt'].value='';
		document.forms[0].elements['ee1_rs_loss'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_loss_nt'].value='';
		document.forms[0].elements['ee1_rs_gain'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_gain_nt'].value='';
	}
}

function ClearSkin()
{
  if(document.forms[0].elements['ee1_rs_skin_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_rash'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_rash_nt'].value='';
		document.forms[0].elements['ee1_rs_skn_ecz'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_skn_ecz_nt'].value='';
	}
}

function ClearBreast()
{
  if(document.forms[0].elements['ee1_rs_breast_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_nip'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_nip_nt'].value='';
		document.forms[0].elements['ee1_rs_lmp'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_lmp_nt'].value='';
		document.forms[0].elements['ee1_rs_skn'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_skn_nt'].value='';
	}
}

function ClearNeurologic()
{
  if(document.forms[0].elements['ee1_rs_neuro_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_diz'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_diz_nt'].value='';
		document.forms[0].elements['ee1_rs_sz'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_sz_nt'].value='';
		document.forms[0].elements['ee1_rs_numb'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_numb_nt'].value='';
		document.forms[0].elements['ee1_rs_head'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_head_nt'].value='';
		document.forms[0].elements['ee1_rs_tremor'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_tremor_nt'].value='';
	}
}

function ClearMusculoskeletal()
{
  if(document.forms[0].elements['ee1_rs_msk_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_jnt'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_jnt_nt'].value='';
		document.forms[0].elements['ee1_rs_j_inf'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_j_inf_nt'].value='';
		document.forms[0].elements['ee1_rs_stiff'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_stiff_nt'].value='';
		document.forms[0].elements['ee1_rs_mpain'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_mpain_nt'].value='';
	}
}

function ClearEndocrine(sex)
{
  if(document.forms[0].elements['ee1_rs_endocrine_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_hair'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_hair_nt'].value='';
		document.forms[0].elements['ee1_rs_acne'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_acne_nt'].value='';
		document.forms[0].elements['ee1_rs_hotcold'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_hotcold_nt'].value='';
		document.forms[0].elements['ee1_rs_diabetes'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_diabetes_nt'].value='';
		document.forms[0].elements['ee1_rs_thyroid'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_thyroid_nt'].value='';
		document.forms[0].elements['ee1_rs_tired'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_tired_nt'].value='';
		if(sex == 'f') {
			document.forms[0].elements['ee1_rs_menses'].selectedIndex=0;
			document.forms[0].elements['ee1_rs_menses_nt'].value='';
		}
	}
}

function ClearGastrointestinal()
{
  if(document.forms[0].elements['ee1_rs_gastro_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_naus'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_naus_nt'].value='';
		document.forms[0].elements['ee1_rs_vomit'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_vomit_nt'].value='';
		document.forms[0].elements['ee1_rs_ref'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_ref_nt'].value='';
		document.forms[0].elements['ee1_rs_anal_p'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_anal_p_nt'].value='';
		document.forms[0].elements['ee1_rs_jaun'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_jaun_nt'].value='';
		document.forms[0].elements['ee1_rs_bow'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_bow_nt'].value='';
		document.forms[0].elements['ee1_rs_dia'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_dia_nt'].value='';
		document.forms[0].elements['ee1_rs_const'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_const_nt'].value='';
		document.forms[0].elements['ee1_rs_melena'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_melena_nt'].value='';
		document.forms[0].elements['ee1_rs_hematemesis'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_hematemesis_nt'].value='';
		document.forms[0].elements['ee1_rs_hematochezia'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_hematochezia_nt'].value='';
		document.forms[0].elements['ee1_rs_ab_pain'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_ab_pain_nt'].value='';
	}
}

function ClearCardiovascular()
{
  if(document.forms[0].elements['ee1_rs_cardio_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_cpain'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_cpain_nt'].value='';
		document.forms[0].elements['ee1_rs_breathe'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_breathe_nt'].value='';
		document.forms[0].elements['ee1_rs_swell'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_swell_nt'].value='';
		document.forms[0].elements['ee1_rs_palp'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_palp_nt'].value='';
		document.forms[0].elements['ee1_rs_trans'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_trans_nt'].value='';
		document.forms[0].elements['ee1_rs_hd'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_hd_nt'].value='';
	}
}

function ClearAllergic()
{
  if(document.forms[0].elements['ee1_rs_imm_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_hay'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_hay_nt'].value='';
		document.forms[0].elements['ee1_rs_med'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_med_nt'].value='';
		document.forms[0].elements['ee1_rs_auto'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_auto_nt'].value='';
		document.forms[0].elements['ee1_rs_supp'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_supp_nt'].value='';
		document.forms[0].elements['ee1_rs_ecz'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_ecz_nt'].value='';
	}
}

function ClearRespiratory()
{
  if(document.forms[0].elements['ee1_rs_resp_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_whz'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_whz_nt'].value='';
		document.forms[0].elements['ee1_rs_shrt'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_shrt_nt'].value='';
		document.forms[0].elements['ee1_rs_cgh'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_cgh_nt'].value='';
		document.forms[0].elements['ee1_rs_slp'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_slp_nt'].value='';
		document.forms[0].elements['ee1_rs_asthma'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_asthma_nt'].value='';
		document.forms[0].elements['ee1_rs_all'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_all_nt'].value='';
	}
}

function ClearEyes()
{
  if(document.forms[0].elements['ee1_rs_eyes_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_blr'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_blr_nt'].value='';
		document.forms[0].elements['ee1_rs_dbl'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_dbl_nt'].value='';
		document.forms[0].elements['ee1_rs_vis'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_vis_nt'].value='';
		document.forms[0].elements['ee1_rs_vloss'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_vloss_nt'].value='';
		document.forms[0].elements['ee1_rs_blind'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_blind_nt'].value='';
		document.forms[0].elements['ee1_rs_vpain'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_vpain_nt'].value='';
		document.forms[0].elements['ee1_rs_dry'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_dry_nt'].value='';
		document.forms[0].elements['ee1_rs_uv'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_uv_nt'].value='';
	}
}

function ClearENT()
{
  if(document.forms[0].elements['ee1_rs_ent_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_sore'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_sore_nt'].value='';
		document.forms[0].elements['ee1_rs_sin'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_sin_nt'].value='';
		document.forms[0].elements['ee1_rs_hear'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_hear_nt'].value='';
		document.forms[0].elements['ee1_rs_lymph'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_lymph_nt'].value='';
		document.forms[0].elements['ee1_rs_mass'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_mass_nt'].value='';
		document.forms[0].elements['ee1_rs_epain'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_epain_nt'].value='';
		document.forms[0].elements['ee1_rs_thrush'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_thrush_nt'].value='';
		document.forms[0].elements['ee1_rs_strep'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_strep_nt'].value='';
	}
}

function ClearLymph()
{
  if(document.forms[0].elements['ee1_rs_lymph_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_swl'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_swl_nt'].value='';
		document.forms[0].elements['ee1_rs_brse'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_brse_nt'].value='';
		document.forms[0].elements['ee1_rs_nose'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_nose_nt'].value='';
		document.forms[0].elements['ee1_rs_trait'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_trait_nt'].value='';
		document.forms[0].elements['ee1_rs_bld_dis'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_bld_dis_nt'].value='';
	}
}

function ClearPsychiatric()
{
  if(document.forms[0].elements['ee1_rs_psych_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_dep'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_dep_nt'].value='';
		document.forms[0].elements['ee1_rs_anx'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_anx_nt'].value='';
		document.forms[0].elements['ee1_rs_oth'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_oth_nt'].value='';
	}
}

function ClearGenitourinary()
{
  if(document.forms[0].elements['ee1_rs_gen_hpi'].checked==true) {
		document.forms[0].elements['ee1_rs_leak'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_leak_nt'].value='';
		document.forms[0].elements['ee1_rs_ret'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_ret_nt'].value='';
		document.forms[0].elements['ee1_rs_uti'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_uti_nt'].value='';
		document.forms[0].elements['ee1_rs_upain'].selectedIndex=0;
		document.forms[0].elements['ee1_rs_upain_nt'].value='';
	}
}

function SetExamNormal()
{
	// There is really no way to loop this any more
  document.forms[0].elements['pc1_ge_gen_norm'].checked=true;
  document.forms[0].elements['pc1_ge_gen_dev'].checked=true;
  document.forms[0].elements['pc1_ge_gen_alert'].checked=true;
  document.forms[0].elements['pc1_ge_gen_act'].checked=true;
  document.forms[0].elements['pc1_ge_gen_dis'].checked=true;
  document.forms[0].elements['pc1_ge_gen_jaun'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gen_waste'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gen_delay'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_eye_perrla'].checked=true;
  document.forms[0].elements['pc1_ge_hd_atra'].checked=true;
  document.forms[0].elements['pc1_ge_hd_norm'].checked=true;
  document.forms[0].elements['pc1_ge_nose_ery'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_nose_swell'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_mouth_moist'].checked=true;
  document.forms[0].elements['pc1_ge_thrt_ery'].checked=true;
  document.forms[0].elements['pc1_ge_thrt_exu'].checked=true;
  document.forms[0].elements['pc1_ge_nk_sup'].checked=true;
  document.forms[0].elements['pc1_ge_cr_norm'].selectedIndex='1';
  document.forms[0].elements['pc1_ge_cr_mur'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_cr_gall'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_cr_click'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_cr_rubs'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_cr_extra'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_pul_clear'].selectedIndex='1';
  document.forms[0].elements['pc1_ge_pul_rales'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_pul_whz'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_pul_ron'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_pul_dec'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gi_soft'].selectedIndex='1';
  document.forms[0].elements['pc1_ge_gi_tend'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gi_dis'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gi_scar'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gi_hern'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gi_bowel'].selectedIndex='1';
  document.forms[0].elements['pc1_ge_gi_hepa'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gi_spleno'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gi_fiss'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_gi_mass'].selectedIndex='2';
  document.forms[0].elements['pc1_ge_neu_ao'].selectedIndex='3';
  document.forms[0].elements['pc1_ge_psych_judge'].checked=true;
  document.forms[0].elements['pc1_ge_psych_orient'].checked=true;
  document.forms[0].elements['pc1_ge_psych_mood'].checked=true;
}
