<?php

/**
 * CryptoGenException - Exception class for cryptographic operations.
 *
 * This exception is thrown when critical cryptographic errors occur,
 * such as missing OpenSSL extension, key generation failures, or
 * encryption/decryption errors that require the script to exit.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

/**
 * Exception class for critical cryptographic errors.
 *
 * Used to indicate fatal errors in encryption/decryption operations
 * that should cause the script to terminate.
 */
class CryptoGenException extends \Exception
{
}
