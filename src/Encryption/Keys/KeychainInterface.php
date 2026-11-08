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

use OpenEMR\Encryption\Cipher\CipherInterface;
use OpenEMR\Encryption\KeyId;

/**
 * Implementations of KeychainInterface are responsible for key management and
 * cipher selection. How keys are stored and loaded are implementation-specific:
 * these could be lazy-loaded, fetched from a remote service, or handled in
 * other ways.
 */
interface KeychainInterface
{
    public function getCipher(KeyId $keyId): CipherInterface;

    public function getCurrentKeyId(): KeyId;

    public function hasKey(KeyId $keyId): bool;
}
