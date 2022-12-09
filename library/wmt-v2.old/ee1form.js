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
    if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      if(document.forms[0].elements[i].name.indexOf('ee1_rs_') != -1) {
        document.forms[0].elements[i].selectedIndex = '0';
      }
    }
  }
}

function toggleFamilyExtraNo()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      if(document.forms[0].elements[i].name.indexOf('tmp_fh_rs_') != -1) {
        document.forms[0].elements[i].selectedIndex = '2';
      }
    }
  }
}

function ClearExam()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf('ee1_ge_') != -1) {
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
    if(document.forms[0].elements[i].name.indexOf('ee1_ms_nt') != -1) {
			document.forms[0].elements[i].value='';
		}
  }
}

function setGEGeneralNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_gen_nt'].value=
				'Alert and fully oriented in no apparent distress. Voice is strong '+
				'and clear. Breathing quiet and non-labored on room air. Mesomorphic '+
				'body habitus.';
		} else {
			clearGESection(practice, 'ge_gen_', 'norm_exam');
  		document.forms[0].elements['ee1_ge_gen_norm'].checked=true;
  		document.forms[0].elements['ee1_ge_gen_dev'].checked=true;
  		document.forms[0].elements['ee1_ge_gen_groom'].checked=true;
  		document.forms[0].elements['ee1_ge_gen_dis'].checked=true;
			if(practice != 'sfa') {
  			document.forms[0].elements['ee1_ge_gen_jaun'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_gen_waste'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_gen_sleep'].selectedIndex='1';
			}
			if(practice == 'cffm') {
  			document.forms[0].elements['ee1_ge_gen_sleep'].selectedIndex='0';
			}
		}
	} else {
		clearGESection(practice, 'ge_gen_');
	}
}

function setGEHeadNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_hd_nt'].value=
				'No edema or erythema. No cutaneous lesions. Facial movement full '+
				'and symmetric';
		} else {
			clearGESection(practice, 'ge_hd_', 'norm_exam');
  		document.forms[0].elements['ee1_ge_hd_atra'].checked=true;
  		document.forms[0].elements['ee1_ge_hd_norm'].checked=true;
  		document.forms[0].elements['ee1_ge_hd_feat'].selectedIndex=1;
  		document.forms[0].elements['tmp_ee1_ge_hd_head_mid-line'].checked=true;
		}
	} else {
		clearGESection(practice, 'ge_hd_');
	}
}

function setGEEyesNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_eye_nt'].value=
				'Pupils equal, round and reactive to light and accommodation. '+
				'Extra-ocular movements are intact. Sclera white, conjunctiva pink '+
				'without icterus or injection.';
		} else {
			clearGESection(practice, 'ge_eyer', 'norm_exam');
			clearGESection(practice, 'ge_eyel', 'norm_exam');
			if(practice != 'sfa') {
  			document.forms[0].elements['ee1_ge_eye_hem'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_eye_exu'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_eye_av'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_eye_pap'].selectedIndex='2';
			}
  		document.forms[0].elements['ee1_ge_eyer_norm'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_eyer_scleral'].checked=true;
  		document.forms[0].elements['ee1_ge_eyer_eomi'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_eyer_perrl'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_eyel_norm'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_eyel_scleral'].checked=true;
  		document.forms[0].elements['ee1_ge_eyel_eomi'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_eyel_perrl'].selectedIndex='1';
			if(practice == 'cffm') {
  			document.forms[0].elements['ee1_ge_eyer_exo'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_eyel_exo'].selectedIndex='2';
			}
		}
	} else {
		clearGESection(practice, 'ge_eye');
	}
}

function setGEEarsNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_ear_nt'].value=
				'Auricles are normally formed without lesions. Ear canals are '+
				'clear bilaterally. Tympanic membranes are intact and '+
				'translucent without effusion. Ossicular structures grossly intact.';
		} else {
			clearGESection(practice, 'ge_ear', 'norm_exam');
  		document.forms[0].elements['ee1_ge_earr_clear'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_earr_perf'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_earr_ret'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_earr_bulge'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_earr_pus'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_earr_ceru'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_earl_clear'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_earl_perf'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_earl_ret'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_earl_bulge'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_earl_pus'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_earl_ceru'].selectedIndex='2';
		}
	} else {
		clearGESection(practice, 'ge_ear');
	}
}

function setGENoseNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_nose_nt'].value=
				'Anterior rhinoscopy shows septum to be intact and midline. '+
				'No masses, polyps or pus. Mucosa is moist and without edema.';
		} else {
			clearGESection(practice, 'ge_nose_', 'norm_exam');
  		document.forms[0].elements['ee1_ge_nose_ery'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_nose_swell'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_nose_pall'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_nose_polps'].selectedIndex='2';
			if(practice == 'cffm') {
  			document.forms[0].elements['ee1_ge_nose_sept'].selectedIndex='2';
			}
		}
	} else {
		clearGESection(practice, 'ge_nose_');
	}
}

function setGEMouthNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_mouth_nt'].value=
				'Mucosa is moist without lesion. Tongue is soft and freely mobile. '+
				'Dentition is in good repair. There is no trismus.';
		} else {
			clearGESection(practice, 'ge_mouth_', 'norm_exam');
  		document.forms[0].elements['ee1_ge_mouth_moist'].checked=true;
			if(practice == 'cffm') {
  			document.forms[0].elements['ee1_ge_mouth_les'].checked=true;
			}
		}
	} else {
		clearGESection(practice, 'ge_mouth_');
	}
}

function setGEThroatNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_thrt_nt'].value=
				'Tonsils are unremarkable. Pharyngeal walls without mucosal lesion '+
				'or cobblestoning. Soft palate is intact and elevates symmetrically. '+
				'Strong intact gag reflex.';
		} else {
			clearGESection(practice, 'ge_thrt_', 'norm_exam');
  		document.forms[0].elements['ee1_ge_thrt_ery'].checked=true;
  		document.forms[0].elements['ee1_ge_thrt_exu'].checked=true;
		}
	} else {
		clearGESection(practice, 'ge_thrt_');
	}
}

function setGENeckNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_nk_nt'].value=
				'Supple without adenopathy or masses. Trachea is midline without '+
				'stridor. Thyroid is not enlarged or nodular to palpation.';
		} else {
			clearGESection(practice, 'ge_nk_', 'norm_exam');
  		document.forms[0].elements['ee1_ge_nk_sup'].checked=true;
  		document.forms[0].elements['ee1_ge_nk_trach'].checked=true;
			if(practice == 'cffm' || practice == 'uimda' || practice == 'sfa') {
  			document.forms[0].elements['ee1_ge_nk_brit'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_nk_jvp'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_nk_lymph'].selectedIndex='2';
			}
			if(practice == 'ccc') {
  			document.forms[0].elements['ee1_ge_nk_brit'].selectedIndex='1';
  			document.forms[0].elements['ee1_ge_nk_jvp'].selectedIndex='1';
  			document.forms[0].elements['ee1_ge_nk_lymph'].selectedIndex='1';
			}
		}
	} else {
		clearGESection(practice, 'ge_nk_');
	}
}

function setGEThyroidNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_thy_nt'].value=
				'Indirect mirror exam inadequate.';
		} else {
			clearGESection(practice, 'ge_thy_', 'norm_exam');
  		document.forms[0].elements['ee1_ge_thy_norm'].checked=true;
  		document.forms[0].elements['ee1_ge_thy_nod'].selectedIndex='2';
			if(practice != 'cffm' && practice != 'sfa') {
  			document.forms[0].elements['ee1_ge_thy_brit'].selectedIndex='2';
			}
  		document.forms[0].elements['ee1_ge_thy_tnd'].selectedIndex='2';
			if(practice == 'cffm') {
  			document.forms[0].elements['ee1_ge_thy_tnd'].selectedIndex='2';
			}
		}
	} else {
		clearGESection(practice, 'ge_thy_');
	}
}

function setGELymphNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_lym_nt'].value=
				'512 Hz tuning fork shows symmetric hearing, weber is '+
				'midline, air conduction is louder than bone conduction bilaterally.';
		} else {
			clearGESection(practice, 'ge_lym_', 'norm_exam');
		}
	} else {
		clearGESection(practice, 'ge_lym_');
	}
}

