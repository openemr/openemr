<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\MedicationOrder
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class MedicationOrder extends QDMBaseType
{
    /**
     * @property BaseTypes\DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property BaseTypes\Interval $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property BaseTypes\Integer $refills
     */
    public $refills = null;

    /**
     * @property BaseTypes\Quantity $dosage
     */
    public $dosage = null;

    /**
     * @property BaseTypes\Quantity $supply
     */
    public $supply = null;

    /**
     * @property BaseTypes\Code $frequency
     */
    public $frequency = null;

    /**
     * @property BaseTypes\Integer $daysSupplied
     */
    public $daysSupplied = null;

    /**
     * @property BaseTypes\Code $route
     */
    public $route = null;

    /**
     * @property BaseTypes\Code $setting
     */
    public $setting = null;

    /**
     * @property BaseTypes\Code $reason
     */
    public $reason = null;

    /**
     * @property BaseTypes\Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property BaseTypes\Any $prescriber
     */
    public $prescriber = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Medication, Order';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.51';

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
    public $qdmStatus = 'order';

    public $_type = 'QDM::MedicationOrder';
}

