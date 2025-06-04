<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\EncounterOrder
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class EncounterOrder extends QDMBaseType
{
    /**
     * @property BaseTypes\DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property BaseTypes\Code $reason
     */
    public $reason = null;

    /**
     * @property BaseTypes\Code $facilityLocation
     */
    public $facilityLocation = null;

    /**
     * @property BaseTypes\Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property BaseTypes\Any $requester
     */
    public $requester = null;

    /**
     * @property BaseTypes\Code $priority
     */
    public $priority = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Encounter, Order';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.27';

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
    public $qdmStatus = 'order';

    public $_type = 'QDM::EncounterOrder';
}