function setGEBreastNormal(practice, chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_br_', 'norm_exam');
		clearGESection(practice, 'ge_brr_', 'norm_exam');
		clearGESection(practice, 'ge_brl_', 'norm_exam');
		clearGESection(practice, 'ge_nipr_', 'norm_exam');
		clearGESection(practice, 'ge_nipl_', 'norm_exam');
		if(practice == 'cffm') {
  		document.forms[0].elements['ee1_ge_br_sym'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_brr_axil'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_brr_mass'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_nipr_dis'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_nipr_ret'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_brl_axil'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_brl_mass'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_nipl_dis'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_nipl_ret'].selectedIndex='2';
		}
		if(practice == 'uimda') {
  		document.forms[0].elements['ee1_ge_brr_axil'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_brr_mass'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_brl_axil'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_brl_mass'].selectedIndex='2';
		}
	} else {
		clearGESection(practice, 'ge_br');
		clearGESection(practice, 'ge_nip');
	}
}

function setGECardioNormal(practice, chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_cr_', 'norm_exam');
  	document.forms[0].elements['ee1_ge_cr_norm'].selectedIndex='1';
  	document.forms[0].elements['ee1_ge_cr_mur'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_cr_gall'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_cr_click'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_cr_rubs'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_cr_extra'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_cr_pmi'].selectedIndex='1';
	} else {
		clearGESection(practice, 'ge_cr_');
	}
}

function setGEPulmoNormal(practice, chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_pul_', 'norm_exam');
  	document.forms[0].elements['ee1_ge_pul_clear'].selectedIndex='1';
  	document.forms[0].elements['ee1_ge_pul_rales'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_pul_whz'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_pul_ron'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_pul_dec'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_pul_crack'].selectedIndex='2';
	} else {
		clearGESection(practice, 'ge_pul_');
	}
}

function setGEGastroNormal(practice, chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_gi_', 'norm_exam');
  	document.forms[0].elements['ee1_ge_gi_soft'].selectedIndex='1';
  	document.forms[0].elements['ee1_ge_gi_tend'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_dis'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_asc'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_pnt'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_grd'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_reb'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_mass'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_scar'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_hern'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_bowel'].selectedIndex='0';
  	document.forms[0].elements['ee1_ge_gi_hepa'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_gi_spleno'].selectedIndex='2';
	} else {
		clearGESection(practice, 'ge_gi_');
	}
}

function setGENeuroNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_neu_cn_nt'].value=
				'Olfaction not tested. Visual acuity grossly intact, PERRL, EOMI, '+
				'full and symmetric facial movement, palate elevation symmetric, '+
				'gag reflex intact, tongue mobile and protrusion midline.';
		} else {
			clearGESection(practice, 'ge_neu_', 'norm_exam');
  		document.forms[0].elements['ee1_ge_neu_ao'].selectedIndex='3';
  		document.forms[0].elements['ee1_ge_neu_cn'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_neu_bicr'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_bicl'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_trir'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_tril'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_brar'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_bral'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_patr'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_patl'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_achr'].selectedIndex='0';
  		document.forms[0].elements['ee1_ge_neu_achl'].selectedIndex='0';
			if(practice == 'cffm') {
  			document.forms[0].elements['ee1_ge_neu_bicr'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_neu_bicl'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_neu_patr'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_neu_patl'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_neu_achr'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_neu_achl'].selectedIndex='2';
  			document.forms[0].elements['ee1_ge_neu_pup'].selectedIndex='6';
  			document.forms[0].elements['ee1_ge_neu_plow'].selectedIndex='6';
  			document.forms[0].elements['ee1_ge_neu_dup'].selectedIndex='6';
  			document.forms[0].elements['ee1_ge_neu_dlow'].selectedIndex='6';
			}
		}
	} else {
		clearGESection(practice, 'ge_neu_');
	}
}

