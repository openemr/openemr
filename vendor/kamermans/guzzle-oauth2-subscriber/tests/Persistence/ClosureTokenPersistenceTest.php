<?php

namespace kamermans\OAuth2\Tests\Persistence;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use kamermans\OAuth2\Persistence\ClosureTokenPersistence;
use kamermans\OAuth2\Token\RawToken;
use kamermans\OAuth2\Token\RawTokenFactory;

class ClosureTokenPersistenceTest extends TokenPersistenceTestBase
{

    private $cache = [];

    public function setUp()
    {
        $this->cache = [];
    }

    public function getInstance()
    {
        $cache = &$this->cache;
        $cache_key = "foo";

        $exists = function() use (&$cache, $cache_key) {
            return array_key_exists($cache_key, $cache);
        };

        $set = function(array $value) use (&$cache, $cache_key) {
            $cache[$cache_key] = $value;
        };

        $get = function() use (&$cache, $cache_key, $exists) {
            return $exists()? $cache[$cache_key]: null;
        };

        $delete = function() use (&$cache, $cache_key, $exists) {
            if ($exists()) {
                unset($cache[$cache_key]);
            }
        };

        return new ClosureTokenPersistence($set, $get, $delete, $exists);
    }
}
