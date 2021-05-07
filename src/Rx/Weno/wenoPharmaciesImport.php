<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

use Pharmacy;

class wenoPharmaciesImport
{
    private $filename;
    private $state;

    public function __construct()
    {
        $this->filename = $GLOBALS['fileroot'] . "/contrib/weno/WenoPharmacyDirectory2020-12-13.csv";
        $this->state = $this->getState();
    }

    /**
     * @return string
     */
    public function importPharmacy()
    {
        $i = 0;
        if (file_exists($this->filename)) {
            $import = fopen($this->filename, "r");
            while (! feof($import)) {
                $line = fgetcsv($import);
                if ($i <= 95) {
                    ++$i;
                    continue;
                }
                if ($line[12] === $this->state) {
                    $pharmacy = new Pharmacy();
                    $pharmacy->set_id();
                    $pharmacy->set_name($line[8]);
                    $pharmacy->set_ncpdp($line[6]);
                    $pharmacy->set_npi($line[2]);
                    $pharmacy->set_address_line1($line[9]);
                    $pharmacy->set_city($line[11]);
                    $pharmacy->set_state($line[12]);
                    $pharmacy->set_zip($line[13]);
                    $pharmacy->set_fax($line[21]);
                    $pharmacy->set_phone($line[19]);
                    $pharmacy->set_transmit_method("4");
                    $pharmacy->persist();
                    ++$i;
                }
            }
            fclose($import);
            return "imported";
        } else {
            return "file not found";
        }
    }

    private function getState()
    {
        $sql = "select state from facility";
        $res = sqlQuery($sql);
        return $res['state'];
    }
}
