<?php

/**
 * PatientValidationService: whether the Patientvalidation module is registered and active in the
 * modules table (moved here from library/patientvalidation.inc.php).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dror Golan <drorgo@matrix.co.il>
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2016 Matrix Israel
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;

class PatientValidationService extends BaseService
{
    public const TABLE_NAME = 'modules';

    /**
     * Binds the service to the modules table; the lookup itself is static.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * Whether at least one Patientvalidation row in the modules table is active.
     *
     * Moved from library/patientvalidation.inc.php (checkIfPatientValidationHookIsActive).
     */
    public static function checkIfPatientValidationHookIsActive(): bool
    {
        $row = QueryUtils::querySingleRow(
            "SELECT 1 FROM `modules` WHERE `mod_name` = 'Patientvalidation' AND `mod_active` = 1 LIMIT 1"
        );
        return is_array($row);
    }
}
