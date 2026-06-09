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

if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', __DIR__ . '/../../../../library/smarty_legacy/smarty/');
}

if (!defined('SMARTY_CORE_DIR')) {
    define('SMARTY_CORE_DIR', SMARTY_DIR . 'internals/');
}

require_once SMARTY_CORE_DIR . 'core.process_cached_inserts.php';
require_once SMARTY_CORE_DIR . 'core.read_cache_file.php';

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

    // ---- Smarty: core.process_cached_inserts (line 28) ----

    public function testSmartyProcessCachedInsertsDeserializesArgs(): void
    {
        $smarty = new \stdClass();
        $smarty->_smarty_md5 = 'f8d698aea36fcbead2b9d5359ffca76f';
        $smarty->debugging = false;
        $smarty->_plugins = [
            'insert' => [
                'testfunc' => [
                    static fn(array $args, object $smarty): string => 'REPLACED',
                ],
            ],
        ];

        $args = ['name' => 'testfunc'];
        $serialized = serialize($args);
        $results = $smarty->_smarty_md5
            . '{insert_cache ' . $serialized . '}'
            . $smarty->_smarty_md5;

        $output = smarty_core_process_cached_inserts(
            ['results' => $results],
            $smarty
        );

        $this->assertSame('REPLACED', $output);
    }

    public function testSmartyProcessCachedInsertsBlocksObjectInjection(): void
    {
        $smarty = new \stdClass();
        $smarty->_smarty_md5 = 'f8d698aea36fcbead2b9d5359ffca76f';
        $smarty->debugging = false;

        // The insert function checks whether the 'extra' key survived as an
        // object or was converted to __PHP_Incomplete_Class.
        $smarty->_plugins = [
            'insert' => [
                'testfunc' => [
                    static fn(array $args, object $smarty): string => ($args['extra'] instanceof \__PHP_Incomplete_Class)
                        ? 'BLOCKED'
                        : 'INJECTED',
                ],
            ],
        ];

        $args = ['name' => 'testfunc', 'extra' => new \stdClass()];
        $serialized = serialize($args);
        $results = $smarty->_smarty_md5
            . '{insert_cache ' . $serialized . '}'
            . $smarty->_smarty_md5;

        $output = smarty_core_process_cached_inserts(
            ['results' => $results],
            $smarty
        );

        $this->assertSame('BLOCKED', $output);
    }

    // ---- Smarty: core.read_cache_file (line 55) ----

    public function testSmartyReadCacheFileDeserializesCacheInfo(): void
    {
        $cacheInfo = [
            'expires' => -1,
            'timestamp' => time(),
            'template' => [],
        ];
        $serializedInfo = serialize($cacheInfo);
        $content = strlen($serializedInfo) . "\n" . $serializedInfo . '<html>cached</html>';

        $smarty = new \stdClass();
        $smarty->force_compile = false;
        $smarty->cache_handler_func = static function (
            string $action,
            object $smarty,
            ?string &$results,
            mixed ...$unused,
        ) use ($content): void {
            $results = $content;
        };
        $smarty->caching = 2;
        $smarty->compile_check = false;
        $smarty->_cache_info = [];

        $params = [
            'tpl_file' => 'read-cache-test.tpl',
            'cache_id' => 'c1',
            'compile_id' => 'co1',
            'results' => '',
        ];

        $result = smarty_core_read_cache_file($params, $smarty);

        $this->assertTrue($result);
        $this->assertSame($cacheInfo, $smarty->_cache_info); // @phpstan-ignore property.nonObject (by-reference parameter loses type)
        $this->assertSame('<html>cached</html>', $params['results']); // @phpstan-ignore offsetAccess.nonOffsetAccessible
    }

    public function testSmartyReadCacheFileBlocksObjectInjection(): void
    {
        $cacheInfo = [
            'expires' => -1,
            'timestamp' => time(),
            'template' => [],
            'evil' => new \stdClass(),
        ];
        $serializedInfo = serialize($cacheInfo);
        $content = strlen($serializedInfo) . "\n" . $serializedInfo . '<html>cached</html>';

        $smarty = new \stdClass();
        $smarty->force_compile = false;
        $smarty->cache_handler_func = static function (
            string $action,
            object $smarty,
            ?string &$results,
            mixed ...$unused,
        ) use ($content): void {
            $results = $content;
        };
        $smarty->caching = 2;
        $smarty->compile_check = false;
        $smarty->_cache_info = [];

        $params = [
            'tpl_file' => 'read-cache-inject.tpl',
            'cache_id' => 'c2',
            'compile_id' => 'co2',
            'results' => '',
        ];

        $result = smarty_core_read_cache_file($params, $smarty);

        $this->assertTrue($result);
        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $smarty->_cache_info['evil']); // @phpstan-ignore property.nonObject, offsetAccess.nonOffsetAccessible
    }
}
