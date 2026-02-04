<?php

/**
 * EncounterInfo - pairs an encounter's ID with the date of the encounter
 *
 * @package   OpenEMR
 * @link      https://open-emr.org/
 * @link      https://opencoreemr.com/
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @copyright Copyright (c) 2026 OpenCoreEmr Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Forms\FeeSheet\Review;

class EncounterInfo
{
    public function __construct(
        public $id,
        public $date
    ) {
    }

    public function getID()
    {
        return $this->id;
    }
}
