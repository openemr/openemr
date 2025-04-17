<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\NegativeEncounterOrder
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class NegativeEncounterOrder extends EncounterOrder
{
    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Encounter, Not Ordered';

    /**
     * @property string $qdmVersion
     */
    public $qdmVersion = '5.5';

    public $_type = 'QDM::NegativeEncounterOrder';
}

