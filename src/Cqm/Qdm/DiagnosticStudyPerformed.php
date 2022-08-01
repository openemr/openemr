<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\DiagnosticStudyPerformed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class DiagnosticStudyPerformed extends QDMBaseType
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
     * @property BaseTypes\Code $reason
     */
    public $reason = null;

    /**
     * @property BaseTypes\Any $result
     */
    public $result = null;

    /**
     * @property BaseTypes\DateTime $resultDatetime
     */
    public $resultDatetime = null;

    /**
     * @property BaseTypes\Code $status
     */
    public $status = null;

    /**
     * @property BaseTypes\Code $method
     */
    public $method = null;

    /**
     * @property BaseTypes\Code $facilityLocation
     */
    public $facilityLocation = null;

    /**
     * @property BaseTypes\Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property array $components
     */
    public $components = [
        
    ];

    /**
     * @property BaseTypes\Any $performer
     */
    public $performer = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Diagnostic Study, Performed';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.23';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'diagnostic_study';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = 'performed';

    public $_type = 'QDM::DiagnosticStudyPerformed';
}