function setGEMuscNormal(practice, chk)
{
	if(chk.checked == true) {
		if(practice == 'ashford') {
  		document.forms[0].elements['ee1_ge_ms_nt'].value=
				'Indirect mirror exam shows no supraglottic masses. True vocal folds '+
				'are fully mobile and without obvious lesion. '+
				'No pooling of secretions.';
		} else {
			clearGESection(practice, 'ge_ms_', 'norm_exam');
			if(practice == 'cffm') {
  			document.forms[0].elements['ee1_ge_ms_intact'].checked=true;
			}
  		document.forms[0].elements['ee1_ge_ms_mass'].selectedIndex=2;
  		document.forms[0].elements['ee1_ge_ms_tnd'].selectedIndex=2;
  		document.forms[0].elements['ee1_ge_ms_scl'].selectedIndex=2;
  		document.forms[0].elements['ee1_ge_ms_cval'].selectedIndex=2;
  		document.forms[0].elements['ee1_ge_ms_cvar'].selectedIndex=2;
  		document.forms[0].elements['ee1_ge_ms_lim'].selectedIndex=2;
  		document.forms[0].elements['ee1_ge_ms_def'].selectedIndex=2;
  		document.forms[0].elements['ee1_ge_ms_full'].selectedIndex=1;
  		document.forms[0].elements['ee1_ge_ms_gait'].selectedIndex=1;
  		document.forms[0].elements['ee1_ge_ms_norm'].checked=true;
		}
	} else {
		clearGESection(practice, 'ge_ms_');
	}
}

function setGEExtNormal(practice, chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_ext_', 'norm_exam');
  	document.forms[0].elements['ee1_ge_ext_edema'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_ext_refill'].selectedIndex='1';
  	document.forms[0].elements['ee1_ge_ext_club'].selectedIndex='2';
  	document.forms[0].elements['ee1_ge_ext_cyan'].selectedIndex='2';
		if(practice == 'cffm') {
  		document.forms[0].elements['ee1_ge_ext_pls_rad'].selectedIndex='3';
  		document.forms[0].elements['ee1_ge_ext_pls_post'].selectedIndex='3';
		}
	} else {
		clearGESection(practice, 'ge_ext_');
	}
}

function setGEFootNormal(practice,chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_db_', 'norm_exam');
  	document.forms[0].elements['ee1_ge_db_prop'].selectedIndex='1';
  	document.forms[0].elements['ee1_ge_db_vib'].selectedIndex='1';
  	document.forms[0].elements['ee1_ge_db_sens'].selectedIndex='1';
	} else {
		clearGESection(practice, 'ge_db_');
	}
}

function setGETestesNormal(practice,chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_te_', 'norm_exam');
	} else {
		clearGESection(practice, 'ge_te_');
	}
}

function setGERectalNormal(sex,practice,chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_rc_', 'norm_exam');
		if(practice == 'sfa') {
  		document.forms[0].elements['ee1_ge_rc_tone'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_rc_tone_nt'].value='and remainder of exam';
		}
		if(practice == 'cffm') {
  		document.forms[0].elements['ee1_ge_rc_tone'].selectedIndex='1';
  		document.forms[0].elements['ee1_ge_rc_ext'].selectedIndex='2';
			if(sex != 'f') {
  			document.forms[0].elements['ee1_ge_rc_pro'].selectedIndex='1';
			}
  		document.forms[0].elements['ee1_ge_rc_bog'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_rc_hard'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_rc_mass'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_rc_tend'].selectedIndex='2';
		}
	} else {
		clearGESection(practice, 'ge_rc_');
	}
}

function setGESkinNormal(practice, chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_skin_', 'norm_exam');
		if(practice == 'cffm') {
  		document.forms[0].elements['ee1_ge_skin_app'].checked=true;
  		document.forms[0].elements['ee1_ge_skin_les'].checked=true;
		} else {
  		document.forms[0].elements['ee1_ge_skin_jau'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_skin_con'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_skin_ecc'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_skin_rash'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_skin_abs'].selectedIndex='2';
  		document.forms[0].elements['ee1_ge_skin_lac'].selectedIndex='2';
		}
	} else {
		clearGESection(practice, 'ge_skin_');
	}
}

