<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\PatientCharacteristicPayer
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class PatientCharacteristicPayer extends QDMBaseType
{
    /**
     * @property BaseTypes\Interval $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Patient Characteristic Payer';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.58';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'patient_characteristic';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = 'payer';

    public $_type = 'QDM::PatientCharacteristicPayer';
}

