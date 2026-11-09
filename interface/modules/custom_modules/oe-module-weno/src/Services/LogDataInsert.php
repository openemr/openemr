<?php

/**
 *  @package OpenEMR
 *  @link    https://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @author  Kofi Appiah <kkappiah@medsov.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2023 Omega Systems Group International <info@omegasystemsgroup.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Rx\Weno\WenoPrescriptionLog;

class LogDataInsert
{
    private readonly WenoPrescriptionLog $prescriptionLog;

    public function __construct(?WenoPrescriptionLog $prescriptionLog = null)
    {
        $this->prescriptionLog = $prescriptionLog ?? new WenoPrescriptionLog();
    }

    /**
     * @param array<string, mixed> $insertdata
     * @return true|string true when the row was written, otherwise the database error
     */
    public function insertPrescriptions(array $insertdata): bool|string
    {
        return $this->prescriptionLog->insert($insertdata);
    }

    /**
     * @param array<string, mixed> $updatedata
     * @return true|string true when the statement ran, otherwise the database error
     */
    public function updatePrescriptions(array $updatedata): bool|string
    {
        return $this->prescriptionLog->update($updatedata);
    }
}
