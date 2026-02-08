<?php

/**
 * Utility functions for retrieving fee sheet options.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Object representing the code,description and price for a fee sheet option
 * (typically a procedure code).
 */
class fee_sheet_option
{
    function __construct(public $code, public $code_type, public $description, public $price, public $category)
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
 * @return an array containing the options
 */
function load_fee_sheet_options($pricelevel)
{
    $clFSO_code_type = 'substring_index(fso.fs_codes,"|",1)';
    $clFSO_code = 'replace(substring_index(fso.fs_codes,"|",-2),"|","")';

    $sql = "SELECT codes.code,code_types.ct_key as code_type,codes.code_text,pr_price,fso.fs_category
        FROM fee_sheet_options as fso, code_types, codes
        LEFT JOIN prices ON (codes.id=prices.pr_id AND prices.pr_level=?)
        WHERE codes.code=?
        AND code_types.ct_key=?
        AND codes.code_type=code_types.ct_id
        ORDER BY fso.fs_category,fso.fs_option";

    $results = sqlStatement($sql, [$pricelevel, $clFSO_code, $clFSO_code]);

    $retval = [];
    while ($res = sqlFetchArray($results)) {
        $fso = new fee_sheet_option($res['code'], $res['code_type'], $res['code_text'], $res['pr_price'], $res['fs_category']);
        $retval[] = $fso;
    }

    return $retval;
}