function setGEPsychNormal(practice, chk)
{
	if(chk.checked == true) {
		clearGESection(practice, 'ge_psych_', 'norm_exam');
  	document.forms[0].elements['ee1_ge_psych_judge'].checked=true;
  	document.forms[0].elements['ee1_ge_psych_judge_nt'].value='Appropriate';
  	document.forms[0].elements['ee1_ge_psych_orient'].checked=true;
  	document.forms[0].elements['ee1_ge_psych_orient_nt'].value='Appropriate';
  	document.forms[0].elements['ee1_ge_psych_memory'].checked=true;
  	document.forms[0].elements['ee1_ge_psych_memory_nt'].value='Appropriate';
  	document.forms[0].elements['ee1_ge_psych_mood'].checked=true;
  	document.forms[0].elements['ee1_ge_psych_mood_nt'].value='Appropriate';
	} else {
		clearGESection(practice, 'ge_psych_');
	}
}

function SetExamNormal(practiceId)
{
	var sex = '';
	if(length.arguments > 1) sex = arguments[1];
	chk = document.getElementById('ee1_ge_gen_norm_exam');
	chk.checked = true;
	setGEGeneralNormal(practiceId, chk)
	
	chk = document.getElementById('ee1_ge_hd_norm_exam');
	chk.checked = true;
	setGEHeadNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_eye_norm_exam');
	chk.checked = true;
	setGEEyesNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_ear_norm_exam');
	chk.checked = true;
	setGEEarsNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_nose_norm_exam');
	chk.checked = true;
	setGENoseNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_mouth_norm_exam');
	chk.checked = true;
	setGEMouthNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_thrt_norm_exam');
	chk.checked = true;
	setGEThroatNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_nk_norm_exam');
	chk.checked = true;
	setGENeckNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_thy_norm_exam');
	chk.checked = true;
	setGEThyroidNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_lym_norm_exam');
	chk.checked = false;
	setGELymphNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_br_norm_exam');
	chk.checked = false;
	setGEBreastNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_cr_norm_exam');
	chk.checked = true;
	setGECardioNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_pul_norm_exam');
	chk.checked = true;
	setGEPulmoNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_gi_norm_exam');
	chk.checked = true;
	setGEGastroNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_neu_norm_exam');
	chk.checked = true;
	setGENeuroNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_ms_norm_exam');
	chk.checked = false;
	setGEMuscNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_ext_norm_exam');
	chk.checked = false;
	setGEExtNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_db_norm_exam');
	chk.checked = false;
	setGEFootNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_te_norm_exam');
	chk.checked = false;
	setGETestesNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_rc_norm_exam');
	chk.checked = false;
	setGERectalNormal(sex, practiceId, chk)

	chk = document.getElementById('ee1_ge_skin_norm_exam');
	chk.checked = false;
	setGESkinNormal(practiceId, chk)

	chk = document.getElementById('ee1_ge_psych_norm_exam');
	chk.checked = true;
	setGEPsychNormal(practiceId, chk)
}

function toggleLineDetail(sel, dtl) {
	var a = arguments.length;
	var dtl2 = '';
	if(a > 2) dtl2 = arguments[2];
	if(sel.selectedIndex == 1) {
		document.getElementById(dtl).style.display = 'inline';
		if(dtl2 != '') {
			document.getElementById(dtl2).style.display = 'inline';
		}
	} else {
		document.getElementById(dtl).style.display = 'none';
		if(dtl2 != '') {
			document.getElementById(dtl2).style.display = 'none';
		}
		document.getElementById(dtl).selectedIndex = 0;
	}
}

function showExamSection(exam_category, button) {
	document.getElementById(exam_category).style.display = 'block';
	document.getElementById(button).style.display = 'block';
	document.forms[0].elements[exam_category].value = 'block';
	document.forms[0].elements[button].value = 'block';
}

function hideExamSection(exam_category, button) {
	document.getElementById(exam_category).style.display = 'none';
	document.getElementById(button).style.display = 'none';
	document.forms[0].elements[exam_category].value = 'none';
	document.forms[0].elements[button].value = 'none';
}

