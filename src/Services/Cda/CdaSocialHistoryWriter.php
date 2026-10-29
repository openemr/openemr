<?php

/**
 * Writes what a C-CDA import carries of a patient's social history as a new version of their history.
 *
 * history_data keeps a row per change, and the latest row is the patient's current history. The import used to
 * insert a row of just pid, alcohol and tobacco, so everything else the patient's history held (alcohol when the
 * document carried only smoking, family history, risk factors) read as empty afterwards. This writes the latest
 * row with only the fields the document carries changed, or a first row when the patient has none.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda;

use OpenEMR\Services\SocialHistoryService;

final class CdaSocialHistoryWriter
{
    /**
     * @param mixed                 $pid     the patient's pid, as the importer and the history service take it
     * @param array<string, string> $changes history_data column => value, only what the document carries
     */
    public static function write(SocialHistoryService $service, mixed $pid, array $changes): void
    {
        if ($changes === []) {
            return;
        }
        $current = $service->getHistoryData($pid);
        if (!is_array($current) || $current === []) {
            $service->create(['pid' => $pid] + $changes);
            return;
        }
        $service->updateHistoryDataForPatientPid($pid, $changes);
    }
}
