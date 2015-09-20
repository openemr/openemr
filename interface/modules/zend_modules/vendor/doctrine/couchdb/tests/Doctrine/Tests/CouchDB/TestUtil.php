<?php

namespace Doctrine\Tests\CouchDB;

class TestUtil
{
    static public function getTestDatabase()
    {
        if (isset($GLOBALS['DOCTRINE_COUCHDB_DATABASE'])) {
            return $GLOBALS['DOCTRINE_COUCHDB_DATABASE'];
        }
        return 'doctrine_test_database';
    }
}
