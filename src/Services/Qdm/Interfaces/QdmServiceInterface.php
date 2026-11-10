<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qdm\Interfaces;

use OpenEMR\Services\Qdm\QdmRecord;

interface QdmServiceInterface
{
    public function getSqlStatement();

    public function makeQdmModel(QdmRecord $recordObj);

    public function executeQuery();
}
