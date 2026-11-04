<?php

/**
 * Where a statement batch goes once a run has written it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

interface StatementOutput
{
    /**
     * Send the batch file to the browser as a download. The request ends there.
     */
    public function download(string $batchFile): never;

    /**
     * Send the batch to the browser as a PDF download. The request ends there.
     *
     * @param ?array{firstName: string, pid: int} $onlyPatient names the file after the patient when the run was one patient's
     */
    public function downloadPdf(string $batchFile, ?array $onlyPatient): never;

    /**
     * Send the batch file to the printer. A failure's details are logged, not returned:
     * they can hold server paths and printer errors the biller shouldn't see.
     *
     * @return bool whether the print command succeeded
     */
    public function print(string $batchFile): bool;
}
