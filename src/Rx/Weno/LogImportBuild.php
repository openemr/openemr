<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

class LogImportBuild
{
    public $rxsynclog;

    public function __construct()
    {
        $this->insertdata = new LogDataInsert();
        $this->rxsynclog = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/logsync.csv";
    }

    public function prescriptionId()
    {
        $sql = "SELECT MAX(id) as id FROM prescriptions";
        $record = sqlQuery($sql);
        $rec = 1 + $record['id'];
        return $rec;
    }

    public function drugForm($title)
    {
        $sql = "SELECT option_id FROM list_options WHERE list_id = 'drug_form' AND title = ?";
        $drugformid = sqlQuery($sql, [$title]);
        return $drugformid['option_id'];
    }

    public function checkMessageId()
    {
        $sql = "select count(*) as count from prescriptions where indication = ?";
        $entry = sqlQuery($sql, [$this->messageid]);
        return $entry['count'];
    }

    public function buildInsertArray()
    {
        $l = 0;
        if (file_exists($this->rxsynclog)) {
            $records = fopen($this->rxsynclog, "r");

            while (!feof($records)) {
                $line = fgetcsv($records);

                if ($l <= 2) {
                    $l++;
                    continue;
                }
                if (!isset($line[1])) {
                    continue;
                }
                if (isset($line[4])) {
                    $this->messageid = isset($line[4]) ? $line[4] : null;
                    $is_saved = $this->checkMessageId();
                    if ($is_saved > 0) {
                        continue;
                    }
                }
                if (!empty($line)) {
                    $pr = $line[2] ?? null;
                    $provider = explode(":", $pr);
                    $windate = $line[16] ?? null;
                    $idate = substr(trim($windate), 0, -5);
                    $idate = explode(" ", $idate);
                    $idate = explode("/", $idate[0]);
                    $year = $idate[2] ?? null;
                    $month = $idate[0] ?? null;
                    $day = $idate[1] ?? null;
                    $idate = $year . '-' . $month . '-' . $day;
                    $ida = filter_var($idate, FILTER_SANITIZE_NUMBER_INT);
                    $p = $line[1] ?? null;
                    $pid = filter_var($p, FILTER_SANITIZE_NUMBER_INT);
                    $r = $line[22] ?? null;
                    $refills = filter_var($r, FILTER_SANITIZE_NUMBER_INT);

                    $insertdata = [];
                    $rec = $this->prescriptionId();
                    $insertdata['id'] = $rec;
                    $active = 1;
                    $insertdata['active'] = $active;
                    $insertdata['date_added'] =  $ida;
                    $insertdata['patient_id'] = $pid;
                    $drug = isset($line[11]) ? str_replace('"', '', $line[11]) : null;
                    $insertdata['drug'] = $drug;
                    $type = $this->drugForm($line[19]);
                    $insertdata['form'] = $type ?? null;
                    $insertdata['quantity'] = $line[18] ?? null;
                    $insertdata['refills'] = $refills;
                    $sub = ($line[14] = 'Allowed' ? 1 : 0);
                    $insertdata['substitute'] = $sub ?? null;
                    $insertdata['note'] = $line[21] ?? null;
                    $insertdata['rxnorm_drugcode'] = $line[12] ?? null;
                    $insertdata['provider_id'] = $provider[0];
                    $insertdata['prescriptionguid'] = $line[4] ?? null;
                    $insertdata['txDate'] = $ida;
                    $loginsert = new LogDataInsert();
                    $loginsert->insertPrescriptions($insertdata);

                    ++$l;
                }
            }
            fclose($records);
        } else {
            echo "File is missing!";
        }
    }
}