function showAllExamSections(patSex) {
	showExamSection('tmp_ge_gen_disp', 'tmp_ge_gen_button_disp');
	showExamSection('tmp_ge_head_disp', 'tmp_ge_head_button_disp');
	showExamSection('tmp_ge_eyes_disp', 'tmp_ge_eyes_button_disp');
	showExamSection('tmp_ge_ears_disp', 'tmp_ge_ears_button_disp');
	showExamSection('tmp_ge_nose_disp', 'tmp_ge_nose_button_disp');
	showExamSection('tmp_ge_mouth_disp', 'tmp_ge_mouth_button_disp');
	showExamSection('tmp_ge_throat_disp', 'tmp_ge_throat_button_disp');
	showExamSection('tmp_ge_neck_disp', 'tmp_ge_neck_button_disp');
	showExamSection('tmp_ge_thyroid_disp', 'tmp_ge_thyroid_button_disp');
	showExamSection('tmp_ge_lymph_disp', 'tmp_ge_lymph_button_disp');
	showExamSection('tmp_ge_breast_disp', 'tmp_ge_breast_button_disp');
	showExamSection('tmp_ge_cardio_disp', 'tmp_ge_cardio_button_disp');
	showExamSection('tmp_ge_pulmo_disp', 'tmp_ge_pulmo_button_disp');
	showExamSection('tmp_ge_gastro_disp', 'tmp_ge_gastro_button_disp');
	showExamSection('tmp_ge_neuro_disp', 'tmp_ge_neuro_button_disp');
	showExamSection('tmp_ge_musc_disp', 'tmp_ge_musc_button_disp');
	showExamSection('tmp_ge_ext_disp', 'tmp_ge_ext_button_disp');
	showExamSection('tmp_ge_dia_disp', 'tmp_ge_dia_button_disp');
	showExamSection('tmp_ge_test_disp', 'tmp_ge_test_button_disp');
	showExamSection('tmp_ge_rectal_disp', 'tmp_ge_rectal_button_disp');
	showExamSection('tmp_ge_skin_disp', 'tmp_ge_skin_button_disp');
	showExamSection('tmp_ge_psych_disp', 'tmp_ge_psych_button_disp');
	document.getElementById('tmp_ge_gen').checked = true;
	document.getElementById('tmp_ge_head').checked = true;
	document.getElementById('tmp_ge_eyes').checked = true;
	document.getElementById('tmp_ge_ears').checked = true;
	document.getElementById('tmp_ge_nose').checked = true;
	document.getElementById('tmp_ge_mouth').checked = true;
	document.getElementById('tmp_ge_throat').checked = true;
	document.getElementById('tmp_ge_neck').checked = true;
	document.getElementById('tmp_ge_thyroid').checked = true;
	document.getElementById('tmp_ge_lymph').checked = true;
	document.getElementById('tmp_ge_breast').checked = true;
	document.getElementById('tmp_ge_cardio').checked = true;
	document.getElementById('tmp_ge_pulmo').checked = true;
	document.getElementById('tmp_ge_gastro').checked = true;
	document.getElementById('tmp_ge_neuro').checked = true;
	document.getElementById('tmp_ge_musc').checked = true;
	document.getElementById('tmp_ge_ext').checked = true;
	document.getElementById('tmp_ge_dia').checked = true;
	document.getElementById('tmp_ge_test').checked = true;
	document.getElementById('tmp_ge_rectal').checked = true;
	document.getElementById('tmp_ge_skin').checked = true;
	document.getElementById('tmp_ge_psych').checked = true;
}

