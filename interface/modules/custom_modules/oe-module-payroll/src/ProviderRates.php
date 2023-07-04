<?php

/*
 * @package OpenEMR
 *
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * Copyright (c) 2023.
 * @license "All rights reserved"
 */


namespace Juggernaut\Modules\Payroll;

class ProviderRates
{
    public function getProviders()
    {
        $sql = "SELECT id, fname, lname FROM users WHERE authorized = 1";
        return sqlStatement($sql);
    }

    public function retreiveRates($userid)
    {
        $sql = "SELECT percentage, flat FROM `module_payroll_data` WHERE userid = ?";
        return sqlQuery($sql, [$userid]);
    }

    public function savePayrollData($userid, $percentage, $flat): string
    {
        $doeuserexist = sqlQuery("SELECT userid FROM `module_payroll_data` WHERE  userid = ?", [$userid]);
        if (!isset($doeuserexist['userid'])) {
            $sql = "INSERT INTO `module_payroll_data` SET userid = ?, percentage = ?, flat = ?";
            sqlStatement($sql, [$userid, $percentage, $flat]);
            return "insert completed";
        } else {
            if (!empty($percentage)) {
                $sql = "UPDATE `module_payroll_data` SET percentage = ?, flat = NULL WHERE userid = ?";
                sqlStatement($sql, [$percentage, $userid]);
                return "percentage inserted " . $percentage;
            } else {
                $sql = "UPDATE `module_payroll_data` SET flat = ?, percentage = NULL WHERE userid = ?";
                sqlStatement($sql, [$flat, $userid]);
                return "flat rate inserted " . $flat;
            }
        }
    }
}
