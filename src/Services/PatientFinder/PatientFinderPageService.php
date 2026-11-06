<?php

/**
 * PatientFinderPageService
 *
 * Maps the shared column registry into the plain column data the Patient Finder
 * page (dynamic_finder.php) needs: each column's key, (already translated)
 * title, initial sort direction, and whether it renders as a link (the Name
 * column). Because it derives from the same registry the server-side endpoint
 * uses, module-contributed columns are included automatically.
 *
 * This is deliberately data-only: the HTML (column headers, per-column search
 * inputs) is rendered by the finder Twig template, and the DataTables column
 * configuration is serialised to JSON at the page boundary — no markup is built
 * here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

final class PatientFinderPageService
{
    /**
     * Build the finder's column data from the registry, in display order.
     *
     * @return array<int, array{key: string, title: string, sortDir: string, link: bool}>
     *         key     - the column key / DataTables sName;
     *         title   - the (translated) header label;
     *         sortDir - the initial sort direction ("asc"/"desc");
     *         link    - whether the cell renders as a link (the Name column).
     */
    public function getColumns(PatientFinderColumnRegistry $registry): array
    {
        $columns = [];
        foreach ($registry->all() as $column) {
            $columns[] = [
                'key' => $column->key,
                'title' => $column->title,
                'sortDir' => $column->defaultSortDir,
                'link' => $column->key === PatientFinderQueryBuilder::NAME_KEY,
            ];
        }
        return $columns;
    }
}
