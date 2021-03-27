<?php

/**
 * checkout_receipt_array.inc.php
 *
 * This provides a function to build an array of data for printing on a checkout
 * receipt.  The idea is to support a receipt-printing script that is very easy
 * to understand and customize and does not have to be concerned with where the
 * data comes from.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see
 * http://www.gnu.org/licenses/licenses.html#GPL .
 *
 * @package OpenEMR
 * @license http://www.gnu.org/licenses/licenses.html#GPL GNU GPL V3+
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2013-2021 Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */

// Get a list item's title, translated if appropriate.
function getAdjustTitle($option)
{
    $row = sqlQuery(
        "SELECT title, notes FROM list_options WHERE " .
        "list_id = 'adjreason' AND option_id = ? AND activity = 1",
        array($option)
    );
    if (empty($row['title'])) {
        return $option;
    }
    return xl_list_label($row['title']);
}

// Store a receipt line item.
//
function receiptArrayDetailLine(
    &$aReceipt,
    $code_type,
    $code,
    $description,
    $quantity,
    $charge,
    $billtime = '',
    $tax = 0,
    $chargecat = ''
) {
    $adjust = 0;
    $adjreason = '';

    // This supports Suriname insurers.
    // It is set to true when the Customer is set and its notes include "=Ins".
    $insurer = false;
    if ($chargecat) {
        $tmprow = sqlQuery(
            "SELECT notes FROM list_options WHERE list_id = 'chargecats' AND " .
            "option_id = ? AND activity = 1 AND notes LIKE '%=Ins%' LIMIT 1",
            array($chargecat)
        );
        if (!empty($tmprow['notes'])) {
            $insurer = true;
        }
    }

    // If an invoice level adjustment, get it into the right column.
    if ($code_type === '') {
        $adjust = 0 - $charge;
        $charge = 0;
    } else {
        // Otherwise pull out any adjustments matching this line item.
        if (!empty($GLOBALS['gbl_checkout_line_adjustments'])) {
            // Total and clear matching adjustments in $aReceipt['_adjusts'].
            for ($i = 0; $i < count($aReceipt['_adjusts']); ++$i) {
                if (
                    $aReceipt['_adjusts'][$i]['code_type'] == $code_type &&
                    $aReceipt['_adjusts'][$i]['code'] == $code &&
                    $aReceipt['_adjusts'][$i]['post_time'] == $billtime
                ) {
                    $adjust += $aReceipt['_adjusts'][$i]['adj_amount'];
                    if ($aReceipt['_adjusts'][$i]['memo']) {
                        $adjreason = getAdjustTitle($aReceipt['_adjusts'][$i]['memo']);
                    }
                    $aReceipt['_adjusts'][$i]['adj_amount'] = 0;
                }
            }
        }
    }

    $charge = sprintf('%01.2f', $charge);
    $total  = sprintf('%01.2f', $charge - $adjust);
    if (empty($quantity)) {
        $quantity = 1;
    }
    $price = sprintf('%01.4f', $charge / $quantity);
    $tmp = sprintf('%01.2f', $price);
    if ($price == $tmp) {
        $price = $tmp; // converts xx.xx00 to xx.xx.
    }

    $aReceipt['items'][] = array(
        'code_type'   => $code_type,
        'code'        => $code,
        'description' => $description,
        'price'       => $price,
        'quantity'    => $quantity,
        'charge'      => $charge,
        'adjustment'  => sprintf('%01.2f', $adjust),
        'adjreason'   => $adjreason,
        'total'       => $total,
        'insurer'     => $insurer,
        'tax'         => $tax,
    );

    $aReceipt['total_price']       = sprintf('%01.2f', $aReceipt['total_price'      ] + $price);
    $aReceipt['total_quantity']    = sprintf('%01.2f', $aReceipt['total_quantity'   ] + $quantity);
    $aReceipt['total_charges']     = sprintf('%01.2f', $aReceipt['total_charge'     ] + $charge);
    $aReceipt['total_adjustments'] = sprintf('%01.2f', $aReceipt['total_adjustments'] + $adjust);
    $aReceipt['total_totals']      = sprintf('%01.2f', $aReceipt['total_totals'     ] + $total);
}

