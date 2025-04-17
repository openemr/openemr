<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once('ClinicalType.php');

class Characteristic extends ClinicalType
{
    const TERMINAL_ILLNESS = 'terminal_illness';
    const TOBACCO_USER = 'char_tobacco_user';
    const TOBACCO_NON_USER = 'char_tobacco_non_user';

    public function getListId()
    {
        return 'Clinical_Rules_Char_Types';
    }

    public function doPatientCheck(RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        $return = false;

        if ($this->getOptionId() == self::TERMINAL_ILLNESS) {
            // TODO check for terminal illness
        } elseif ($this->getOptionId() == self::TOBACCO_USER) {
            $tobaccoHistory = getHistoryData($patient->id, "tobacco", $beginDate, $endDate);

            if (isset($tobaccoHistory['tobacco'])) {
                $tmp = explode('|', $tobaccoHistory['tobacco']);
                $tobaccoStatus = $tmp[1];
                if ($tobaccoStatus == 'currenttobacco') {
                    $return = true;
                } elseif ($tobaccoStatus == 'quittobacco') {
                    $quitDate = $tmp[2];
                    if (strtotime($quitDate) > strtotime($beginDate)) {
                        $return = true;
                    }
                }
            }
        } elseif ($this->getOptionId() == self::TOBACCO_NON_USER) {
            $tobaccoHistory = getHistoryData($patient->id, "tobacco", $beginDate, $endDate);
            if (isset($tobaccoHistory['tobacco'])) {
                $tmp = explode('|', $tobaccoHistory['tobacco']);
                $tobaccoStatus = $tmp[1];
                if ($tobaccoStatus == 'quittobacco') {
                    $quitDate = $tmp[2];
                    if (strtotime($quitDate) < strtotime($beginDate)) {
                        $return = true;
                    }
                } elseif ($tobaccoStatus == 'nevertobacco') {
                    $return = true;
                }
            }
        }

        return $return;
    }
}
