<?php

namespace kamermans\OAuth2\Tests\Persistence;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use kamermans\OAuth2\Persistence\Laravel5CacheTokenPersistence;
use kamermans\OAuth2\Token\RawToken;
use kamermans\OAuth2\Token\RawTokenFactory;

class Laravel5CacheTokenPersistenceTest extends TokenPersistenceTestBase
{
    protected $cache;

    public function getInstance()
    {
        return new Laravel5CacheTokenPersistence($this->cache);
    }

    public function setUp()
    {
        $this->cache = new Repository(new ArrayStore());
    }

    public function testRestoreTokenCustomKey()
    {
        $simpleCache = new Laravel5CacheTokenPersistence($this->cache, 'foo-bar');

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
