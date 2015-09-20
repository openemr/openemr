<?php

namespace Doctrine\Tests\CouchDB;

use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\HTTP\SocketClient;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\AnnotationReader;

abstract class CouchDBFunctionalTestCase extends \PHPUnit_Framework_TestCase
{
    private $httpClient = null;

    /**
     * @return \Doctrine\CouchDB\HTTP\Client
     */
    public function getHttpClient()
    {
        if ($this->httpClient === null) {
            if (isset($GLOBALS['DOCTRINE_COUCHDB_CLIENT'])) {
                $this->httpClient = new $GLOBALS['DOCTRINE_COUCHDB_CLIENT'];
            } else {
                $this->httpClient = new SocketClient();
            }
        }
        return $this->httpClient;
    }

    public function getTestDatabase()
    {
        return TestUtil::getTestDatabase();
    }

    public function createCouchDBClient()
    {
        return new CouchDBClient($this->getHttpClient(), $this->getTestDatabase());
    }
}
