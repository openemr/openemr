<?php

// Copyright (C) 2006-2016 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Modified 7-2009 by BM in order to migrate using the form,
// unit, route, and interval lists with the
// functions in openemr/library/options.inc.php .
// These lists are based on the constants found in the
// openemr/library/classes/Prescription.class.php file.

use PHPMailer\PHPMailer\PHPMailer;
use OpenEMR\Services\DrugSalesService;

// Decision was made in June 2013 that a sale line item in the Fee Sheet may
// come only from the specified warehouse. Set this to false if the decision
// is reversed.
$GLOBALS['SELL_FROM_ONE_WAREHOUSE'] = true;

$substitute_array = ['', xl('Allowed'), xl('Not Allowed')];

function send_drug_email($subject, $body): void
{
    $recipient = $GLOBALS['practice_return_email_path'];
    if (empty($recipient)) {
        return;
    }

    $mail = new PHPMailer();
    $mail->From = $recipient;
    $mail->FromName = 'In-House Pharmacy';
    $mail->isMail();
    $mail->Host = "localhost";
    $mail->Mailer = "mail";
    $mail->Body = $body;
    $mail->Subject = $subject;
    $mail->AddAddress($recipient);
    if (!$mail->Send()) {
        error_log("There has been a mail error sending to " . errorLogEscape($recipient .
        " " . $mail->ErrorInfo));
    }
}

/**
 * @deprecated Use DrugSalesService::sellDrug instead.
 * @param $drug_id
 * @param $quantity
 * @param $fee
 * @param $patient_id
 * @param $encounter_id
 * @param $prescription_id
 * @param $sale_date
 * @param $user
 * @param $default_warehouse
 * @param $testonly
 * @param $expiredlots
 * @param $pricelevel
 * @param $selector
 * @return bool|int|void
 */
function sellDrug(
    $drug_id,
    $quantity,
    $fee,
    $patient_id = 0,
    $encounter_id = 0,
    $prescription_id = 0,
    $sale_date = '',
    $user = '',
    $default_warehouse = '',
    $testonly = false,
    &$expiredlots = null,
    $pricelevel = '',
    $selector = ''
) {
    $drugSalesService = new DrugSalesService();
    return $drugSalesService->sellDrug(
        $drug_id,
        $quantity,
        $fee,
        $patient_id,
        $encounter_id,
        $prescription_id,
        $sale_date,
        $user,
        $default_warehouse,
        $testonly,
        $expiredlots,
        $pricelevel,
        $selector
    );
}

// Determine if facility and warehouse restrictions are applicable for this user.
function isUserRestricted($userid = 0)
{
    if (!$userid) {
        $userid = $_SESSION['authUserID'];
    }

    $countrow = sqlQuery("SELECT count(*) AS count FROM users_facility WHERE " .
    "tablename = 'users' AND table_id = ?", [$userid]);
    return !empty($countrow['count']);
}

// Check if the user has access to the given facility.
// Do not call this if user is not restricted!
function isFacilityAllowed($facid, $userid = 0)
{
    if (!$userid) {
        $userid = $_SESSION['authUserID'];
    }

    $countrow = sqlQuery(
        "SELECT count(*) AS count FROM users_facility WHERE " .
        "tablename = 'users' AND table_id = ? AND facility_id = ?",
        [$userid, $facid]
    );
    if (empty($countrow['count'])) {
        $countrow = sqlQuery(
            "SELECT count(*) AS count FROM users WHERE " .
            "id = ? AND facility_id = ?",
            [$userid, $facid]
        );
        return !empty($countrow['count']);
    }

    return true;
}

// Check if the user has access to the given warehouse within the given facility.
// Do not call this if user is not restricted!
function isWarehouseAllowed($facid, $whid, $userid = 0)
{
    if (!$userid) {
        $userid = $_SESSION['authUserID'];
    }

    $countrow = sqlQuery(
        "SELECT count(*) AS count FROM users_facility WHERE " .
        "tablename = 'users' AND table_id = ? AND facility_id = ? AND " .
        "(warehouse_id = ? OR warehouse_id = '')",
        [$userid, $facid, $whid]
    );
    if (empty($countrow['count'])) {
        $countrow = sqlQuery(
            "SELECT count(*) AS count FROM users WHERE " .
            "id = ? AND default_warehouse = ?",
            [$userid, $whid]
        );
        return !empty($countrow['count']);
    }

    return true;
}

// Determine if this product is one that we have on hand and that the user has permission for.
//
function isProductSelectable($drug_id)
{
    $is_user_restricted = isUserRestricted();
    $wfres = sqlStatement(
        "SELECT di.warehouse_id, lo.option_value AS facid " .
        "FROM drug_inventory AS di " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "WHERE di.drug_id = ? AND di.destroy_date IS NULL AND di.on_hand > 0 AND " .
        "(di.expiration IS NULL OR di.expiration > NOW())",
        [$drug_id]
    );
    while ($wfrow = sqlFetchArray($wfres)) {
        if ($is_user_restricted && !isWarehouseAllowed($wfrow['facid'], $wfrow['warehouse_id'])) {
            continue;
        }
        return true;
    }
    return false;
}
