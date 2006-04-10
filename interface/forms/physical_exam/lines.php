<?php

// The hash is overkill, but easy to traverse for presenting the form.
// The first level key is the displayed category name, and the second
// level is the line_id for the database.  Be careful not to duplicate
// these IDs!

$pelines = array(
	'GEN' => array(
		'GENWELL'  => 'Appearance'),
	'EYE' => array(
		'EYECP'    => 'Conjuntiva, pupils'),
	'ENT' => array(
		'ENTTM'    => 'TMs/EAMs/EE, ext nose',
		'ENTNASAL' => 'Nasal mucosa pink, septum midline',
		'ENTORAL'  => 'Oral mucosa pink, throat clear',
		'ENTNECK'  => 'Neck supple',
		'ENTTHY'   => 'Thyroid normal'),
	'CV' => array(
		'CVRRR'    => 'RRR without MOR',
		'CVNTOH'   => 'No thrills or heaves',
		'CVCP'     => 'Cartoid pulsations nl, pedal pulses nl',
		'CVNPE'    => 'No peripheral edema'),
	'CHEST' => array(
		'CHNSD'    => 'No skin dimpling or breast nodules'),
	'RESP' => array(
		'RECTAB'   => 'Chest CTAB',
		'REEFF'    => 'Respirator effort unlabored'),
	'GI' => array(
		'GIMASS'   => 'No masses, tenderness',
		'GIOG'     => 'No ogrganomegoly',
		'GIHERN'   => 'No hernia',
		'GIRECT'   => 'Anus nl, no rectal tenderness/mass'),
	'GU' => array(
		'GUTEST'   => 'No testicular tenderness, masses',
		'GUPROS'   => 'Prostate w/o enlrgmt, nodules, tender',
		'GUEG'     => 'Nl ext genitalia, vag mucosa, cervix',
		'GUAD'     => 'No adnexal tenderness/masses'),
	'LYMPH' => array(
		'LYAD'     => 'No adenopathy (2 areas required)'),
	'MUSC' => array(
		'MUSTR'    => 'Strength',
		'MUROM'    => 'ROM',
		'MUSTAB'   => 'Stability',
		'MUINSP'   => 'Inspection'),
	'NEURO' => array(
		'NEUCN2'   => 'CN2-12 intact',
		'NEUREF'   => 'Reflexes normal',
		'NEUSENS'  => 'Sensory exam normal'),
	'PSYCH' => array(
		'PSYOR'    => 'Orientated x 3',
		'PSYAFF'   => 'Affect normal'),
	'SKIN' => array(
		'SKRASH'   => 'No rash or abnormal lesions'),
	'OTHER' => array(
		'OTHER'    => 'Other'),

	// These generate the Treatment lines:
	'*' => array(
		'TRTLABS' => 'Labs',
		'TRTXRAY' => 'X-ray',
		'TRTRET'  => 'Return Visit')
);
?>
