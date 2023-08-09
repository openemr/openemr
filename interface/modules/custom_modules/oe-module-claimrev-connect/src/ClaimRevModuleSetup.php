<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    namespace OpenEMR\Modules\ClaimRevConnector;

    use OpenEMR\Modules\ClaimRevConnector\Bootstrap;

class ClaimRevModuleSetup
{
    public function __construct()
    {
    }

    public static function doesPartnerExists()
    {
        $x12Name = $GLOBALS['oe_claimrev_x12_partner_name'];
        $sql = "SELECT * FROM x12_partners WHERE name = ?";
        $sqlarr = array($x12Name);
        $result = sqlStatementNoLog($sql, $sqlarr);
        $rowCount = sqlNumRows($result);

        if ($rowCount > 0) {
            return true;
        }
        return false;
    }
    public static function couldSftpServiceCauseIssues()
    {
        $sftp = ClaimRevModuleSetup::getServiceRecord("X12_SFTP");
        if ($sftp != null) {
            if ($sftp["active"] == 1) {
                if ($sftp["require_once"] == "/library/billing_sftp_service.php") {
                    return true;
                }
            }
        }
        return false;
    }
    public static function deactivateSftpService()
    {
        $require_once = "/interface/modules/custom_modules/oe-module-claimrev-connect/src/SFTP_Mock_Service.php";
        ClaimRevModuleSetup::updateBackGroundServiceSetRequireOnce("X12_SFTP", $require_once);
    }
    public static function reactivateSftpService()
    {
        $require_once = "/library/billing_sftp_service.php";
        ClaimRevModuleSetup::updateBackGroundServiceSetRequireOnce("X12_SFTP", $require_once);
    }
    public static function updateBackGroundServiceSetRequireOnce($name, $requireOnce)
    {
        $sql = "UPDATE background_services SET require_once = ? WHERE name = ?";
        $sqlarr = array($requireOnce,$name);
        sqlStatement($sql, $sqlarr);
    }
    public static function getServiceRecord($name)
    {
        $sql = "SELECT * FROM background_services WHERE name = ? LIMIT 1";
        $sqlarr = array($name);
        $result = sqlStatement($sql, $sqlarr);
        if (sqlNumRows($result) == 1) {
            foreach ($result as $row) {
                return $row;
            }
        }
        return null;
    }
    public static function getBackgroundServices()
    {
        $sql = "SELECT * FROM background_services WHERE name like '%ClaimRev%' OR name = 'X12_SFTP'";
        $result = sqlStatement($sql);
        return $result;
    }
    public static function createBackGroundServices()
    {
        $sql = "DELETE FROM background_services WHERE name like '%ClaimRev%'";
        sqlStatement($sql);

        $sql = "INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
            ('ClaimRev_Send', 'Send Claims To ClaimRev', 1, 0, '2017-05-09 17:39:10', 1, 'start_X12_Claimrev_send_files', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php', 100);";
        sqlStatement($sql);

        $sql = "INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
            ('ClaimRev_Receive', 'Get Reports from ClaimRev', 1, 0, '2017-05-09 17:39:10', 240, 'start_X12_Claimrev_get_reports', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php', 100);";
        sqlStatement($sql);

        $sql = "INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
            ('ClaimRev_Elig_Send_Receive', 'Send and Receive Eligibility from ClaimRev', 1, 0, '2017-05-09 17:39:10', 1, 'start_send_eligibility', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Eligibility_ClaimRev_Service.php', 100);";
        sqlStatement($sql);
    }
}
