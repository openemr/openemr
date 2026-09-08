<?php

/**
 * ParsedExtension - a schematron `<sch:extends rule="X"/>` in structured form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

final readonly class ParsedExtension
{
    public function __construct(public string $rule)
    {
    }
}
