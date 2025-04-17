<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\CarePartner
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class CarePartner extends Entity
{
    /**
     * @property BaseTypes\Code $relationship
     */
    public $relationship = null;

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.134';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '2.16.840.1.113883.10.20.24.3.160';

    public $_type = 'QDM::CarePartner';
}

