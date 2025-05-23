<?php

/**
 * physical_exam lines.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// The hash is overkill, but easy to traverse for presenting the form.
// The first level key is the displayed category name, and the second
// level is the line_id for the database.  Be careful not to duplicate
// these IDs!
global $pelines;
$pelines = array(
    'GEN' => array(
        'GENWELL'  => xl('Appearance')),
    'EYE' => array(
        'EYECP'    => xl('Conjuntiva, pupils')),
    'ENT' => array(
        'ENTTM'    => xl('TMs/EAMs/EE, ext nose'),
        'ENTNASAL' => xl('Nasal mucosa pink, septum midline'),
        'ENTORAL'  => xl('Oral mucosa pink, throat clear'),
        'ENTNECK'  => xl('Neck supple'),
        'ENTTHY'   => xl('Thyroid normal')),
    'CV' => array(
        'CVRRR'    => xl('RRR without MOR'),
        'CVNTOH'   => xl('No thrills or heaves'),
        'CVCP'     => xl('Cartoid pulsations nl, pedal pulses nl'),
        'CVNPE'    => xl('No peripheral edema')),
    'CHEST' => array(
        'CHNSD'    => xl('No skin dimpling or breast nodules')),
    'RESP' => array(
        'RECTAB'   => xl('Chest CTAB'),
        'REEFF'    => xl('Respirator effort unlabored')),
    'GI' => array(
        'GIMASS'   => xl('No masses, tenderness'),
        'GIOG'     => xl('No organomegaly'),
        'GIHERN'   => xl('No hernia'),
        'GIRECT'   => xl('Anus nl, no rectal tenderness/mass')),
    'GU' => array(
        'GUTEST'   => xl('No testicular tenderness, masses'),
        'GUPROS'   => xl('Prostate w/o enlrgmt, nodules, tender'),
        'GUEG'     => xl('Nl ext genitalia, vag mucosa, cervix'),
        'GUAD'     => xl('No adnexal tenderness/masses')),
    'LYMPH' => array(
        'LYAD'     => xl('No adenopathy (2 areas required)')),
    'MUSC' => array(
        'MUSTR'    => xl('Strength'),
        'MUROM'    => xl('ROM'),
        'MUSTAB'   => xl('Stability'),
        'MUINSP'   => xl('Inspection')),
    'NEURO' => array(
        'NEUCN2'   => xl('CN2-12 intact'),
        'NEUREF'   => xl('Reflexes normal'),
        'NEUSENS'  => xl('Sensory exam normal')),
    'PSYCH' => array(
        'PSYOR'    => xl('Orientated x 3'),
        'PSYAFF'   => xl('Affect normal')),
    'SKIN' => array(
        'SKRASH'   => xl('No rash or abnormal lesions')),
    'OTHER' => array(
        'OTHER'    => xl('Other')),

    // These generate the Treatment lines:
    '*' => array(
        'TRTLABS' => xl('Labs'),
        'TRTXRAY' => xl('X-ray'),
        'TRTRET'  => xl('Return Visit'))
);
