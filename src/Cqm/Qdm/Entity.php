<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\Entity
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class Entity extends \OpenEMR\Cqm\Qdm\BaseTypes\Any
{
    /**
     * @property string $id
     */
    public $id = '';

    /**
     * @property BaseTypes\Identifier $identifier
     */
    public $identifier = null;

    /**
     * @property string $qdmVersion
     */
    public $qdmVersion = '5.5';

    public $_type = 'QDM::Entity';
}

