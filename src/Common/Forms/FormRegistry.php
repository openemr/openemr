<?php

/**
 * Whether a form is registered and enabled in the site's form registry (Administration > Forms), for pages that
 * load a form's card or fragment only when the form is in use.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms;

use OpenEMR\Common\Database\QueryUtils;

final readonly class FormRegistry
{
    /** @var \Closure(string): int */
    private \Closure $countEnabled;

    /**
     * @param ?\Closure(string): int $countEnabled how many enabled registry rows a form directory has; the registry
     *                                              table when left out
     */
    public function __construct(?\Closure $countEnabled = null)
    {
        $this->countEnabled = $countEnabled ?? static function (string $directory): int {
            $count = QueryUtils::fetchSingleValue(
                'SELECT COUNT(*) AS count FROM registry WHERE directory = ? AND state = 1',
                'count',
                [$directory]
            );
            return is_numeric($count) ? (int) $count : 0;
        };
    }

    /** Whether the form in this directory (for example 'vitals' or 'track_anything') is registered and enabled. */
    public function isEnabled(string $directory): bool
    {
        return ($this->countEnabled)($directory) > 0;
    }
}
