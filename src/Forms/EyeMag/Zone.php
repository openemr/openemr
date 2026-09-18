<?php

/**
 * Exam zones of the eye exam form, and the fields each one owns.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

/**
 * The four field-bearing zones of the exam. Each case knows which columns of the
 * joined `form_eye_*` record belong to it, which is what makes copy-forward a
 * table lookup instead of a branch per zone.
 *
 * Drawings are not listed here. They live in the documents table, not in a
 * `form_eye_*` column, and copy-forward does not carry them.
 */
enum Zone: string
{
    case EXT = 'EXT';
    case ANTSEG = 'ANTSEG';
    case RETINA = 'RETINA';
    case NEURO = 'NEURO';

    /**
     * Columns of the joined eye exam record that belong to this zone.
     *
     * @return list<string>
     */
    public function fields(): array
    {
        return match ($this) {
            self::EXT => [
                'RUL', 'LUL', 'RLL', 'LLL', 'RBROW', 'LBROW', 'RMCT', 'LMCT',
                'RADNEXA', 'LADNEXA', 'RMRD', 'LMRD', 'RLF', 'LLF',
                'RVFISSURE', 'LVFISSURE', 'RCAROTID', 'LCAROTID',
                'RTEMPART', 'LTEMPART', 'RCNV', 'LCNV', 'RCNVII', 'LCNVII',
                'ODSCHIRMER1', 'OSSCHIRMER1', 'ODSCHIRMER2', 'OSSCHIRMER2',
                'ODTBUT', 'OSTBUT', 'ODHERTEL', 'OSHERTEL', 'HERTELBASE',
                'EXT_COMMENTS',
            ],
            self::ANTSEG => [
                'OSCONJ', 'ODCONJ', 'ODCORNEA', 'OSCORNEA', 'ODAC', 'OSAC',
                'ODLENS', 'OSLENS', 'ODIRIS', 'OSIRIS',
                'ODKTHICKNESS', 'OSKTHICKNESS', 'ODGONIO', 'OSGONIO',
                'ODSCHIRMER1', 'OSSCHIRMER1', 'ODSCHIRMER2', 'OSSCHIRMER2',
                'ODTBUT', 'OSTBUT', 'ANTSEG_COMMENTS',
            ],
            self::RETINA => [
                'ODDISC', 'OSDISC', 'ODCUP', 'OSCUP', 'ODMACULA', 'OSMACULA',
                'ODVESSELS', 'OSVESSELS', 'ODVITREOUS', 'OSVITREOUS',
                'ODPERIPH', 'OSPERIPH',
                'ODCMT', 'OSCMT', 'RETINA_COMMENTS',
            ],
            self::NEURO => [
                'ACT',
                'ACT1CCDIST', 'ACT2CCDIST', 'ACT3CCDIST', 'ACT4CCDIST', 'ACT5CCDIST', 'ACT6CCDIST',
                'ACT7CCDIST', 'ACT8CCDIST', 'ACT9CCDIST', 'ACT10CCDIST', 'ACT11CCDIST',
                'ACT1SCDIST', 'ACT2SCDIST', 'ACT3SCDIST', 'ACT4SCDIST', 'ACT5SCDIST', 'ACT6SCDIST',
                'ACT7SCDIST', 'ACT8SCDIST', 'ACT9SCDIST', 'ACT10SCDIST', 'ACT11SCDIST',
                'ACT1CCNEAR', 'ACT2CCNEAR', 'ACT3CCNEAR', 'ACT4CCNEAR', 'ACT5CCNEAR', 'ACT6CCNEAR',
                'ACT7CCNEAR', 'ACT8CCNEAR', 'ACT9CCNEAR', 'ACT10CCNEAR', 'ACT11CCNEAR',
                'ACT1SCNEAR', 'ACT2SCNEAR', 'ACT3SCNEAR', 'ACT4SCNEAR', 'ACT5SCNEAR', 'ACT6SCNEAR',
                'ACT7SCNEAR', 'ACT8SCNEAR', 'ACT9SCNEAR', 'ACT10SCNEAR', 'ACT11SCNEAR',
                'ODVF1', 'ODVF2', 'ODVF3', 'ODVF4', 'OSVF1', 'OSVF2', 'OSVF3', 'OSVF4',
                'MOTILITY_RS', 'MOTILITY_RI', 'MOTILITY_RR', 'MOTILITY_RL',
                'MOTILITY_LS', 'MOTILITY_LI', 'MOTILITY_LR', 'MOTILITY_LL',
                'NEURO_COMMENTS', 'STEREOPSIS', 'ODNPA', 'OSNPA',
                'VERTFUSAMPS', 'DIVERGENCEAMPS', 'NPC',
                'DACCDIST', 'DACCNEAR', 'CACCDIST', 'CACCNEAR',
                'ODCOLOR', 'OSCOLOR', 'ODCOINS', 'OSCOINS', 'ODREDDESAT', 'OSREDDESAT',
                'ODPUPILSIZE1', 'ODPUPILSIZE2', 'ODPUPILREACTIVITY', 'ODAPD',
                'OSPUPILSIZE1', 'OSPUPILSIZE2', 'OSPUPILREACTIVITY', 'OSAPD',
                'DIMODPUPILSIZE1', 'DIMODPUPILSIZE2', 'DIMODPUPILREACTIVITY',
                'DIMOSPUPILSIZE1', 'DIMOSPUPILSIZE2', 'DIMOSPUPILREACTIVITY',
                'PUPIL_COMMENTS',
            ],
        };
    }
}
