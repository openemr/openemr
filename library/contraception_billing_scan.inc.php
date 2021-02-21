<?php

/**
* Copyright (C) 2012-2021 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @link      http://www.open-emr.org
*/

use OpenEMR\Billing\BillingUtilities;

// These variables are used to compute the service with highest CYP.
//
$contraception_billing_code = '';
$contraception_billing_cyp  = -1;
$contraception_billing_prov = 0;

// This is called for each service in the visit to determine the method
// of the service with highest CYP.
//
function _contraception_billing_check($code_type, $code, $provider)
{
    global $code_types;
    global $contraception_billing_code, $contraception_billing_cyp, $contraception_billing_prov;

    if ($code_type != 'MA') {
        return;
    }

    // The cyp_factor test in this query is to select only MA codes that
    // are flagged as Initial Consult.
    $sql = "SELECT related_code FROM codes WHERE " .
        "code_type = ? AND code = ? AND cyp_factor != 0 LIMIT 1";
    $codesrow = sqlQuery($sql, array($code_types[$code_type]['id'], $code));

    if (!empty($codesrow['related_code'])) {
        $relcodes = explode(';', $codesrow['related_code']);
        foreach ($relcodes as $relstring) {
            if ($relstring === '') {
                continue;
            }
            list($reltype, $relcode) = explode(':', $relstring);
            if ($reltype !== 'IPPFCM') {
                continue;
            }
            $tmprow = sqlQuery(
                "SELECT cyp_factor FROM codes WHERE " .
                "code_type = '32' AND code = ? LIMIT 1",
                array($relcode)
            );
            $cyp = 0 + $tmprow['cyp_factor'];
            if ($cyp > $contraception_billing_cyp) {
                $contraception_billing_cyp  = $cyp;
                $contraception_billing_code = $relcode;
                $contraception_billing_prov = $provider;
            }
        }
    }
}

// Get the contraceptive method (IPPFCM) code $contraception_billing_code, if any,
// indicated by an initial-consult contraception service in the visit.  If
// there is more than one then the code with highest CYP is selected.  This
// call returns TRUE if one is found, otherwise FALSE.  Also set is the
// provider of that service, $contraception_billing_prov, and the corresponding
// CYP value $contraception_billing_cyp.
//
function contraception_billing_scan($patient_id, $encounter_id, $provider_id = 0)
{
    global $contraception_billing_code, $contraception_billing_cyp, $contraception_billing_prov;

    $contraception_billing_code = '';
    $contraception_billing_cyp  = -1;
    $contraception_billing_prov = 0;

    $billresult = BillingUtilities::getBillingByEncounter($patient_id, $encounter_id, "*");
    if (is_array($billresult)) {
        foreach ($billresult as $iter) {
            _contraception_billing_check($iter["code_type"], trim($iter["code"]), $iter['provider_id']);
        }
    }
    // If no provider at the line level, use the encounter's default provider.
    if (empty($contraception_billing_prov)) {
        $contraception_billing_prov = 0 + $provider_id;
    }
    if (!empty($contraception_billing_code)) {
        return true;
    }
    return false;
}
