<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\MedicationActive
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class MedicationActive extends QDMBaseType
{
    /**
     * @property BaseTypes\DateTime $relevantDatetime
     */
    public $relevantDatetime = null;

    /**
     * @property BaseTypes\Interval $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property BaseTypes\Quantity $dosage
     */
    public $dosage = null;

    /**
     * @property BaseTypes\Code $frequency
     */
    public $frequency = null;

    /**
     * @property BaseTypes\Code $route
     */
    public $route = null;

    /**
     * @property BaseTypes\Any $recorder
     */
    public $recorder = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Medication, Active';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.44';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'medication';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = 'active';

    public $_type = 'QDM::MedicationActive';
}

