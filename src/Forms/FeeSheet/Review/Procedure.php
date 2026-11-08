<?php

/**
 * Procedure - extension of CodeInfo for procedure billing entries
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR <https://www.open-emr.org/wiki/index.php/OEMR_wiki_page>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
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
        string $code,
        string $code_type,
        string $description,
        public string $fee,
        public string $justify,
        public string $modifiers,
        public string $units,
        public ?int $mod_size,
        public string $ndc_info,
        bool $selected = true
    ) {
        parent::__construct($code, $code_type, $description, $selected);
    }

    /**
     * @param array<mixed> $params
     */
    public function addProcParameters(array &$params): void
    {
        array_push($params, $this->modifiers, $this->units, $this->fee, $this->ndc_info, $this->justify);
    }
}
