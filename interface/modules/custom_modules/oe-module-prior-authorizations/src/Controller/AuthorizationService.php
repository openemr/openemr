<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  All Rights Reserved
 */

namespace Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller;

use OpenEMR\Common\Database\QueryUtils;

class AuthorizationService
{
    private const MODULE_TABLE = 'module_prior_authorizations';
    private ?int $id = null;
    private ?int $pid = null;
    private ?string $auth_num = null;
    private ?string $start_date = null;
    private ?string $end_date = null;
    private ?string $cpt = null;
    private ?int $init_units = null;
    private ?int $remaining_units = null;

    public function storeAuthorizationInfo(): void
    {
        $statement = "INSERT INTO " . self::MODULE_TABLE .
            "(`id`, `pid`, `auth_num`, `start_date`, `end_date`, `cpt`, `init_units`, `remaining_units`) " .
            "VALUES (?,?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE " .
            "auth_num = VALUES(auth_num), start_date = VALUES(start_date), end_date = VALUES(end_date), " .
            "cpt = VALUES(cpt), init_units = VALUES(init_units)";

        $binding = [];
        $binding[] = $this->id;
        $binding[] = $this->pid;
        $binding[] = $this->auth_num;
        $binding[] = $this->start_date;
        $binding[] = $this->end_date;
        $binding[] = $this->cpt;
        $binding[] = $this->init_units;
        $binding[] = $this->remaining_units;
        QueryUtils::sqlInsert($statement, $binding);
    }

    public static function getUnitsUsed($number): false|array|null
    {
        $statement = "SELECT count(prior_auth_number) AS count FROM `form_misc_billing_options` WHERE `prior_auth_number` = ?";
        $binds = [$number];
        return sqlQuery($statement, $binds);
    }

    public function setId($id): void
    {
        $this->id = $id;
    }
    /**
     * @return mixed
     */
    public function getPid(): ?int
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     */
    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return string
     */
    public function getAuthNum(): ?string
    {
        return $this->auth_num;
    }

    /**
     * @param string $auth_num
     */
    public function setAuthNum(string $auth_num): void
    {
        $this->auth_num = $auth_num;
    }

    /**
     * @return string
     */
    public function getStartDate(): ?string
    {
        return $this->start_date;
    }

    /**
     * @param $start_data
     */
    public function setStartDate($start_data): void
    {
        $this->start_date = $start_data;
    }

    /**
     * @return string
     */
    public function getEndDate(): ?string
    {
        return $this->end_date;
    }

    /**
     * @param string $end_date
     */
    public function setEndDate(string $end_date): void
    {
        $this->end_date = $end_date;
    }

    /**
     * @return string
     */
    public function getCpt(): ?string
    {
        return $this->cpt;
    }

    /**
     * @param string $cpt
     */
    public function setCpt(string $cpt): void
    {
        $this->cpt = $cpt;
    }

    /**
     * @return int|null
     */
    public function getInitUnits(): ?int
    {
        return $this->init_units;
    }

    /**
     * @param int $init_units
     */
    public function setInitUnits(int $init_units): void
    {
        $this->init_units = $init_units;
    }

    /**
     * @return int|null
     */
    public function getRemainingUnits(): ?int
    {
        return $this->remaining_units;
    }

    /**
     * @param int $remaining_units
     */
    public function setRemainingUnits(int $remaining_units): void
    {
        $this->remaining_units = $remaining_units;
    }

    public function listPatientAuths(): false|array
    {
        $sql = "SELECT DISTINCT pd.pid AS mrn, pd.fname, pd.lname, mpa.pid, mpa.auth_num, mpa.start_date, mpa.end_date, mpa.cpt, mpa.init_units, ins.provider " . "
            FROM `patient_data` pd " . "
            LEFT JOIN  `module_prior_authorizations` mpa ON pd.pid = mpa.pid " . "
            LEFT JOIN `insurance_data` ins ON  `ins`.`pid` = `pd`.`pid` " . "
            ORDER BY pd.lname";
        return sqlStatement($sql);
    }

    public static function registerFacility(): false|array
    {
        $sql = "SELECT * FROM `facility` WHERE id = 3";
        return sqlQuery($sql);
    }

    public static function registration($clinic): bool|string
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.affordablecustomehr.com/register.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array("name" => $clinic['name'],"phone" => $clinic['phone'],"email" => $clinic['email']),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public static function insuranceName($pid): false|array|null
    {
        return sqlQuery("SELECT ic.name  FROM `insurance_data` id
            JOIN insurance_companies ic ON id.provider = ic.id
            WHERE `pid` = ? AND type = 'primary'", [$pid]);
    }

    public static function countUsageOfAuthNumber($pid, $authnum): false|array|null
    {
        return sqlQuery("SELECT count(*) AS count FROM `form_misc_billing_options`
                         WHERE pid = ? AND `prior_auth_number` = ?", [$pid, $authnum]);
    }

    public static function requiresAuthorization($pid): false|array|null
    {
        $sql = "SELECT `d`.`field_value` FROM `lbt_data` d
JOIN `transactions` t ON `t`.`id` = `d`.`form_id` AND `t`.`title` = 'LBT_authorizations'
WHERE `t`.`pid` = ? AND `d`.`field_id` = 'authorization_001'";
        return sqlQuery($sql, [$pid]);
    }

    public static function patientInactive($pid): false|array|null
    {
        return sqlQuery("SELECT `ps`.`status` FROM `patient_status` ps WHERE `ps`.`pid` = ? ORDER BY `ps`.`statusId` DESC", [$pid]);
    }
}
