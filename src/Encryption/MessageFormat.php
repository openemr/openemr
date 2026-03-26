<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption;

use UnexpectedValueException;

enum MessageFormat
{
    // Existing format as of March 2026: the "format" id is also a key id
    case Legacy;
    // Future: v8 will allow for actual key versioning without additional code
    // changes. It will get different handling in Message.

    const LATEST = self::Legacy;

    // This is effectively BackedEnum's `::from`, rejiggered in
    // a backwards-compatible way
    public static function detect(string $message): MessageFormat
    {
        if (strlen($message) < 3) {
            throw new UnexpectedValueException('Message is missing expected prefix');
        }
        $prefix = substr($message, 0, 3);
        return match ($prefix) {
            '001', '002', '003', '004', '005', '006', '007' => self::Legacy,
            // 008: modern
            default => throw new UnexpectedValueException(''),
            // default: plaintext?
        };
    }
}
