<?php

/**
 * PaymentProcessing class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PaymentProcessing;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Uuid\UuidRegistry;

class PaymentProcessing
{
    /**
     * Audit log entry for transaction.
     */
    public static function saveAudit(string $service, int $pid, int $success, array $auditData, ?string $ticket = null, ?string $transactionId = null, ?string $actionName = null, ?string $amount = null): void
    {
        $uuid = (new UuidRegistry(['table_name' => 'payment_processing_audit']))->createUuid();
        $auditData = json_encode($auditData);
        $auditData = (new CryptoGen())->encryptStandard($auditData);
        sqlStatement(
            "INSERT INTO `payment_processing_audit` (`uuid`, `service`, `pid`, `success`, `action_name`, `amount`, `ticket`, `transaction_id`, `audit_data`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $uuid,
                $service,
                $pid,
                $success,
                $actionName,
                $amount,
                $ticket,
                $transactionId,
                $auditData
            ]
        );
    }

    /**
     * Update log entry for when revert transaction.
     */
    public static function saveRevertAudit(string $uuidUpdate, string $actionName, array $auditData, int $success, ?string $transactionId = null)
    {
        $auditData = json_encode($auditData);
        $auditData = (new CryptoGen())->encryptStandard($auditData);
        $uuidUpdate = UuidRegistry::uuidToBytes($uuidUpdate);

        // Update the audit log to show the charge was reverted (if successful)
        if ($success == 1) {
            sqlStatement(
                "UPDATE `payment_processing_audit` SET `revert_action_name` = ?, `revert_transaction_id` = ?, `revert_audit_data` = ?, `revert_date` = NOW(), `reverted` = 1 WHERE `uuid` = ?",
                [
                    $actionName,
                    $transactionId,
                    $auditData,
                    $uuidUpdate
                ]
            );
        }

        // Add a new separate audit log entry for the revert
        $ret = sqlQuery("SELECT `uuid`, `service`, `pid`, `transaction_id`, `amount` FROM `payment_processing_audit` WHERE `uuid` = ?", [$uuidUpdate]);
        $uuidNew = (new UuidRegistry(['table_name' => 'payment_processing_audit']))->createUuid();
        sqlStatement(
            "INSERT INTO `payment_processing_audit` (`uuid`, `service`, `pid`, `success`, `action_name`, `amount`, `transaction_id`, `audit_data`, `map_uuid`, `map_transaction_id`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $uuidNew,
                $ret['service'],
                $ret['pid'],
                $success,
                $actionName,
                $ret['amount'],
                $transactionId,
                $auditData,
                $ret['uuid'],
                $ret['transaction_id']
            ]
        );
    }

    /**
     * Fetch audit log for transactions.
     */
    public static function fetchAudit(string $from, string $to, $pid = null, ?string $service = null, ?string $ticket = null, ?string $transactionId = null, ?string $actionName = null): array
    {
        $sqlBind = [];
        $sql = "SELECT * FROM `payment_processing_audit` WHERE `date` > ? AND `date` < ?";
        $sqlBind[] = $from;
        $sqlBind[] = $to;

        if (!empty($pid)) {
            $sql .= " AND `pid` = ?";
            $sqlBind[] = $pid;
        }

        if (!empty($service)) {
            $sql .= " AND `service` = ?";
            $sqlBind[] = $service;
        }

        if (!empty($ticket)) {
            $sql .= " AND `ticket` = ?";
            $sqlBind[] = $ticket;
        }

        if (!empty($transactionId)) {
            $sql .= " AND `transaction_id` = ?";
            $sqlBind[] = $transactionId;
        }

        if (!empty($actionName)) {
            $sql .= " AND `action_name` = ?";
            $sqlBind[] = $actionName;
        }

        $sql .= " ORDER BY `date` DESC";

        // Collect pertinent information from auditdata
        $cryptoGen = new CryptoGen();
        $return = [];
        $res = sqlStatement($sql, $sqlBind);
        while ($row = sqlFetchArray($res)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);

            // Set up the action label
            if (!empty($row['action_name'])) {
                if ($row['action_name'] == 'Sale') {
                    $row['action_name_label'] = xl("Sale");
                } elseif ($row['action_name'] == 'void') {
                    $row['action_name_label'] = xl("Void");
                } elseif ($row['action_name'] == 'credit') {
                    $row['action_name_label'] = xl("Credit");
                } else {
                    $row['action_name_label'] = xl($row['action_name']);
                }
            }

            // decrypt the audit data
            $auditData = $cryptoGen->decryptStandard($row['audit_data']);
            $auditData = json_decode($auditData, true);

            // Collect the error message if not succcess
            if ($row['success'] != 1) {
                if (!empty($auditData['get']['cancel']) && $auditData['get']['cancel'] == 'cancel') {
                    $row['error_message'] = xl("Cancelled");
                } elseif (!empty($row['action_name']) && ($row['action_name'] == 'Sale')) {
                    $row['error_message'] = $auditData['post']['status_name'] . " - " . $auditData['post']['description'];
                } elseif (!empty($row['action_name']) && ($row['action_name'] == 'void' || $row['action_name'] == 'credit')) {
                    if (!empty($auditData['post']['status']) && (($auditData['post']['status'] == 'baddata') || ($auditData['post']['status'] == 'error'))) {
                        $row['error_message'] = xl("Aborted since unable to submit transaction") . ": " . $auditData['post']['status'] . " " . $auditData['post']['error'] . " " . $auditData['post']['offenders'];
                    } elseif (isset($auditData['check_querystring_hash']) && $auditData['check_querystring_hash'] === false) {
                        $row['error_message'] = xl("querystring hash was invalid");
                    } elseif (!empty($auditData['token_request_error'])) {
                        $row['error_message'] = xl("Aborted since unable to obtain token") . ": " . $auditData['token_request_error'];
                    } elseif (!empty($auditData['error_custom'])) {
                        $row['error_message'] = $auditData['error_custom'];
                    } elseif ($auditData['complete_transaction']['status'] != 'accepted') {
                        $completeRevertToString = "";
                        foreach ($auditData['complete_transaction'] as $key => $value) {
                            if (!empty($key) || !empty($value)) {
                                $completeRevertToString .= $key . ":" . $value . "; ";
                            }
                        }
                        $row['error_message'] = xl("Unable to complete transaction") . ": " . $completeRevertToString;
                    }
                }
            }

            // Collect the front label
            $row['front'] = $auditData['get']['front'] ?? null;
            if ($row['front'] == 'patient') {
                $row['front_label'] = xl("Patient Portal");
            } elseif ($row['front'] == 'clinic-phone') {
                $row['front_label'] = xl("Front Office by Phone");
            } elseif ($row['front'] == 'clinic-retail') {
                $row['front_label'] = xl("Front Office in Person");
            } elseif (!empty($row['front'])) {
                $row['front_label'] = xl($row['front']);
            }

            // See if offer void versus credit
            if (($row['reverted'] != 1) && ($row['action_name'] == "Sale") && ($auditData['post']['status_name'] == 'approved') && ($row['success'] == 1)) {
                if ((new \DateTime($row['date']))->format('Y-m-d') == (new \DateTime('NOW'))->format('Y-m-d')) {
                    // still on same day as charge, so offer void and credit
                    // TODO - appears void is not allowed (d/w Sphere) with current testing custids and also ask Sphere when should be offering void versus credit
                    $row['offer_void'] = true;
                    $row['offer_credit'] = true;
                } else {
                    // no longer on same day as charge, so offer only credit
                    $row['offer_credit'] = true;
                }
            }

            $return[] = $row;
        }

        return $return;
    }
}
