<?php

namespace kamermans\OAuth2\Tests\Persistence;

use kamermans\OAuth2\Persistence\SimpleCacheTokenPersistence;
use kamermans\OAuth2\Token\RawToken;
use kamermans\OAuth2\Token\RawTokenFactory;
use Symfony\Component\Cache\Simple\ArrayCache;

class SimpleCacheTokenPersistenceTest extends TokenPersistenceTestBase
{
    protected $cache;

    public function getInstance()
    {
        return new SimpleCacheTokenPersistence($this->cache);
    }

    public function setUp()
    {
        $this->cache = new ArrayCache();
    }

    public function testRestoreTokenCustomKey()
    {
        $simpleCache = new SimpleCacheTokenPersistence($this->cache, 'foo-bar');

        $factory = new RawTokenFactory();
        $token = $factory([
            'access_token' => 'abcdefghijklmnop',
            'refresh_token' => '0123456789abcdef',
            'expires_in' => 3600,
        ]);
        $simpleCache->saveToken($token);

        $restoredToken = $simpleCache->restoreToken(new RawToken);
        $this->assertInstanceOf('\kamermans\OAuth2\Token\RawToken', $restoredToken);

        $tokenBefore = $token->serialize();
        $tokenAfter = $restoredToken->serialize();

        $this->assertEquals($tokenBefore, $tokenAfter);
    }
}
