<?php

/**
 * Shared test helpers for cipher unit tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Cipher;

use OpenEMR\Encryption\Ciphertext;
use OpenEMR\Tests\Fixtures\CryptoFixtureManager;

trait CipherTestHelperTrait
{
    private CryptoFixtureManager $fixtures;

    protected function setUp(): void
    {
        // No install() needed - we only use the static test vectors
        $this->fixtures = new CryptoFixtureManager('/dev/null');
    }

    /**
     * Strip version prefix and base64 decode to get raw ciphertext.
     */
    private function extractRawCiphertext(string $encoded): Ciphertext
    {
        $raw = base64_decode(substr($encoded, 3), strict: true);
        self::assertIsString($raw, 'Test vector base64 decode failed');
        return new Ciphertext($raw);
    }
}
