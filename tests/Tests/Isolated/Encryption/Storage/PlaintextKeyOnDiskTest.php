<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Storage;

use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Encryption\Storage\PlaintextKeyOnDisk;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Marked as deprecated to match SUT
 */
final class PlaintextKeyOnDiskTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/openemr_key_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        // Clean up any files created during tests
        $files = glob($this->tempDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    public function testStoreAndRetrieveKey(): void
    {
        $storage = new PlaintextKeyOnDisk($this->tempDir);
        $keyMaterial = new KeyMaterial('test_secret_key_________________');

        $storage->storeKey('test-key', $keyMaterial);
        $retrieved = $storage->getKey('test-key');

        self::assertSame($keyMaterial->key, $retrieved->key);
    }

    public function testGetKeyThrowsWhenNotFound(): void
    {
        $storage = new PlaintextKeyOnDisk($this->tempDir);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Key not found');

        $storage->getKey('nonexistent-key');
    }

    public function testStoreKeyThrowsWhenKeyExists(): void
    {
        $storage = new PlaintextKeyOnDisk($this->tempDir);
        $keyMaterial = new KeyMaterial('test_secret_key_________________');

        $storage->storeKey('duplicate-key', $keyMaterial);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Key exists, will not overwrite');

        $storage->storeKey('duplicate-key', $keyMaterial);
    }

    public function testGetKeyReadsBase64EncodedFile(): void
    {
        $rawKey = 'externally_stored_key___________';
        file_put_contents($this->tempDir . '/external-key', base64_encode($rawKey));

        $storage = new PlaintextKeyOnDisk($this->tempDir);
        $retrieved = $storage->getKey('external-key');

        self::assertSame($rawKey, $retrieved->key);
    }
}
