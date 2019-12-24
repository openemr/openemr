<?php
/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

namespace OpenEMR\Rx\Weno;

class GenMessageId
{
    public $message_id;

    public function __construct()
    {
        $t = time();

        $ext1 = rand();
        //$ext3 = rand();
        $ext2 = rand(111111, 999999);
        $randomId = $ext1.$ext2.$t;

        $this->message_id = $randomId;
    }

    public function getMessageId()
    {
        return $this->message_id;
    }
}
