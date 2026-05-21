<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Background\BackgroundServiceDefinition;
use OpenEMR\Services\Background\BackgroundServiceRegistry;

class ClaimRevModuleSetup
{
    public function __construct()
    {
    }

    public static function doesPartnerExists()
    {
        $x12Name = OEGlobalsBag::getInstance()->get('oe_claimrev_x12_partner_name');
        $sql = "SELECT * FROM x12_partners WHERE name = ?";
        $sqlarr = [$x12Name];
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
        $sqlarr = [$requireOnce,$name];
        sqlStatement($sql, $sqlarr);
    }
    public static function getServiceRecord($name)
    {
        $sql = "SELECT * FROM background_services WHERE name = ? LIMIT 1";
        $sqlarr = [$name];
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
    public static function createBackGroundServices(): void
    {
        // Use BackgroundServiceRegistry so module upgrades don't silently
        // reset an admin's enable/disable toggle. The Registry's "first
        // install wins" policy preserves the active flag on upsert;
        // reinstalling this module no longer wipes admin preferences the
        // way the previous DELETE-then-INSERT pattern did.
        $registry = new BackgroundServiceRegistry();
        $billingPath = '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php';
        $eligibilityPath = '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Eligibility_ClaimRev_Service.php';

        $registry->register(new BackgroundServiceDefinition(
            name: 'ClaimRev_Send',
            title: 'Send Claims To ClaimRev',
            function: 'start_X12_Claimrev_send_files',
            requireOnce: $billingPath,
            executeInterval: 1,
            sortOrder: 100,
            active: true,
        ));

        $registry->register(new BackgroundServiceDefinition(
            name: 'ClaimRev_Receive',
            title: 'Get Reports from ClaimRev',
            function: 'start_X12_Claimrev_get_reports',
            requireOnce: $billingPath,
            executeInterval: 240,
            sortOrder: 100,
            active: true,
        ));

        $registry->register(new BackgroundServiceDefinition(
            name: 'ClaimRev_Elig_Send_Receive',
            title: 'Send and Receive Eligibility from ClaimRev',
            function: 'start_send_eligibility',
            requireOnce: $eligibilityPath,
            executeInterval: 1,
            sortOrder: 100,
            active: true,
        ));
    }
}
