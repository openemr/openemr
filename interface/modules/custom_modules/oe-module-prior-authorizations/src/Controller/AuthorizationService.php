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

    public static function getUnitsUsed($authnum, $pid, $cpt, $start_date, $end_date): int
    {
        $statement = "SELECT SUM(b.units) AS count
                    FROM billing b
                    JOIN forms f
                        ON b.encounter = f.encounter
                    JOIN form_misc_billing_options fmbo
                        ON f.form_id = fmbo.id
                    JOIN form_encounter fe
                        ON f.encounter = fe.encounter
                    WHERE
                        f.form_name = 'Misc Billing Options'
                        AND fmbo.prior_auth_number = ?
                        AND fmbo.pid = ?
                        AND b.code = ?
                        AND fe.date BETWEEN ? AND ?";

        $binds = [$authnum, $pid, $cpt, $start_date, $end_date];
        $result = sqlQuery($statement, $binds);
        return (int) ($result['count'] ?? 0);
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

    public function listPatientAuths(): false|array|\ADORecordSet_mysqli
    {
        $sql = "SELECT DISTINCT pd.pid AS mrn, pd.fname, pd.lname, mpa.pid, mpa.auth_num, mpa.start_date, mpa.end_date, mpa.cpt, mpa.init_units, ins.provider " . "
            FROM `patient_data` pd " . "
            LEFT JOIN  `module_prior_authorizations` mpa ON pd.pid = mpa.pid " . "
            LEFT JOIN `insurance_data` ins ON  `ins`.`pid` = `pd`.`pid` " . "
            ORDER BY pd.lname";

        try {
            $result = sqlStatement($sql);
            return $result;
        } catch (\Throwable $e) {
            error_log("Database error in listPatientAuths: " . $e->getMessage());
            return false;
        }
    }

    public static function registerFacility(): false|array
    {
        $sql = "SELECT * FROM `facility` WHERE id = 3";
        return sqlQuery($sql);
    }

    public static function registration($clinic): bool|string
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.affordablecustomehr.com/register.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ["name" => $clinic['name'],"phone" => $clinic['phone'],"email" => $clinic['email']],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public static function insuranceName($pid): string
    {
        $insurance = sqlQuery("SELECT ic.name FROM `insurance_data` id
            JOIN insurance_companies ic ON id.provider = ic.id
            WHERE `pid` = ? AND type = 'primary'", [$pid]);

        if (is_array($insurance) && array_key_exists('name', $insurance)) {
            return (string) $insurance['name'];
        }

        return '';
    }

    public static function countUsageOfAuthNumber($authnum, $pid, $cpt, $start_date, $end_date): int
    {
        $result_array = sqlQuery(
            "SELECT COALESCE(SUM(b.units), 0) AS count
                             FROM billing b
                             JOIN forms f
                               ON b.encounter = f.encounter
                             JOIN form_misc_billing_options fmbo
                               ON f.form_id = fmbo.id
                             JOIN form_encounter fe
                               ON f.encounter = fe.encounter
                             WHERE
                               f.form_name = 'Misc Billing Options'
                               AND fmbo.prior_auth_number = ?
                               AND fmbo.pid = ?
                               AND b.code = ?
                               AND fe.date BETWEEN ? AND ?",
            [$authnum, $pid, $cpt, $start_date, $end_date]
        );

        if (is_array($result_array) && array_key_exists('count', $result_array)) {
            return (int) $result_array['count'];
        }

        return 0;
    }

    public static function requiresAuthorization($pid): false|array|null
    {
        $sql = "SELECT `d`.`field_value` FROM `lbt_data` d
JOIN `transactions` t ON `t`.`id` = `d`.`form_id` AND `t`.`title` = 'LBT_authorizations'
WHERE `t`.`pid` = ? AND `d`.`field_id` = 'authorization_001'";
        return sqlQuery($sql, [$pid]);
    }

    // This is a custom table patient_status table doesnt exist normally
    public static function patientInactive($pid): false|array|null
    {
        return sqlQuery("SELECT `ps`.`status` FROM `patient_status` ps WHERE `ps`.`pid` = ? ORDER BY `ps`.`statusId` DESC", [$pid]);
    }
}
