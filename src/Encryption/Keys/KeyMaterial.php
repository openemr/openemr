<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use SensitiveParameter;

/**
 * Wrapper that adds type safety and some amount of stacktrace protection to
 * raw key material.
 */
final readonly class KeyMaterial
{
    public function __construct(
        #[SensitiveParameter] public string $key,
    ) {
    }

    /**
     * @param int<1, max> $bytes
     */
    public static function generate(int $bytes): KeyMaterial
    {
        return new KeyMaterial(random_bytes($bytes));
    }

    public function __debugInfo(): array
    {
        return [
            'key' => '******',
        ];
    }
}
