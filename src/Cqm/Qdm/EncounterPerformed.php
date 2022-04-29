<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\EncounterPerformed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class EncounterPerformed extends QDMBaseType
{
    /**
     * @property BaseTypes\DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property BaseTypes\Code $admissionSource
     */
    public $admissionSource = null;

    /**
     * @property BaseTypes\Interval $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property BaseTypes\Code $dischargeDisposition
     */
    public $dischargeDisposition = null;

    /**
     * @property array $facilityLocations
     */
    public $facilityLocations = [
        
    ];

    /**
     * @property array $diagnoses
     */
    public $diagnoses = [
        
    ];

    /**
     * @property BaseTypes\Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property BaseTypes\Quantity $lengthOfStay
     */
    public $lengthOfStay = null;

    /**
     * @property BaseTypes\Code $priority
     */
    public $priority = null;

    /**
     * @property BaseTypes\Any $participant
     */
    public $participant = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Encounter, Performed';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.5';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'encounter';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = 'performed';

    public $_type = 'QDM::EncounterPerformed';
}

