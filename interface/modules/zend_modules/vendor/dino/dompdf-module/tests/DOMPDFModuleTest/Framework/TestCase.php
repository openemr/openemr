<?php

namespace DOMPDFModuleTest\Framework;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected static $serviceManager = null;
    
    public static function setServiceManager(ServiceManager $sm)
    {
        self::$serviceManager = $sm;
    }
    
    public function getServiceManager()
    {
    	return self::$serviceManager;
    }
}