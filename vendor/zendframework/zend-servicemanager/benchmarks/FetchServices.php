<?php

namespace ZendBench\ServiceManager;

use Athletic\AthleticEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

class FetchServices extends AthleticEvent
{
    const NUM_SERVICES = 1000;

    /**
     * @var ServiceManager
     */
    protected $sm;

    protected function getConfig()
    {
        $config = [];
        for ($i = 0; $i <= self::NUM_SERVICES; $i++) {
            $config['factories']["factory_$i"]    = BenchAsset\FactoryFoo::class;
            $config['invokables']["invokable_$i"] = BenchAsset\Foo::class;
            $config['services']["service_$i"]     = $this;
            $config['aliases']["alias_$i"]        = "service_$i";
        }
        $config['abstract_factories'] = [ BenchAsset\AbstractFactoryFoo::class ];
        return $config;
    }

    public function classSetUp()
    {
        $this->sm = new ServiceManager(new Config($this->getConfig()));
    }

    /**
     * Fetch the factory services
     *
     * @iterations 5000
     */
    public function fetchFactoryService()
    {
        $result = $this->sm->get('factory_' . rand(0, self::NUM_SERVICES));
    }

    /**
     * Fetch the invokable services
     *
     * @iterations 5000
     */
    public function fetchInvokableService()
    {
        $result = $this->sm->get('invokable_' . rand(0, self::NUM_SERVICES));
    }

    /**
     * Fetch the services
     *
     * @iterations 5000
     */
    public function fetchService()
    {
        $result = $this->sm->get('service_' . rand(0, self::NUM_SERVICES));
    }

    /**
     * Fetch the alias services
     *
     * @iterations 5000
     */
    public function fetchAliasService()
    {
        $result = $this->sm->get('alias_' . rand(0, self::NUM_SERVICES));
    }

    /**
     * Fetch the abstract factory services
     *
     * @iterations 5000
     */
    public function fetchAbstractFactoryService()
    {
        $result = $this->sm->get('foo');
    }
}
