<?php

/**
 * Procedure - extension of CodeInfo for procedure billing entries
 *
 * @package   OpenEMR
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

/**
 * This is an extension of CodeInfo which supports the additional information
 * held in a procedure billing entry
 */
class Procedure extends CodeInfo
{
    public function __construct(
        $code,
        $code_type,
        $description,
        public $fee,
        public $justify,
        public $modifiers,
        public $units,
        public $mod_size,
        $selected = true
    ) {
        parent::__construct($code, $code_type, $description, $selected);
    }

    public function addProcParameters(&$params)
    {
        array_push($params, $this->modifiers, $this->units, $this->fee, $this->justify);
    }
}
