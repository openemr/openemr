<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\DiagnosisComponent
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class DiagnosisComponent extends \OpenEMR\Cqm\Qdm\BaseTypes\Any
{
    /**
     * @property BaseTypes\Code $code
     */
    public $code = null;

    /**
     * @property BaseTypes\Code $presentOnAdmissionIndicator
     */
    public $presentOnAdmissionIndicator = null;

    /**
     * @property BaseTypes\Integer $rank
     */
    public $rank = null;

    /**
     * @property string $qdmVersion
     */
    public $qdmVersion = '5.5';

    public $_type = 'QDM::DiagnosisComponent';
}

