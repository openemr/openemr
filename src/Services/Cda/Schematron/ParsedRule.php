<?php

/**
 * ParsedRule - a schematron `<sch:rule>` in structured form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

final readonly class ParsedRule
{
    /**
     * @param list<ParsedAssertion|ParsedExtension> $items assertions and extends, in document order
     */
    public function __construct(
        public bool $abstract,
        public ?string $context,
        public array $items,
    ) {
    }
}
