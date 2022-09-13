<?php

/**
 * RXList.class.php
 *
 * Revision 1.6  2019/02/18  avanss llc
 * replaced rxlist API with rxnav.nlm.nih.gov
 *
 * Revision 1.5  2016/02/016   sherwin gaddis
 * fix broken url
 *
 * Revision 1.4  2008/05/09 20:10:28  cfapress
 * Changes to handle to HTML returned by rxlist.com
 *
 * Revision 1.3  2007/06/19 02:34:45  sunsetsystems
 * drug lookup fix from Sam Rajan
 *
 * Revision 1.2  2005/12/27 00:45:19  sunsetsystems
 * fix broken url
 *
 * Revision 1.1.1.3  2005/06/23 05:25:49  drbowen
 * Initial import.
 *
 * Revision 1.1.1.2  2005/03/28 00:44:54  drbowen
 * Initial import.
 *
 * Revision 1.1.1.1  2005/03/09 19:59:43  wpennington
 * First import of OpenEMR
 *
 * Revision 1.2  2002/08/06 13:49:08  rufustfirefly
 * updated rxlist class for changes in RxList.com
 *
 * Revision 1.1  2002/07/08 14:42:25  rufustfirefly
 * RxList.com prescription "module" for formulary
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    rufustfirefly
 * @author    wpennington
 * @author    drbowen
 * @author    sunsetsystems
 * @author    cfapress
 * @author    sherwin gaddis
 * @author    avanss llc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2002 rufustfirefly
 * @copyright Copyright (c) 2005 wpennington
 * @copyright Copyright (c) 2005 drbowen
 * @copyright Copyright (c) 2005-2007 sunsetsystems
 * @copyright Copyright (c) 2008 cfapress
 * @copyright Copyright (c) 2016 sherwin gaddis
 * @copyright Copyright (c) 2019 avanss llc
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx;

use OpenEMR\Common\Http\oeHttp;

class RxList
{
    public function getPage($query)
    {
        $url = "https://rxnav.nlm.nih.gov/REST/Prescribe/drugs";
        $response = oeHttp::get($url, ['name' => $query]);
        $buffer = $response->body();
        return $buffer ?: false;
    }

    public function getList($query)
    {
        $page = $this->getPage($query);
        $tokens = $this->parseToTokens($page);
        $hash = $this->tokensToHash($tokens);
        if (!empty($hash)) {
            foreach ($hash as $index => $data) {
                unset($my_data);
                foreach ($data as $k => $v) {
                    $my_data[$k] = $v;
                }

                $rxcui = '';

                if (trim($my_data['rxcui']) !== '') {
                    $rxcui = " (RxCUI:" . trim($my_data['rxcui'] . ")");
                }

                $synonym = '';
                if (trim($my_data['synonym']) !== '') {
                    $synonym = " | (" . trim($my_data['synonym']) . ")";
                }

                $list[trim($my_data['name'] . $rxcui) . $synonym] =
                trim($my_data['name']);
            }
        }
        return $list;
    }

    /* break the web page into a collection of TAGS
     * such as <input ..> or <img ... >
     */
    public function parseToTokens($page)
    {
        $pos = 0;
        $token = 0;
        unset($tokens);
        $in_token = false;
        while ($pos < strlen($page)) {
            switch (substr($page, $pos, 1)) {
                case "<":
                    if ($in_token) {
                        $token++;
                        $in_token = false;
                    }

                    $tokens[$token] .= substr($page, $pos, 1);
                    $in_token = true;
                    break;

                case ">":
                    $tokens[$token] .= substr($page, $pos, 1);
                    $in_token = false;
                    $token++;
                    break;

                default:
                    $tokens[$token] .= substr($page, $pos, 1);
                    $in_token = false;
                    break;
            }
            $pos++;
        }
        return $tokens;
    }

    public function tokensToHash($tokens)
    {
        $record = false;
        $current = 0;
        unset($hash);
        $hash = [];
        unset($all);
        for ($pos = 0, $posMax = count($tokens); $pos < $posMax; $pos++) {
            //ensure compliant wth php 7.4 (no str_contains() function in 7.4)
            if (!function_exists('str_contains')) {
                if ((strpos($tokens[$pos], "<name>") !== false) && $pos !== 3) {
                    // found a brand line 'token'
                    $type = "name";
                    $record = $pos;
                    $ending = "</name>";
                }
            } else { // function_exists('str_contains')
                if ((str_contains($tokens[$pos], "<name>")) && $pos !== 3) {
                    // found a brand line 'token'
                    $type = "name";
                    $record = $pos;
                    $ending = "</name>";
                }
            }

            //ensure compliant wth php 7.4 (no str_contains() function in 7.4)
            if (!function_exists('str_contains')) {
                if (strpos($tokens[$pos], "<synonym>") !== false) {
                    // found a generic line 'token'
                    $type = "synonym";
                    //print "generic_name record start at $pos<BR>\n";
                    $ending = "</synonym>";
                    $record = $pos;
                }
            } else { // function_exists('str_contains')
                if (str_contains($tokens[$pos], "<synonym>")) {
                    // found a generic line 'token'
                    $type = "synonym";
                    //print "generic_name record start at $pos<BR>\n";
                    $ending = "</synonym>";
                    $record = $pos;
                }
            }

            //ensure compliant wth php 7.4 (no str_contains() function in 7.4)
            if (!function_exists('str_contains')) {
                if (strpos($tokens[$pos], "<rxcui>") !== false) {
                    // found a drug-class 'token'
                    $type = "rxcui";
                    $ending = "</rxcui>";
                    $record = $pos;
                }
            } else { // function_exists('str_contains')
                if (str_contains($tokens[$pos], "<rxcui>")) {
                    // found a drug-class 'token'
                    $type = "rxcui";
                    $ending = "</rxcui>";
                    $record = $pos;
                }
            }

            if (isset($hash["synonym"])) {
                // Reset record
                $record = false;
                // Add hash to all
                $all[] = $hash;
                // Unset hash and type
                unset($hash);
                $type = "";
                $ending = "";
            }

            if ($pos === ($record + 1) and ($ending != "")) {
                $my_pos = stripos($tokens[$pos], "<");
                $hash[$type] = substr($tokens[$pos], 0, $my_pos);
                $hash[$type] = str_replace("&amp;", "&", $hash[$type]);
                //print "hash[$type] = ".htmlentities($hash[$type])."<BR>\n";
                $type = "";
                $ending = "";
            }
        }
        return $all;
    }
}
