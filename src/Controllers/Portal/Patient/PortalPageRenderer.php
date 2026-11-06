<?php

/**
 * Renders a portal/patient page.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

interface PortalPageRenderer
{
    /**
     * @param string $page the page's name, such as `PatientListView`
     * @param array<string, mixed> $vars the page's variables
     */
    public function render(string $page, array $vars = []): string;
}
