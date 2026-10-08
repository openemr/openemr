<?php

/**
 * Handling for encrypted globals whose stored value cannot be decrypted.
 *
 * The Config editor is the only place an administrator can replace an
 * encrypted global. When the stored value cannot be decrypted (for example
 * after the site key files change), the editor must still render, and saving
 * the page must not overwrite the stored value with an empty one unless the
 * administrator enters a replacement.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Globals;

use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\CryptoInterface;

final class EncryptedGlobalValue
{
    /**
     * Prefix of the hidden form field that marks an undecryptable value.
     */
    public const UNDECRYPTABLE_FIELD_PREFIX = 'undecryptable_';

    private const ENCRYPTED_TYPES = [
        GlobalSetting::DATA_TYPE_ENCRYPTED,
        GlobalSetting::DATA_TYPE_ENCRYPTED_HASH,
    ];

    /**
     * Decrypt a stored value for display in the editor.
     *
     * @return string|null The plaintext, or null when the value cannot be decrypted.
     */
    public static function decryptForEdit(CryptoInterface $crypto, mixed $stored): ?string
    {
        try {
            return $crypto->decryptFromDatabase(is_string($stored) ? $stored : null);
        } catch (CryptoGenException) {
            return null;
        }
    }

    /**
     * Name of the hidden form field that marks field $index as undecryptable.
     */
    public static function undecryptableFieldName(int $index): string
    {
        return self::UNDECRYPTABLE_FIELD_PREFIX . $index;
    }

    /**
     * Whether a save should leave the stored value untouched.
     *
     * True only for an encrypted field that the editor marked undecryptable
     * and that is still empty. A value entered in that field is saved as
     * usual, and other fields can still be cleared on purpose.
     */
    public static function keepStoredValue(mixed $fieldType, string $submittedValue, string $undecryptableFlag): bool
    {
        return $submittedValue === ''
            && in_array($fieldType, self::ENCRYPTED_TYPES, true)
            && $undecryptableFlag === '1';
    }
}
