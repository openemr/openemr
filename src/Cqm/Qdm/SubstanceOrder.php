<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\SubstanceOrder
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class SubstanceOrder extends QDMBaseType
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
     * @property BaseTypes\Code $reason
     */
    public $reason = null;

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
     * @property BaseTypes\Integer $refills
     */
    public $refills = null;

    /**
     * @property BaseTypes\Code $route
     */
    public $route = null;

    /**
     * @property BaseTypes\Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property BaseTypes\Any $requester
     */
    public $requester = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Substance, Order';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.77';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'substance';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = 'order';

    public $_type = 'QDM::SubstanceOrder';
}

