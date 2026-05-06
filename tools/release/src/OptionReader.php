<?php

/**
 * Narrow Symfony Console option values from `mixed` to typed primitives,
 * with explicit null/empty handling. Lets the CLI scripts stay free of
 * PHPStan-suppressed casts.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Console\Input\InputInterface;

final readonly class OptionReader
{
    public function __construct(
        private InputInterface $input,
    ) {
    }

    public function string(string $name, string $default = ''): string
    {
        $raw = $this->input->getOption($name);
        return is_string($raw) ? $raw : $default;
    }

    /**
     * @return list<string>
     */
    public function commaList(string $name, string $default = ''): array
    {
        $raw = $this->string($name, $default);
        if ($raw === '') {
            return [];
        }
        $parts = array_map(trim(...), explode(',', $raw));
        return array_values(array_filter($parts, static fn(string $part): bool => $part !== ''));
    }

    public function bool(string $name): bool
    {
        $raw = $this->input->getOption($name);
        return $raw === true;
    }
}
