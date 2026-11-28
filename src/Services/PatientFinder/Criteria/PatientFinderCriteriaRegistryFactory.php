<?php

/**
 * Builds the Patient Finder's criteria for drawing the page, with the sex list's active entries.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientFinder\PatientFinderValue;

final class PatientFinderCriteriaRegistryFactory
{
    public function create(): PatientFinderCriteriaRegistry
    {
        $sexOptions = [];
        $rows = QueryUtils::fetchRecords(
            "SELECT option_id, title FROM list_options WHERE list_id = 'sex' AND activity = 1 ORDER BY seq, title"
        );
        foreach ($rows as $row) {
            $sexOptions[] = [
                'id' => PatientFinderValue::string($row['option_id'] ?? null),
                'title' => xl_list_label(PatientFinderValue::string($row['title'] ?? null)),
            ];
        }
        return PatientFinderCriteriaRegistry::standard($sexOptions);
    }
}
