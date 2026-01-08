<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\QDMBaseType
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.6
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class QDMBaseType extends \OpenEMR\Cqm\Qdm\BaseTypes\DataElement
{
    /**
     * @property string $id
     */
    public $id = '';

    /**
     * @property BaseTypes\Code $code
     */
    public $code = null;

    /**
     * @property string $patientId
     */
    public $patientId = '';

    /**
     * @property string $qdmVersion
     */
    public $qdmVersion = '5.6';

    public $_type = 'QDM::QDMBaseType';
}
