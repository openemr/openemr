<?php

/**
 * Load a fee schedule from a payer
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2023 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

// comment this out when using this script (and then uncomment it again when done using script)
exit;

if (php_sapi_name() !== 'cli') {
    echo "Only php cli can execute command\n";
    echo "example use: php default feesched.txt 10 33 2023-10-01\n";
    die;
}

$_GET['site'] = $argv[1];
$ignoreAuth = true;
require_once __DIR__ . "/../../../interface/globals.php";

use League\Csv\Reader;

// setup a csv file with a header consiting of type, code and modifier
// at the specified location
$filename = DIRECTORY_SEPARATOR . $argv[2];
$filepath = $GLOBALS['temporary_files_dir'];
$reader = Reader::createFromPath($filepath . $filename);
$reader->setDelimiter("\t");

$start_record = $argv[3];
$reader->setHeaderOffset($start_record);
$header = $reader->getHeader();

$insurance_company_id = $argv[4];
$effective_date = $argv[5] ?? '';
$records = $reader->getRecords($header);
foreach ($records as $offset => $record) {
    if (trim($record['type'] ?? '') == "VT") {
        $sched_plan = trim($record['plan'] ?? '');
        $sched_code = trim($record['code'] ?? '');
        $sched_mod = trim($record['modifier'] ?? '');
        $sched_fee = number_format($record['fee'], 2, '.', '');
        $sched_type = trim($record['type'] ?? '');

        $codes_sql = sqlQuery("SELECT `codes`.*, `prices`.`pr_id`, `prices`.`pr_price` as fee FROM `codes` LEFT JOIN `prices` ON `prices`.`pr_id` = `codes`.`id` WHERE `code` = ? AND `modifier` = ?", [$sched_code, $sched_mod]);
        if (!empty($codes_sql)) {
            $price_id = $codes_sql['id'];
            $our_code = $codes_sql['code'];
            $our_fee = $codes_sql['fee'];
            $our_mod = trim($codes_sql['modifier'] ?? '');
        } else {
            continue;
        }

        if (
            $our_code == $sched_code
            && $our_mod == $sched_mod
        ) {
            $sql = "INSERT INTO `fee_schedule` (`insurance_company_id`, `plan`, `code`, `modifier`, `type`, `fee`, `effective_date`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            sqlQuery($sql, array($insurance_company_id, $sched_plan, $sched_code, $sched_mod, $sched_type, $sched_fee, $effective_date));
            if ($codes_sql['fee'] < $sched_fee) {
                $ceil_fee = number_format(ceil($sched_fee), 2, '.', '');
                echo "*** existing fee " . sprintf("%7.2f", $our_fee) . " for $our_code:$our_mod " .
                    "is less than their fee of " . sprintf("%7.2f", $sched_fee) . "\n";
                // uncomment below 3 lines to update prices accordingly
                /*echo "update prices table for code $our_code:$our_mod from " . $our_fee .
                    " to ". $ceil_fee . " with price id " . $price_id . "\n";
                $update_prices = sqlQuery("UPDATE `prices` SET `pr_price` = ? WHERE `pr_id` = ?", [$ceil_fee, $price_id]);
                */
            }
        }
    }
}
