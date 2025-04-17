<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\CareGoal
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class CareGoal extends QDMBaseType
{
    /**
     * @property BaseTypes\Date $statusDate
     */
    public $statusDate = null;

    /**
     * @property BaseTypes\Interval $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property array $relatedTo
     */
    public $relatedTo = [
        
    ];

    /**
     * @property BaseTypes\Any $targetOutcome
     */
    public $targetOutcome = null;

    /**
     * @property BaseTypes\Any $performer
     */
    public $performer = null;

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Care Goal';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.7';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'care_goal';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = '';

    public $_type = 'QDM::CareGoal';
}

