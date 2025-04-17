<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\QDMBaseType
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class QDMBaseType extends \OpenEMR\Cqm\Qdm\BaseTypes\DataElement
{
    public $denormalize_as_datetime = null;
    public $dataElementAttributes = [];
    public $codeListId = null;
    public $_id = null;

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
    public $qdmVersion = '5.5';

    public $_type = 'QDM::QDMBaseType';
}

