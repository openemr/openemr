
function ClearConstitutional(practice)
{
  if(document.ext1_form.elements['ee1_rs_const_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_fev'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_fev_nt'].value='';
		document.ext1_form.elements['ee1_rs_loss'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_loss_nt'].value='';
		document.ext1_form.elements['ee1_rs_gain'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_gain_nt'].value='';
	}
}

function ClearSkin(practice)
{
  if(document.ext1_form.elements['ee1_rs_skin_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_rash'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_rash_nt'].value='';
		document.ext1_form.elements['ee1_rs_ml'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_ml_nt'].value='';
	}
}

function ClearBreast(practice)
{
  if(document.ext1_form.elements['ee1_rs_breast_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_nip'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_nip_nt'].value='';
		document.ext1_form.elements['ee1_rs_lmp'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_lmp_nt'].value='';
		document.ext1_form.elements['ee1_rs_skn'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_skn_nt'].value='';
	}
}

function ClearNeurologic(practice)
{
  if(document.ext1_form.elements['ee1_rs_neuro_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_diz'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_diz_nt'].value='';
		document.ext1_form.elements['ee1_rs_sz'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_sz_nt'].value='';
		document.ext1_form.elements['ee1_rs_numb'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_numb_nt'].value='';
		document.ext1_form.elements['ee1_rs_head'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_head_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_strength'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_strength_nt'].value='';
		}
		document.ext1_form.elements['ee1_rs_tremor'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_tremor_nt'].value='';
		document.ext1_form.elements['ee1_rs_dysarthria'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_dysarthria_nt'].value='';
	}
}

function ClearMusculoskeletal(practice)
{
  if(document.ext1_form.elements['ee1_rs_msk_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_jnt'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_jnt_nt'].value='';
		document.ext1_form.elements['ee1_rs_stiff'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_stiff_nt'].value='';
		document.ext1_form.elements['ee1_rs_wk'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_wk_nt'].value='';
		document.ext1_form.elements['ee1_rs_mpain'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_mpain_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_ply_up'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_ply_up_nt'].value='';
			document.ext1_form.elements['ee1_rs_ply_dn'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_ply_dn_nt'].value='';
		}
	}
}

function ClearEndocrine(sex,practice)
{
  if(document.ext1_form.elements['ee1_rs_endocrine_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_hair'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_hair_nt'].value='';
		document.ext1_form.elements['ee1_rs_acne'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_acne_nt'].value='';
		document.ext1_form.elements['ee1_rs_hotcold'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_hotcold_nt'].value='';
		if(practice == 'capeneuro') {
			document.ext1_form.elements['ee1_rs_hot'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_hot_nt'].value='';
		}
		document.ext1_form.elements['ee1_rs_diabetes'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_diabetes_nt'].value='';
		document.ext1_form.elements['ee1_rs_thyroid'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_thyroid_nt'].value='';
		document.ext1_form.elements['ee1_rs_tired'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_tired_nt'].value='';
		document.ext1_form.elements['ee1_rs_voice'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_voice_nt'].value='';
		document.ext1_form.elements['ee1_rs_dysphagia'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_dysphagia_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_odyno'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_odyno_nt'].value='';
			document.ext1_form.elements['ee1_rs_polyuria'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_polyuria_nt'].value='';
			document.ext1_form.elements['ee1_rs_polydipsia'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_polydipsia_nt'].value='';
		}
		document.ext1_form.elements['ee1_rs_nightmare'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_nightmare_nt'].value='';
		document.ext1_form.elements['ee1_rs_nightswt'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_nightswt_nt'].value='';
		document.ext1_form.elements['ee1_rs_brittle'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_brittle_nt'].value='';
		document.ext1_form.elements['ee1_rs_sweat'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_sweat_nt'].value='';
		document.ext1_form.elements['ee1_rs_neck'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_neck_nt'].value='';
		if(sex == 'f') {
			document.ext1_form.elements['ee1_rs_menses'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_menses_nt'].value='';
		}
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_hirs'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_hirs_nt'].value='';
		}
	}
}

function ClearGastrointestinal(practice)
{
  if(document.ext1_form.elements['ee1_rs_gastro_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_naus'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_naus_nt'].value='';
		document.ext1_form.elements['ee1_rs_vomit'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_vomit_nt'].value='';
		document.ext1_form.elements['ee1_rs_ref'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_ref_nt'].value='';
		document.ext1_form.elements['ee1_rs_anal_p'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_anal_p_nt'].value='';
		document.ext1_form.elements['ee1_rs_jaun'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_jaun_nt'].value='';
		document.ext1_form.elements['ee1_rs_bow'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_bow_nt'].value='';
		document.ext1_form.elements['ee1_rs_dia'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_dia_nt'].value='';
		document.ext1_form.elements['ee1_rs_const'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_const_nt'].value='';
		document.ext1_form.elements['ee1_rs_melena'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_melena_nt'].value='';
		document.ext1_form.elements['ee1_rs_hematemesis'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_hematemesis_nt'].value='';
		document.ext1_form.elements['ee1_rs_hematochezia'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_hematochezia_nt'].value='';
	}
}

function ClearCardiovascular(practice)
{
  if(document.ext1_form.elements['ee1_rs_cardio_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_cpain'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_cpain_nt'].value='';
		document.ext1_form.elements['ee1_rs_breathe'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_breathe_nt'].value='';
		document.ext1_form.elements['ee1_rs_swell'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_swell_nt'].value='';
		document.ext1_form.elements['ee1_rs_palp'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_palp_nt'].value='';
		document.ext1_form.elements['ee1_rs_jaw'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_jaw_nt'].value='';
		document.ext1_form.elements['ee1_rs_arm'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_arm_nt'].value='';
		document.ext1_form.elements['ee1_rs_back'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_back_nt'].value='';
		document.ext1_form.elements['ee1_rs_acute'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_acute_nt'].value='';
	}
}

function ClearAllergic(practice)
{
  if(document.ext1_form.elements['ee1_rs_imm_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_hay'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_hay_nt'].value='';
		document.ext1_form.elements['ee1_rs_med'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_med_nt'].value='';
	}
}

function ClearRespiratory(practice)
{
  if(document.ext1_form.elements['ee1_rs_resp_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_whz'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_whz_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_shrt'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_shrt_nt'].value='';
		}
		document.ext1_form.elements['ee1_rs_cgh'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_cgh_nt'].value='';
		document.ext1_form.elements['ee1_rs_slp'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_slp_nt'].value='';
		document.ext1_form.elements['ee1_rs_spu'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_spu_nt'].value='';
		document.ext1_form.elements['ee1_rs_dys'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_dys_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_hemoptysis'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_hemoptysis_nt'].value='';
		}
		document.ext1_form.elements['ee1_rs_snore'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_snore_nt'].value='';
	}
}

function ClearEyes(practice)
{
  if(document.ext1_form.elements['ee1_rs_eyes_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_blr'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_blr_nt'].value='';
		document.ext1_form.elements['ee1_rs_dbl'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_dbl_nt'].value='';
		document.ext1_form.elements['ee1_rs_vis'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_vis_nt'].value='';
		document.ext1_form.elements['ee1_rs_vloss'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_vloss_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_blind'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_blind_nt'].value='';
			document.ext1_form.elements['ee1_rs_mac'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_mac_nt'].value='';
		}
		document.ext1_form.elements['ee1_rs_vpain'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_vpain_nt'].value='';
		document.ext1_form.elements['ee1_rs_dry'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_dry_nt'].value='';
	}
}

function ClearENT(practice)
{
  if(document.ext1_form.elements['ee1_rs_ent_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_sore'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_sore_nt'].value='';
		document.ext1_form.elements['ee1_rs_sin'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_sin_nt'].value='';
		document.ext1_form.elements['ee1_rs_hear'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_hear_nt'].value='';
		document.ext1_form.elements['ee1_rs_tin'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_tin_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_hot'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_hot_nt'].value='';
			document.ext1_form.elements['ee1_rs_lymph'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_lymph_nt'].value='';
		}
		document.ext1_form.elements['ee1_rs_mass'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_mass_nt'].value='';
		document.ext1_form.elements['ee1_rs_epain'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_epain_nt'].value='';
		if(practice == 'capeneuro') {
			document.ext1_form.elements['ee1_rs_nose'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_nose_nt'].value='';
		}
	}
}

function ClearLymph(practice)
{
  if(document.ext1_form.elements['ee1_rs_lymph_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_swl'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_swl_nt'].value='';
		document.ext1_form.elements['ee1_rs_brse'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_brse_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_nose'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_nose_nt'].value='';
		}
		document.ext1_form.elements['ee1_rs_trait'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_trait_nt'].value='';
	}
}

function ClearPsychiatric(practice)
{
  if(document.ext1_form.elements['ee1_rs_psych_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_dep'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_dep_nt'].value='';
		document.ext1_form.elements['ee1_rs_anx'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_anx_nt'].value='';
		document.ext1_form.elements['ee1_rs_sui'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_sui_nt'].value='';
		document.ext1_form.elements['ee1_rs_hom'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_hom_nt'].value='';
	}
}

function ClearGenitourinary(sex,practice)
{
  if(document.ext1_form.elements['ee1_rs_gen_hpi'].checked==true) {
		document.ext1_form.elements['ee1_rs_leak'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_leak_nt'].value='';
		document.ext1_form.elements['ee1_rs_ret'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_ret_nt'].value='';
		if(sex == 'f') {
			if(practice != 'capeneuro') {
				document.ext1_form.elements['ee1_rs_vag'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_vag_nt'].value='';
				document.ext1_form.elements['ee1_rs_bleed'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_bleed_nt'].value='';
				document.ext1_form.elements['ee1_rs_pp'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_pp_nt'].value='';
				document.ext1_form.elements['ee1_rs_sex'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_sex_nt'].value='';
				document.ext1_form.elements['ee1_rs_fib'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_fib_nt'].value='';
				document.ext1_form.elements['ee1_rs_inf'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_inf_nt'].value='';
			}
		}
		document.ext1_form.elements['ee1_rs_urg'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_urg_nt'].value='';
		if(practice != 'capeneuro') {
			document.ext1_form.elements['ee1_rs_hematuria'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_hematuria_nt'].value='';
			document.ext1_form.elements['ee1_rs_nocturia'].selectedIndex=0;
			document.ext1_form.elements['ee1_rs_nocturia_nt'].value='';
		}
		if(sex == 'f') {
			if(practice != 'capeneuro') {
				document.ext1_form.elements['ee1_rs_low'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_low_nt'].value='';
			}
		}
		if(sex == 'm') {
			if(practice != 'capeneuro') {
				document.ext1_form.elements['ee1_rs_ed'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_ed_nt'].value='';
				document.ext1_form.elements['ee1_rs_libido'].selectedIndex=0;
				document.ext1_form.elements['ee1_rs_libido_nt'].value='';
			}
		}
		document.ext1_form.elements['ee1_rs_weaks'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_weaks_nt'].value='';
		document.ext1_form.elements['ee1_rs_drib'].selectedIndex=0;
		document.ext1_form.elements['ee1_rs_drib_nt'].value='';
	}
}
