<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\DeviceApplied
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class DeviceApplied extends QDMBaseType
{
    /**
     * @property BaseTypes\DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property BaseTypes\DateTime $relevantDatetime
     */
    public $relevantDatetime = null;

    /**
     * @property BaseTypes\Interval $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property BaseTypes\Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property BaseTypes\Code $reason
     */
    public $reason = null;

    /**
     * @property BaseTypes\Code $anatomicalLocationSite
     */
    public $anatomicalLocationSite = null;

    /**
     * @property BaseTypes\Any $performer
     */
    public $performer = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Device, Applied';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.13';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'device';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = 'applied';

    public $_type = 'QDM::DeviceApplied';
}

