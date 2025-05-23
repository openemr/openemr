<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  All Rights Reserved
 */

namespace Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller;

class ListAuthorizations
{
    private $pid;

    /**
     * @param mixed $pid
     */
    public function setPid($pid): void
    {
        $this->pid = $pid;
    }

    public function __construct()
    {
        //do epic stuff
    }

    public function getAllAuthorizations()
    {
        $sql = "SELECT *
                      FROM module_prior_authorizations
                      WHERE pid = ? ORDER BY `start_date` DESC";
        return sqlStatement($sql, [$this->pid]);
    }

    private static function getAuthsFromModulePriorAuth(): array
    {
        $sql = "SELECT auth_num FROM module_prior_authorizations WHERE pid = ?";
        $auths = sqlStatement($sql, [$_SESSION['pid'] ?? null]);
        $auth_array = [];
        while ($row = sqlFetchArray($auths)) {
            $auth_array[] = $row['auth_num'];
        }
        return $auth_array;
    }

    /**
     * @return void
     * this method is to back populate the module table in case just uses the prior auth form
     * or they have already been using the misc billing options
     * this is a silent function
     */
    public static function insertMissingAuthsFromForm(): void
    {
        $formsAuths = self::formPriorAuth();
        $formMiscBilling = self::formMiscBilling();
        $array_merger = array_push($formsAuths, $formMiscBilling) ?? null;
        $moduleAuths = self::getAuthsFromModulePriorAuth() ?? null;
        if (is_array($moduleAuths) && is_array($array_merger)) {
            $insertArray = array_diff($moduleAuths, $array_merger);

            if (!empty($insertArray)) {
                foreach ($insertArray as $auth) {
                    $isinstalled = sqlQuery("SELECT 1 FROM `form_prior_auth` LIMIT 1");
                    if ($isinstalled !== false) {
                        $getinfo = sqlQuery("SELECT date_from, date_to FROM `form_prior_auth` WHERE `prior_auth_number` = ? ORDER BY `id` DESC LIMIT 1 ", [$auth]);
                    }
                    if (!empty($getinfo['date_from'])) {
                        $saveInfoWithDate = "INSERT INTO `module_prior_authorizations` SET `id` = '', `pid` = ?, `auth_num` = ?, `start_date` = ?, `end_date` = ?";
                        $bindArray = [$_SESSION['pid'], $auth, $getinfo['date_from'], $getinfo['date_to']];
                        sqlStatement($saveInfoWithDate, $bindArray);
                    } elseif (!empty($auth)) {
                        $saveInfoWithDate = "INSERT INTO `module_prior_authorizations` SET `id` = '', `pid` = ?, `auth_num` = ?";
                        $bindArray = [$_SESSION['pid'], $auth];
                        sqlStatement($saveInfoWithDate, $bindArray);
                    }
                }
            }
        }
    }
    /**
     * @return array
     * from form prior auth
     */
    private static function formPriorAuth(): array
    {
        $doesExist = sqlQuery("SELECT table_name FROM information_schema.tables WHERE table_name = 'form_form_prior_auth'");
        $auths_array = [];
        if (!empty($doesExist)) {
            $sql = "select prior_auth_number from form_prior_auth where pid = ?";
            $auths = sqlStatement($sql, [$_SESSION['pid']]);
            while ($row = sqlFetchArray($auths)) {
                $auths_array[] = $row['prior_auth_number'];
            }
            return $auths_array;
        }
        return $auths_array;
    }

    /**
     * @return array
     */
    private static function formMiscBilling()
    {
        $sql = "select prior_auth_number from form_misc_billing_options where pid = ?";
        $auths = sqlStatement($sql, [$_SESSION['pid'] ?? null]);
        $auths_array = [];
        while ($row = sqlFetchArray($auths)) {
            $auths_array[] = $row['prior_auth_number'];
        }
        return $auths_array;
    }

    public function findTriwestClients()
    {
        $list = [];
        $sql = "SELECT `pid`  FROM `insurance_data` WHERE `provider` LIKE '133' ORDER BY `id` ASC";
        $load = sqlStatement($sql);
        while ($row = sqlFetchArray($load)) {
            $list[] = $row['pid'];
        }
        return $list;
    }
}