// Store a receipt payment line.
//
function receiptArrayPaymentLine(&$aReceipt, $paydate, $amount, $description = '', $method = '')
{
    $amount = sprintf('%01.2f', $amount);
    $aReceipt['payments'][] = array(
        'date'        => $paydate,
        'method'      => $method,
        'description' => $description,
        'amount'      => $amount,
    );
    $aReceipt['total_payments'] += $amount;
}

// Generate a receipt data array from the last-billed invoice for this patient,
// or from the specified encounter.
//
function generateReceiptArray($patient_id, $encounter = 0, $billtime = '')
{
    // Get the most recent invoice data or that for the specified encounter.
    $query = "SELECT " .
        "fe.id, fe.date, fe.encounter, fe.facility_id, fe.invoice_refno, " .
        "u.fname, u.mname, u.lname " .
        "FROM form_encounter AS fe " .
        "LEFT JOIN users AS u ON u.id = fe.provider_id " .
        "WHERE fe.pid = ? ";
    $binds = array($patient_id);
    if ($encounter) {
        $query .= "AND encounter = ?";
        $binds[] = $encounter;
    } else {
        $query .= "ORDER BY id DESC LIMIT 1";
    }
    $ferow = sqlQuery($query, $binds);
    if (empty($ferow)) {
        die(xlt("This patient has no activity."));
    }
    $trans_id = $ferow['id'];
    $encounter = $ferow['encounter'];
    $svcdate = substr($ferow['date'], 0, 10);
    $invoice_refno = $ferow['invoice_refno'];
    $docname = '';
    if (!empty($ferow['fname'])) {
        $docname = trim($ferow['fname']);
    }
    if (!empty($ferow['mname'])) {
        if ($docname) {
            $docname .= ' ';
        }
        $docname .= trim($ferow['fname']);
    }
    if (!empty($ferow['lname'])) {
        if ($docname) {
            $docname .= ' ';
        }
        $docname .= trim($ferow['lname']);
    }

    // Get details for the visit's facility and organization facility.
    $frow = getFacility($ferow['facility_id']);
    $orgrow = getFacility(0);

    $patdata = getPatientData($patient_id, 'fname,mname,lname,pubpid,street,city,state,postal_code');

    // Get text for the logged-in user's name (first middle last).
    $username = "UID: " . $_SESSION["authUserID"];
    $userrow = sqlQuery(
        "SELECT id, username, fname, mname, lname FROM users " .
        "WHERE id = ?",
        array($_SESSION["authUserID"])
    );
    if ($userrow['id']) {
        if (!empty($userrow['fname'])) {
            $username = $userrow['fname'];
        }
        if (!empty($userrow['mname'])) {
            if (!empty($username)) {
                $username .= ' ';
            }
            $username .= $userrow['mname'];
        }
        if (!empty($userrow['lname'])) {
            if (!empty($username)) {
                $username .= ' ';
            }
            $username .= $userrow['lname'];
        }
    }

    // Compute numbers for summary on right side of page.
    $head_begbal = get_patient_balance_excluding($patient_id, $encounter);

    $query = "SELECT SUM(fee) AS amount FROM billing WHERE " .
        "pid = ? AND encounter = ? AND activity = 1 AND " .
        "code_type != 'COPAY'";
    $binds = array($patient_id, $encounter);
    if ($billtime) {
        $query .= " AND billed = 1 AND bill_date <= ?";
        $binds[] = $billtime;
    }
    $row = sqlQuery($query, $binds);
    $head_charges = $row['amount'];

    $query = "SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
        "pid = ? AND encounter = ?";
    $binds = array($patient_id, $encounter);
    if ($billtime) {
        $query .= " AND billed = 1 AND bill_date <= ?";
        $binds[] = $billtime;
    }
    $row = sqlQuery($query, $binds);
    $head_charges += $row['amount'];

    $query = "SELECT SUM(pay_amount) AS payments, " .
        "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " .
        "deleted IS NULL AND pid = ? AND encounter = ?";
    $binds = array($patient_id, $encounter);
    if ($billtime) {
        $query .= " AND post_time <= ?";
        $binds[] = $billtime;
    }
    $row = sqlQuery($query, $binds);
    $head_charges -= $row['adjustments'];
    $head_payments = $row['payments'];

    $query = "SELECT SUM(fee) AS amount FROM billing WHERE " .
        "pid = ? AND encounter = ? AND activity = 1 AND " .
        "code_type = 'COPAY'";
    $binds = array($patient_id, $encounter);
    if ($billtime) {
        $query .= " AND billed = 1 AND bill_date <= ?";
        $binds[] = $billtime;
    }
    $row = sqlQuery($query, $binds);
    $head_payments -= $row['amount'];

    // This is the amount due for the encounter as a whole up to this point.
    $encounter_due = $head_charges - $head_payments;
    $head_endbal = $head_begbal + $encounter_due;

    $aReceipt = array(
        'encounter_id'      => $encounter,
        'encounter_date'    => $svcdate,
        'invoice_refno'     => $invoice_refno,
        'patient_id'        => $patient_id,
        'patient_pubpid'    => $patdata['pubpid'],
        'patient_fname'     => $patdata['fname'],
        'patient_mname'     => $patdata['mname'],
        'patient_lname'     => $patdata['lname'],
        'patient_street'    => $patdata['street'],
        'patient_city'      => $patdata['city'],
        'patient_state'     => $patdata['state'],
        'patient_zip'       => $patdata['postal_code'],
        'facility_id'       => $frow['id'],
        'facility_name'     => $frow['name'],
        'facility_street'   => $frow['street'],
        'facility_city'     => $frow['city'],
        'facility_state'    => $frow['state'],
        'facility_zip'      => $frow['postal_code'],
        'facility_phone'    => $frow['phone'],
        'facility_fax'      => $frow['fax'],
        'facility_url'      => empty($frow['website']) ? '' : $frow['website'],
        'organization_name' => $orgrow['name'],
        'docname'           => $docname,
        'username'          => $username,
        'userlogin'         => $userrow['username'],
        'starting_balance'  => $head_begbal,
        'ending_balance'    => $head_endbal,
        'items'             => array(),
        'payments'          => array(),
        'total_price'       => 0,
        'total_quantity'    => 0,
        'total_charges'     => 0,
        'total_adjustments' => 0,
        'total_totals'      => 0,
        'total_payments'    => 0,
        'checkout_prvbal'   => 0, // see calculation at end of this function
        'encounter_due'     => $encounter_due,
        'cashier'           => '',
    );

    // Get timestamp of the previous checkout, if any.
    $prevtime = '1900-01-01 00:00:00';
    if ($billtime) {
        $tmp = sqlQuery(
            "(SELECT bill_date FROM billing WHERE pid = ? AND encounter = ? AND activity = 1 AND billed = 1 AND bill_date < ?) " .
            "UNION " .
            "(SELECT bill_date FROM drug_sales WHERE pid = ? AND encounter = ? AND billed = 1 AND bill_date < ?) " .
            "ORDER BY bill_date DESC LIMIT 1",
            array($patient_id, $encounter, $billtime, $patient_id, $encounter, $billtime)
        );
        if (!empty($tmp['bill_date'])) {
            $prevtime = $tmp['bill_date'];
        }
    }

    // Create array aAdjusts from ar_activity rows for $inv_encounter.
    $aReceipt['_adjusts'] = array();
    $ares = sqlStatement(
        "SELECT " .
        "a.payer_type, a.adj_amount, a.memo, a.code_type, a.code, a.post_time, " .
        "s.session_id, s.reference, s.check_date " .
        "FROM ar_activity AS a " .
        "LEFT JOIN ar_session AS s ON s.session_id = a.session_id WHERE " .
        "a.deleted IS NULL AND a.pid = ? AND a.encounter = ? AND " .
        "(a.adj_amount != 0 || a.pay_amount = 0)",
        array($patient_id, $encounter)
    );
    while ($arow = sqlFetchArray($ares)) {
        if ($billtime && $arow['post_time'] != $billtime) {
            continue;
        }
        $aReceipt['_adjusts'][] = $arow;
    }

    // Product sales
    $inres = sqlStatement(
        "SELECT s.sale_id, s.sale_date, s.fee, s.billed, " .
        "s.bill_date, s.quantity, s.drug_id, s.selector, s.user, s.chargecat, d.name " .
        "FROM drug_sales AS s LEFT JOIN drugs AS d ON d.drug_id = s.drug_id " .
        "WHERE s.pid = ? AND s.encounter = ? " .
        "ORDER BY s.sale_id",
        array($patient_id, $encounter)
    );
    while ($inrow = sqlFetchArray($inres)) {
        if ($billtime && $inrow['bill_date'] != $billtime) {
            continue;
        }
        $tmpname = $inrow['name'];
        if ($tmpname !== $inrow['selector']) {
            $tmpname .= ' / ' . $inrow['selector'];
        }
        $taxrow = sqlQuery(
            "SELECT SUM(fee) AS fee FROM billing WHERE " .
            "pid = ? AND encounter = ? AND code_type = 'TAX' AND activity = 1 AND ndc_info = ?",
            array($patient_id, $encounter, 'P:' . $inrow['sale_id'])
        );
        $tax = $taxrow['fee'];
        receiptArrayDetailLine(
            $aReceipt,
            'PROD',
            $inrow['drug_id'],
            $tmpname,
            $inrow['quantity'],
            $inrow['fee'],
            $inrow['bill_date'],
            $tax,
            $inrow['chargecat']
        );
        if (!empty($inrow['user'])) {
            $aReceipt['cashier'] = $inrow['user'];
        }
    }

    // Service items. Use short code description when available.
    $inres = sqlStatement(
        "SELECT b.*, c.code_text_short, u.username FROM billing AS b " .
        "LEFT JOIN code_types AS ct ON ct.ct_key = b.code_type " .
        "LEFT JOIN codes AS c ON c.code_type = ct.ct_id AND c.code = b.code AND c.modifier = b.modifier " .
        "LEFT JOIN users AS u on u.id = b.user " .
        "WHERE " .
        "b.pid = ? AND b.encounter = ? AND " .
        "b.code_type != 'COPAY' AND b.code_type != 'TAX' AND b.activity = 1 AND " .
        "(ct.ct_id IS NULL OR ct.ct_fee = 1 OR ct.ct_diag = 0) " .
        "ORDER BY b.id",
        array($patient_id, $encounter)
    );
    while ($inrow = sqlFetchArray($inres)) {
        if ($billtime && $inrow['bill_date'] != $billtime) {
            continue;
        }
        $code_text = empty($inrow['code_text_short']) ? $inrow['code_text'] : $inrow['code_text_short'];
        $taxrow = sqlQuery(
            "SELECT SUM(fee) AS fee FROM billing WHERE " .
            "pid = ? AND encounter = ? AND code_type = 'TAX' AND activity = 1 AND ndc_info = ?",
            array($patient_id, $encounter, 'S:' . $inrow['id'])
        );
        $tax = $taxrow['fee'];
        receiptArrayDetailLine(
            $aReceipt,
            $inrow['code_type'],
            $inrow['code'],
            $code_text,
            $inrow['units'],
            $inrow['fee'],
            $inrow['bill_date'],
            $tax,
            $inrow['chargecat']
        );
        if (!empty($inrow['username'])) {
            $aReceipt['cashier'] = $inrow['username'];
        }
    }

    // Write any adjustments left in the aAdjusts array.
    foreach ($aReceipt['_adjusts'] as $arow) {
        if ($arow['adj_amount'] == 0) {
            continue;
        }
        if ($billtime && $arow['post_time'] != $billtime) {
            continue;
        }
        // $payer = empty($arow['payer_type']) ? 'Pt' : ('Ins' . $arow['payer_type']);
        receiptArrayDetailLine(
            $aReceipt,
            '',
            xl('Adjustment'),
            getAdjustTitle($arow['memo']),
            1,
            0 - $arow['adj_amount']
        );
    }

    // Tax items.
    $inres = sqlStatement(
        "SELECT * FROM billing WHERE " .
        "pid = ? AND encounter = ? AND code_type = 'TAX' AND activity = 1 " .
        "ORDER BY id",
        array($patient_id, $encounter)
    );
    while ($inrow = sqlFetchArray($inres)) {
        if ($billtime && $inrow['bill_date'] != $billtime) {
            continue;
        }
        receiptArrayDetailLine(
            $aReceipt,
            $inrow['code_type'],
            $inrow['code'],
            $inrow['code_text'],
            1,
            $inrow['fee']
        );
    }

    $payments = 0;

    // Get co-pays.
    $inres = sqlStatement(
        "SELECT fee, code_text, bill_date FROM billing WHERE " .
        "pid = ? AND encounter = ? AND " .
        "code_type = 'COPAY' AND activity = 1 AND fee != 0 " .
        "ORDER BY id",
        array($patient_id, $encounter)
    );
    while ($inrow = sqlFetchArray($inres)) {
        if ($billtime && $inrow['bill_date'] != $billtime) {
            continue;
        }
        $payments -= sprintf('%01.2f', $inrow['fee']);
        receiptArrayPaymentLine(
            $aReceipt,
            $svcdate,
            0 - $inrow['fee'],
            $inrow['code_text'],
            'COPAY'
        );
    }

    // Get other payments.
    $inres = sqlStatement(
        "SELECT " .
        "a.code, a.modifier, a.memo, a.payer_type, a.adj_amount, a.pay_amount, a.post_time, " .
        "s.payer_id, s.reference, s.check_date, s.deposit_date " .
        "FROM ar_activity AS a " .
        "LEFT JOIN ar_session AS s ON s.session_id = a.session_id WHERE " .
        "a.deleted IS NULL AND a.pid = ? AND a.encounter = ? AND " .
        "a.pay_amount != 0 " .
        "ORDER BY a.post_time, s.check_date, a.sequence_no",
        array($patient_id, $encounter)
    );
    $payer = empty($inrow['payer_type']) ? 'Pt' : ('Ins' . $inrow['payer_type']);
    while ($inrow = sqlFetchArray($inres)) {
        $meth = $inrow['memo'];
        if ($billtime && $inrow['post_time'] != $billtime) {
            if ($inrow['post_time'] > $billtime || $inrow['post_time'] <= $prevtime) {
                continue;
            }
            // This is the case of payments after the previous checkout and before this checkout.
            $meth = xl('Previous');
        }
        $payments += sprintf('%01.2f', $inrow['pay_amount']);
        receiptArrayPaymentLine(
            $aReceipt,
            $svcdate,
            $inrow['pay_amount'],
            $payer . ' ' . $inrow['reference'],
            $meth
        );
    }

    // Previous balance for this checkout is local to the encounter, and is the total balance
    // for the encounter up to this time minus total balance for this checkout.
    // If there is no $billtime then this should always be zero.
    $aReceipt['checkout_prvbal'] = $aReceipt['encounter_due'] - $aReceipt['total_totals'] + $aReceipt['total_payments'];

    // Get cashier as the person who did the checkout. If there was no checkout then it will
    // remain as the person who entered the Fee Sheet.
    $query = "SELECT u.username FROM ar_activity AS a" .
        " JOIN users AS u ON u.id = a.post_user" .
        " WHERE" .
        " a.deleted IS NULL AND a.pid = ? AND a.encounter = ?";
    $binds = array($patient_id, $encounter);
    if ($billtime) {
        $query .= " AND a.post_time = ?";
        $binds[] = $billtime;
    }
    $query .= " ORDER BY a.sequence_no DESC LIMIT 1";
    $inrow = sqlQuery($query, $binds);
    if (!empty($inrow['username'])) {
        $aReceipt['cashier'] = $inrow['username'];
    }

    return $aReceipt;
}

// Get the array of checkout timestamps for the specified visit.
//
function craGetTimestamps($patient_id, $encounter_id)
{
    $ret = array();
    $res = sqlStatement(
        "(SELECT bill_date FROM billing WHERE pid = ? AND encounter = ? AND activity = 1 AND billed = 1) " .
        "UNION " .
        "(SELECT bill_date FROM drug_sales WHERE pid = ? AND encounter = ? AND billed = 1) " .
        "ORDER BY bill_date",
        array($patient_id, $encounter_id, $patient_id, $encounter_id)
    );
    while ($row = sqlFetchArray($res)) {
        $ret[] = $row['bill_date'];
    }
    return $ret;
}
