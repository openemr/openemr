<?php

/**
 * Tests that unserialize() calls use allowed_classes to prevent object injection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Security;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../gacl/Cache_Lite/Lite.php';
require_once __DIR__ . '/../../../../portal/patient/fwk/libs/verysimple/Phreeze/ConnectionSetting.php';

// Legacy require_once + SMARTY_DIR setup for library/smarty_legacy/smarty/
// internals removed — the PostCalendar migration deleted that directory.
// The Cache_Lite tests below don't actually exercise those Smarty
// internal helpers; they were loaded reflexively because the test file
// originated in a Smarty-aware context.

/**
 * Cache_Lite subclass that declares the undeclared dynamic property
 * _memoryCachingState, avoiding PHP 8.2+ deprecation warnings in tests.
 */
class TestCacheLite extends \Cache_Lite
{
    /** @var array<string, mixed> */
    public array $_memoryCachingState = [];
}

class UnserializeAllowedClassesTest extends TestCase
{
    // ---- Cache_Lite: memory-cached automatic deserialization (line 256) ----

    public function testCacheLiteMemoryCacheReturnsArrayNotObject(): void
    {
        $cache = new \Cache_Lite([
            'caching' => true,
            'memoryCaching' => true,
            'onlyMemoryCaching' => true,
            'automaticSerialization' => true,
            'fileNameProtection' => true,
            'cacheDir' => sys_get_temp_dir() . '/',
        ]);

        $original = ['key' => 'value', 'nested' => [1, 2, 3]];
        $cache->save($original, 'test-id', 'test-group'); // @phpstan-ignore argument.type (PHPDoc says string, but automaticSerialization mode accepts mixed)

        $result = $cache->get('test-id', 'test-group');
        $this->assertIsArray($result); // @phpstan-ignore method.impossibleType (Cache_Lite PHPDoc says string return, but automaticSerialization mode returns unserialized mixed)
        $this->assertSame($original, $result); // @phpstan-ignore method.impossibleType
    }

    public function testCacheLiteMemoryCacheBlocksObjectInjection(): void
    {
        $cache = new \Cache_Lite([
            'caching' => true,
            'memoryCaching' => true,
            'onlyMemoryCaching' => true,
            'automaticSerialization' => true,
            'fileNameProtection' => true,
            'cacheDir' => sys_get_temp_dir() . '/',
        ]);

        // With allowed_classes => false, unserialize converts objects to
        // __PHP_Incomplete_Class instead of instantiating them.
        $obj = new \stdClass();
        $obj->injected = true;
        $cache->save($obj, 'inject-id', 'test-group'); // @phpstan-ignore argument.type

        $result = $cache->get('inject-id', 'test-group');
        $this->assertNotInstanceOf(\stdClass::class, $result); // @phpstan-ignore method.alreadyNarrowedType (Cache_Lite PHPDoc says string return, but automaticSerialization mode returns unserialized mixed)
        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $result); // @phpstan-ignore method.impossibleType
    }

    // ---- Cache_Lite: getMemoryCachingState (line 448) ----

    public function testCacheLiteGetMemoryCachingStateRoundTrip(): void
    {
        $cache = new TestCacheLite([
            'caching' => true,
            'memoryCaching' => true,
            'onlyMemoryCaching' => true,
            'automaticSerialization' => false,
            'fileNameProtection' => true,
            'cacheDir' => sys_get_temp_dir() . '/',
        ]);

        // saveMemoryCachingState serializes _memoryCachingCounter and
        // _memoryCachingState into a cache entry.
        $cache->_memoryCachingCounter = 5;
        $cache->_memoryCachingState = ['key1' => 'data1', 'key2' => 'data2'];

        $cache->saveMemoryCachingState('state-id', 'state-group');

        // getMemoryCachingState restores the counter and array from the
        // serialized entry. The counter was 5 when serialized (before
        // _memoryCacheAdd incremented it during save).
        $cache->getMemoryCachingState('state-id', 'state-group');

        $this->assertSame(5, $cache->_memoryCachingCounter);
        $this->assertSame(['key1' => 'data1', 'key2' => 'data2'], $cache->_memoryCachingArray);
    }

    public function testCacheLiteGetMemoryCachingStateBlocksObjectInjection(): void
    {
        $cache = new TestCacheLite([
            'caching' => true,
            'memoryCaching' => true,
            'onlyMemoryCaching' => true,
            'automaticSerialization' => false,
            'fileNameProtection' => true,
            'cacheDir' => sys_get_temp_dir() . '/',
        ]);

        $obj = new \stdClass();
        $obj->evil = true;

        $cache->_memoryCachingCounter = 1;
        $cache->_memoryCachingState = ['entry' => $obj];

        $cache->saveMemoryCachingState('inject-state', 'state-group');

        $cache->getMemoryCachingState('inject-state', 'state-group');

        $restored = $cache->_memoryCachingArray;
        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $restored['entry']);
    }

    // ---- ConnectionSetting: round-trip serialize/unserialize (line 99) ----

    public function testConnectionSettingRoundTrip(): void
    {
        $setting = new \ConnectionSetting();
        $setting->Type = 'mysql';
        $setting->Username = 'testuser';
        $setting->Password = 'testpass';
        $setting->ConnectionString = 'localhost:3306';
        $setting->DBName = 'testdb';
        $setting->TablePrefix = 'oe_';
        $setting->Charset = 'utf8mb4';

        $serialized = $setting->Serialize();

        $restored = new \ConnectionSetting();
        $restored->Unserialize($serialized);

        $this->assertSame('mysql', $restored->Type);
        $this->assertSame('testuser', $restored->Username);
        $this->assertSame('testpass', $restored->Password);
        $this->assertSame('localhost:3306', $restored->ConnectionString);
        $this->assertSame('testdb', $restored->DBName);
        $this->assertSame('oe_', $restored->TablePrefix);
        $this->assertSame('utf8mb4', $restored->Charset);
    }

    public function testConnectionSettingBlocksForeignClass(): void
    {
        // ConnectionSetting::Unserialize validates instanceof self before
        // copying properties, so a foreign class payload is silently rejected.
        $fake = new \stdClass();
        $fake->Type = 'evil';
        $payload = base64_encode(serialize($fake));

        $setting = new \ConnectionSetting();
        $setting->Unserialize($payload);

        $this->assertSame('mysql', $setting->Type);
    }

}
