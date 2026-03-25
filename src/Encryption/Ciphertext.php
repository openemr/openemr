<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption;

/**
 * DTO for handling ciphertext (data that has been encrypted)
 */
final readonly class Ciphertext
{
    public function __construct(public string $wrapped)
    {
    }
}
