<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use OpenEMR\Common\Crypto\{
    KeySource,
    KeyVersion,
};
use OpenEMR\Encryption\KeyId;

/**
 * Backwards-compatibility wrapper that translates the historic format=version
 * keys into the new Keychain-based names
 *
 * @deprecated
 */
enum Key: string
{
    case v1 = 'one';
    case v2 = 'two';  // v3 also uses this
    case v4Drive = 'four-drive';
    case v4Db = 'four-db';
    case v5Drive = 'five-drive';
    case v5Db = 'five-db';
    case v6Drive = 'six-drive';
    case v6Db = 'six-db';
    case v7Drive = 'seven-drive';
    case v7Db = 'seven-db';

    public static function fromCryptoGen(KeyVersion $format, KeySource $source): self
    {
        return match ($format) {
            KeyVersion::ONE => self::v1,
            KeyVersion::TWO, KeyVersion::THREE => self::v2,
            KeyVersion::FOUR => match ($source) {
                KeySource::Drive => self::v4Drive,
                KeySource::Database => self::v4Db,
            },
            KeyVersion::FIVE => match ($source) {
                KeySource::Drive => self::v5Drive,
                KeySource::Database => self::v5Db,
            },
            KeyVersion::SIX  => match ($source) {
                KeySource::Drive => self::v6Drive,
                KeySource::Database => self::v6Db,
            },
            KeyVersion::SEVEN => match ($source) {
                KeySource::Drive => self::v7Drive,
                KeySource::Database => self::v7Db,
            },
        };
    }

    public function getId(): KeyId
    {
        return new KeyId($this->value);
    }
}
