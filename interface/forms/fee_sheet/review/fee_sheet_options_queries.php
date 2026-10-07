<?php

/**
 * Utility functions for retrieving fee sheet options.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Database\QueryUtils;

/**
 * Object representing the code,description and price for a fee sheet option
 * (typically a procedure code).
 */
class fee_sheet_option
{
    public function __construct(public $code, public $code_type, public $description, public $price, public $category)
    {
        if ($this->price == null) {
            $this->price = xl("Not Specified");
        }
    }
    public $fee_display;
}
/**
 * get a list of fee sheet options
 *
 * @param string $pricelevel which pricing level to retrieve
 * @return fee_sheet_option[] containing the options
 */
function load_fee_sheet_options(string $pricelevel): array
{
    // An entry's codes look like "TYPE|code[:modifiers]|[selector]", several joined by "~";
    // the option offers its first code. The pieces are SQL expressions, not bound values.
    $firstCode = "SUBSTRING_INDEX(fso.fs_codes, '~', 1)";
    $codeType = "SUBSTRING_INDEX($firstCode, '|', 1)";
    $code = "SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX($firstCode, '|', 2), '|', -1), ':', 1)";

    $sql = "SELECT codes.code, code_types.ct_key AS code_type, codes.code_text, pr_price, fso.fs_category
        FROM fee_sheet_options AS fso, code_types, codes
        LEFT JOIN prices ON (codes.id = prices.pr_id AND prices.pr_level = ?)
        WHERE codes.code = $code
        AND code_types.ct_key = $codeType
        AND codes.code_type = code_types.ct_id
        ORDER BY fso.fs_category, fso.fs_option";

    $retval = [];
    foreach (QueryUtils::fetchRecords($sql, [$pricelevel]) as $res) {
        $retval[] = new fee_sheet_option($res['code'], $res['code_type'], $res['code_text'], $res['pr_price'], $res['fs_category']);
    }

    return $retval;
}
