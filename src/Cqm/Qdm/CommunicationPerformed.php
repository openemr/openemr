<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\CommunicationPerformed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class CommunicationPerformed extends QDMBaseType
{
    /**
     * @property BaseTypes\DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property BaseTypes\Code $category
     */
    public $category = null;

    /**
     * @property BaseTypes\Code $medium
     */
    public $medium = null;

    /**
     * @property BaseTypes\Any $sender
     */
    public $sender = null;

    /**
     * @property BaseTypes\Any $recipient
     */
    public $recipient = null;

    /**
     * @property array $relatedTo
     */
    public $relatedTo = [
        
    ];

    /**
     * @property BaseTypes\DateTime $sentDatetime
     */
    public $sentDatetime = null;

    /**
     * @property BaseTypes\DateTime $receivedDatetime
     */
    public $receivedDatetime = null;

    /**
     * @property BaseTypes\Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Communication, Performed';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.132';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'communication';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = 'performed';

    public $_type = 'QDM::CommunicationPerformed';
}

