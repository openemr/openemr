<?php

/**
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use DateTime;
use DateTimeZone;

class LogImportBuild
{
    public $rxsynclog;
    private $insertdata;
    /**
     * @var mixed|null
     */
    private mixed $messageid;

    public function __construct()
    {
        $this->insertdata = new LogDataInsert();
        $this->rxsynclog = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/logsync.csv";
    }

    public function getUserIdByWenoId($external_provider_id)
    {
        // This is important so a user is set in prescription table.
        $provider = sqlQuery("SELECT id FROM users WHERE weno_prov_id = ? ", array($external_provider_id));
        if ($provider) {
            return $provider['id'];
        } else {
            // logged-in user is auth weno user so let's ensure a user is set.
            return "REQED:{users}" . xlt("Weno User Id missing. Select Admin then Users and edit the user to add Weno User Id");
        }
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
        return $entry['count'] ?? 0;
    }

    function convertToUTC($dateString)
    {
        $date = new DateTime($dateString, new DateTimeZone('UTC'));
        $tz = new DateTimeZone(date_default_timezone_get());
        $date->setTimezone($tz);

        return $date->format('Y-m-d H:i:s');
    }

    public function buildPrescriptionInserts(): bool|string
    {
        $wenoLog = new WenoLogService();
        $l = 0;
        $rxCnt = 0;
        $updateCnt = 0;
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
                    $this->messageid = $line[4];
                    $is_saved = $this->checkMessageId();
                }
                if (!empty($line)) {
                    $pr = $line[2] ?? '';
                    $provider = explode(":", $pr);
                    $pr = trim($pr);
                    $windate = $line[16] ?? '';
                    $windate = $this->convertToUTC($windate);
                    $ida = $this->convertToUTC($line[0] ?? '');
                    $p = $line[1] ?? '';
                    $pid_and_encounter = explode(":", $p);
                    $pid = intval($pid_and_encounter[0]);
                    $uid = intval($pid_and_encounter[1]);
                    $locId = ($provider[1] ?? '');
                    $r = $line[22] ?? '';
                    $refills = filter_var($r, FILTER_SANITIZE_NUMBER_INT);

                    $insertdata = [];
                    $rec = $this->prescriptionId();
                    $insertdata['id'] = $rec;
                    $active = 1;
                    $insertdata['active'] = $active;
                    $insertdata['date_added'] = $ida;
                    $insertdata['patient_id'] = $pid;
                    $insertdata['attached_user_id'] = $uid;
                    $insertdata['sync_type'] = trim($line[3] ?? '');
                    $insertdata['status'] = trim($line[6] ?? '');
                    $drug = isset($line[11]) ? str_replace('"', '', $line[11]) : ($insertdata['sync_type'] . " " . $insertdata['status'] . " " . xl("Use RxLog"));
                    $insertdata['drug'] = $drug;
                    $insertdata['quantity'] = $line[18] ?? '';
                    $insertdata['refills'] = $refills;
                    $sub = ($line[14] = 'Allowed' ? 1 : 0);
                    $insertdata['substitute'] = $sub ?? '';
                    $insertdata['note'] = $line[21] ?? '';
                    $insertdata['rxnorm_drugcode'] = $line[12] ?? '';
                    $insertdata['provider_id'] = $pr;
                    $insertdata['user_id'] = ($uid > 0) ? $uid : $this->getUserIdByWenoId($provider[0]);
                    $insertdata['prescriptionguid'] = $line[4] ?? '';
                    $insertdata['txDate'] = $ida;
                    $loginsert = new LogDataInsert();
                    if ($is_saved > 0) {
                        $loginsert->updatePrescriptions($insertdata);
                        if (trim($line[7] ?? '') == 'True') {
                            ++$updateCnt;
                        }
                    } else {
                        $loginsert->insertPrescriptions($insertdata);
                        ++$rxCnt;
                    }
                    ++$l;
                }
            }
            fclose($records);
        } else {
            $wenoLog->insertWenoLog("Sync Report", "Missing report file.");
            return false;
        }

        if ($rxCnt == 0 && $updateCnt == 0) {
            $status = xl("No new prescriptions to sync.");
        } else {
            $status = xl("Synced") . " " . text($rxCnt) . " new " . text($updateCnt) . " " . xl("updated")  . " " . xl("prescriptions.");
        }
        $wenoLog->insertWenoLog("Sync Report", $status);
        return true;
    }
}
