<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\Identifier
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class Identifier extends \OpenEMR\Cqm\Qdm\BaseTypes\Any
{
    /**
     * @property string $namingSystem
     */
    public $namingSystem = '';

    /**
     * @property string $value
     */
    public $value = '';

    /**
     * @property string $qdmVersion
     */
    public $qdmVersion = '5.5';

    public $_type = 'QDM::Identifier';
}