function hideAllExamSections(patSex) {
	hideExamSection('tmp_ge_gen_disp', 'tmp_ge_gen_button_disp');
	hideExamSection('tmp_ge_head_disp', 'tmp_ge_head_button_disp');
	hideExamSection('tmp_ge_eyes_disp', 'tmp_ge_eyes_button_disp');
	hideExamSection('tmp_ge_ears_disp', 'tmp_ge_ears_button_disp');
	hideExamSection('tmp_ge_nose_disp', 'tmp_ge_nose_button_disp');
	hideExamSection('tmp_ge_mouth_disp', 'tmp_ge_mouth_button_disp');
	hideExamSection('tmp_ge_throat_disp', 'tmp_ge_throat_button_disp');
	hideExamSection('tmp_ge_neck_disp', 'tmp_ge_neck_button_disp');
	hideExamSection('tmp_ge_thyroid_disp', 'tmp_ge_thyroid_button_disp');
	hideExamSection('tmp_ge_lymph_disp', 'tmp_ge_lymph_button_disp');
	hideExamSection('tmp_ge_breast_disp', 'tmp_ge_breast_button_disp');
	hideExamSection('tmp_ge_cardio_disp', 'tmp_ge_cardio_button_disp');
	hideExamSection('tmp_ge_pulmo_disp', 'tmp_ge_pulmo_button_disp');
	hideExamSection('tmp_ge_gastro_disp', 'tmp_ge_gastro_button_disp');
	hideExamSection('tmp_ge_neuro_disp', 'tmp_ge_neuro_button_disp');
	hideExamSection('tmp_ge_musc_disp', 'tmp_ge_musc_button_disp');
	hideExamSection('tmp_ge_ext_disp', 'tmp_ge_ext_button_disp');
	hideExamSection('tmp_ge_dia_disp', 'tmp_ge_dia_button_disp');
	hideExamSection('tmp_ge_test_disp', 'tmp_ge_test_button_disp');
	hideExamSection('tmp_ge_rectal_disp', 'tmp_ge_rectal_button_disp');
	hideExamSection('tmp_ge_skin_disp', 'tmp_ge_skin_button_disp');
	hideExamSection('tmp_ge_psych_disp', 'tmp_ge_psych_button_disp');
	document.getElementById('tmp_ge_gen').checked = false;
	document.getElementById('tmp_ge_head').checked = false;
	document.getElementById('tmp_ge_eyes').checked = false;
	document.getElementById('tmp_ge_ears').checked = false;
	document.getElementById('tmp_ge_nose').checked = false;
	document.getElementById('tmp_ge_mouth').checked = false;
	document.getElementById('tmp_ge_throat').checked = false;
	document.getElementById('tmp_ge_neck').checked = false;
	document.getElementById('tmp_ge_thyroid').checked = false;
	document.getElementById('tmp_ge_lymph').checked = false;
	document.getElementById('tmp_ge_breast').checked = false;
	document.getElementById('tmp_ge_cardio').checked = false;
	document.getElementById('tmp_ge_pulmo').checked = false;
	document.getElementById('tmp_ge_gastro').checked = false;
	document.getElementById('tmp_ge_neuro').checked = false;
	document.getElementById('tmp_ge_musc').checked = false;
	document.getElementById('tmp_ge_ext').checked = false;
	document.getElementById('tmp_ge_dia').checked = false;
	document.getElementById('tmp_ge_test').checked = false;
	document.getElementById('tmp_ge_rectal').checked = false;
	document.getElementById('tmp_ge_skin').checked = false;
	document.getElementById('tmp_ge_psych').checked = false;
}

function toggleCCNorm(chk) {
	if(chk.checked == true) {
		document.forms[0].elements['ee1_ge_neu_cc_fh'].selectedIndex=0;
		document.forms[0].elements['ee1_ge_neu_cc_hs'].selectedIndex=0;
		document.forms[0].elements['ee1_ge_neu_cc_ra'].selectedIndex=0;
		document.forms[0].elements['ee1_ge_neu_cc_rm'].selectedIndex=0;
		document.forms[0].elements['ee1_ge_neu_cc_pd'].selectedIndex=0;
		document.forms[0].elements['ee1_ge_neu_sns_chc'].selectedIndex=0;
	} else {
		document.forms[0].elements['ee1_ge_neu_norm_exam'].checked = false;
	}
} 

function toggleGenSubSection(stat, category, button) {
	if(document.getElementById(stat).checked == true) {
		showExamSection(category, button);
	} else {
		hideExamSection(category, button);
	}
} 

function clearGESection(client, section) {
	var a = arguments.length;
	var skip = '';
	if(a >= 3) skip = arguments[2];
	var i;
	var l = document.forms[0].elements.length;
	for (i=0; i<l; i++) {
		if(document.forms[0].elements[i].name.indexOf(section) == -1) continue;
		if(document.forms[0].elements[i].name.indexOf("tmp_"+section) != -1) continue;
		if(skip != '') {
			if(document.forms[0].elements[i].name.indexOf(skip) != -1) continue;
		}
		if(document.forms[0].elements[i].type.indexOf('select') != -1) {
			document.forms[0].elements[i].selectedIndex = 0;
		} else if(document.forms[0].elements[i].type.indexOf('check') != -1) {
			document.forms[0].elements[i].checked = false;
		} else {
			document.forms[0].elements[i].value = '';
		}
	}	
}

