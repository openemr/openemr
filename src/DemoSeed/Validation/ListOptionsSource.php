<?php

/**
 * Where the active option ids of an OpenEMR list come from.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Validation;

interface ListOptionsSource
{
    /**
     * @return list<string>
     */
    public function optionIds(string $listId): array;
}
