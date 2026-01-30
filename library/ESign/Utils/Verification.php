<?php

/**
 * Implementation of VerificationIF for hashing a signable object
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/VerificationIF.php';

class Utils_Verification implements VerificationIF
{
    public function hash($data, $algo = 'sha3-512')
    {
        $string = "";
        $string = is_array($data) ? $this->stringifyArray($data) : $data;

        if ($algo == 'sha1') {
            // support backward compatibility of prior hashes in sha1
            $hash = sha1((string) $string);
        } else {
            $hash = hash('sha3-512', (string) $string);
        }
        return $hash;
    }

    protected function stringifyArray(array $arr)
    {
        $string = "";
        foreach ($arr as $part) {
            if (is_array($part)) {
                $string .= $this->stringifyArray($part);
            } else {
                $string .= $part;
            }
        }

        return $string;
    }

    public function verify($data, $hash)
    {
        if (strlen((string) $hash) < 50) {
            // support backward compatibility of prior hashes in sha1
            $currentHash = $this->hash($data, 'sha1');
        } else {
            $currentHash = $this->hash($data);
        }
        if (hash_equals($currentHash, $hash)) {
            return true;
        }

        return false;
    }
}
