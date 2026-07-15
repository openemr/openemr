<?php

/**
 * physical_exam lines.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Forms\PhysicalExam;

/**
 * Coerce a raw request or database value (which PHPStan sees as mixed) to a
 * string. Non-scalars (arrays, objects, null) become the empty string.
 *
 * Shared by new.php and report.php so the coercion cannot drift apart.
 */
function scalar_string(mixed $value): string
{
    return is_scalar($value) ? (string) $value : '';
}

/**
 * The physical-exam checklist, in display order.
 *
 * Each entry is one exam "system": `code` is the internal system identifier
 * (or '*' for the treatment section), `label` is the translated heading shown
 * to the user, and `lines` maps each line_id to its translated description.
 * Be careful not to duplicate line ids across systems.
 *
 * @return list<array{code: string, label: string, lines: array<string, string>}>
 */
function physical_exam_lines(): array
{
    return [
        ['code' => 'GEN', 'label' => xl('GEN'), 'lines' => [
            'GENWELL'  => xl('Appearance'),
        ]],
        ['code' => 'EYE', 'label' => xl('EYE'), 'lines' => [
            'EYECP'    => xl('Conjuntiva, pupils'),
        ]],
        ['code' => 'ENT', 'label' => xl('ENT'), 'lines' => [
            'ENTTM'    => xl('TMs/EAMs/EE, ext nose'),
            'ENTNASAL' => xl('Nasal mucosa pink, septum midline'),
            'ENTORAL'  => xl('Oral mucosa pink, throat clear'),
            'ENTNECK'  => xl('Neck supple'),
            'ENTTHY'   => xl('Thyroid normal'),
        ]],
        ['code' => 'CV', 'label' => xl('CV'), 'lines' => [
            'CVRRR'    => xl('RRR without MOR'),
            'CVNTOH'   => xl('No thrills or heaves'),
            'CVCP'     => xl('Cartoid pulsations nl, pedal pulses nl'),
            'CVNPE'    => xl('No peripheral edema'),
        ]],
        ['code' => 'CHEST', 'label' => xl('CHEST'), 'lines' => [
            'CHNSD'    => xl('No skin dimpling or breast nodules'),
        ]],
        ['code' => 'RESP', 'label' => xl('RESP'), 'lines' => [
            'RECTAB'   => xl('Chest CTAB'),
            'REEFF'    => xl('Respirator effort unlabored'),
        ]],
        ['code' => 'GI', 'label' => xl('GI'), 'lines' => [
            'GIMASS'   => xl('No masses, tenderness'),
            'GIOG'     => xl('No organomegaly'),
            'GIHERN'   => xl('No hernia'),
            'GIRECT'   => xl('Anus nl, no rectal tenderness/mass'),
        ]],
        ['code' => 'GU', 'label' => xl('GU'), 'lines' => [
            'GUTEST'   => xl('No testicular tenderness, masses'),
            'GUPROS'   => xl('Prostate w/o enlrgmt, nodules, tender'),
            'GUEG'     => xl('Nl ext genitalia, vag mucosa, cervix'),
            'GUAD'     => xl('No adnexal tenderness/masses'),
        ]],
        ['code' => 'LYMPH', 'label' => xl('LYMPH'), 'lines' => [
            'LYAD'     => xl('No adenopathy (2 areas required)'),
        ]],
        ['code' => 'MUSC', 'label' => xl('MUSC'), 'lines' => [
            'MUSTR'    => xl('Strength'),
            'MUROM'    => xl('ROM'),
            'MUSTAB'   => xl('Stability'),
            'MUINSP'   => xl('Inspection'),
        ]],
        ['code' => 'NEURO', 'label' => xl('NEURO'), 'lines' => [
            'NEUCN2'   => xl('CN2-12 intact'),
            'NEUREF'   => xl('Reflexes normal'),
            'NEUSENS'  => xl('Sensory exam normal'),
        ]],
        ['code' => 'PSYCH', 'label' => xl('PSYCH'), 'lines' => [
            'PSYOR'    => xl('Orientated x 3'),
            'PSYAFF'   => xl('Affect normal'),
        ]],
        ['code' => 'SKIN', 'label' => xl('SKIN'), 'lines' => [
            'SKRASH'   => xl('No rash or abnormal lesions'),
        ]],
        ['code' => 'OTHER', 'label' => xl('OTHER'), 'lines' => [
            'OTHER'    => xl('Other'),
        ]],

        // These generate the Treatment lines:
        ['code' => '*', 'label' => '', 'lines' => [
            'TRTLABS' => xl('Labs'),
            'TRTXRAY' => xl('X-ray'),
            'TRTRET'  => xl('Return Visit'),
        ]],
    ];
}
