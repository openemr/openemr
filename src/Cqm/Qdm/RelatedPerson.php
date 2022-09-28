<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\RelatedPerson
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class RelatedPerson extends QDMBaseType
{
    /**
     * @property BaseTypes\Identifier $identifier
     */
    public $identifier = null;

    /**
     * @property string $linkedPatientId
     */
    public $linkedPatientId = '';

    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Related Person';

    /**
     * @property string $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.141';

    /**
     * @property string $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property string $qdmCategory
     */
    public $qdmCategory = 'related_person';

    /**
     * @property string $qdmStatus
     */
    public $qdmStatus = '';

    public $_type = 'QDM::RelatedPerson';
}

