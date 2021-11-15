<?php

namespace kamermans\OAuth2\Tests\Persistence;

use kamermans\OAuth2\Persistence\NullTokenPersistence;
use kamermans\OAuth2\Token\RawToken;

class NullTokenPersistenceTest extends TokenPersistenceTestBase
{
    public function getInstance()
    {
        return new NullTokenPersistence();
    }

    public function testRestoreToken()
    {
        $this->testSaveToken();
        $this->assertNull($this->getInstance()->restoreToken(new RawToken));
    }

    public function testHasToken()
    {
        $this->assertFalse($this->getInstance()->hasToken());
    }

    public function testDeleteToken()
    {
        $this->testSaveToken();
        $this->getInstance()->deleteToken();
        $this->assertNull($this->getInstance()->restoreToken(new RawToken));
    }
}
