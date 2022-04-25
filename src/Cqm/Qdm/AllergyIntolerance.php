<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\AllergyIntolerance
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class AllergyIntolerance extends QDMBaseType
{
    /**
     * @property BaseTypes\DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property BaseTypes\Interval $prevalencePeriod
     */
    public $prevalencePeriod = null;

    /**
     * @property BaseTypes\Code $type
     */
    public $type = null;

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
    public $qdmTitle = 'Allergy/Intolerance';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.119';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'allergy';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = 'intolerance';

    public $_type = 'QDM::AllergyIntolerance';
}

