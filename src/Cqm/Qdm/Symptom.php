<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\Symptom
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class Symptom extends QDMBaseType
{
    /**
     * @property BaseTypes\Interval $prevalencePeriod
     */
    public $prevalencePeriod = null;

    /**
     * @property BaseTypes\Code $severity
     */
    public $severity = null;

    /**
     * @property BaseTypes\Any $recorder
     */
    public $recorder = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Symptom';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.116';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '2.16.840.1.113883.10.20.24.3.136';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'symptom';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = '';

    public $_type = 'QDM::Symptom';
}

