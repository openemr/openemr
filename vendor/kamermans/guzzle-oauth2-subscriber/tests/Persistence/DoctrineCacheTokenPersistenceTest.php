<?php

namespace kamermans\OAuth2\Tests\Persistence;

use kamermans\OAuth2\Persistence\DoctrineCacheTokenPersistence;
use kamermans\OAuth2\Token\RawToken;
use kamermans\OAuth2\Token\RawTokenFactory;
use Doctrine\Common\Cache\ArrayCache;

class DoctrineCacheTokenPersistenceTest extends TokenPersistenceTestBase
{
    protected $cache;

    public function getInstance()
    {
        return new DoctrineCacheTokenPersistence($this->cache);
    }

    public function setUp()
    {
        $this->cache = new ArrayCache();
    }

    public function testRestoreTokenCustomKey()
    {
        $doctrine = new DoctrineCacheTokenPersistence($this->cache, 'foo-bar');

        $factory = new RawTokenFactory();
        $token = $factory([
            'access_token' => 'abcdefghijklmnop',
            'refresh_token' => '0123456789abcdef',
            'expires_in' => 3600,
        ]);
        $doctrine->saveToken($token);

        $restoredToken = $doctrine->restoreToken(new RawToken);
        $this->assertInstanceOf('\kamermans\OAuth2\Token\RawToken', $restoredToken);

        $tokenBefore = $token->serialize();
        $tokenAfter = $restoredToken->serialize();

        $this->assertEquals($tokenBefore, $tokenAfter);
    }
}
