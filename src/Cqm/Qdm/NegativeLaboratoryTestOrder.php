<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\NegativeLaboratoryTestOrder
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.6
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class NegativeLaboratoryTestOrder extends LaboratoryTestOrder
{
    /**
     * @property string $qdmTitle
     */
    public $qdmTitle = 'Laboratory Test, Not Ordered';

    /**
     * @property string $qdmVersion
     */
    public $qdmVersion = '5.6';

    public $_type = 'QDM::NegativeLaboratoryTestOrder';
}

