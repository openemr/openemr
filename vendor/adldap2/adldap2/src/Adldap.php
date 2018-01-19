<?php

namespace Adldap;

use InvalidArgumentException;
use Adldap\Connections\Provider;
use Adldap\Schemas\SchemaInterface;
use Adldap\Connections\ProviderInterface;
use Adldap\Connections\ConnectionInterface;
use Adldap\Configuration\DomainConfiguration;

class Adldap implements AdldapInterface
{
    /**
     * The default provider name.
     *
     * @var string
     */
    protected $default = 'default';

    /**
     * The connection providers.
     *
     * @var array
     */
    protected $providers = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $providers = [])
    {
        foreach ($providers as $name => $configuration) {
            $this->addProvider($configuration, $name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addProvider($configuration = [], $name = 'default', ConnectionInterface $connection = null, SchemaInterface $schema = null)
    {
        if (is_array($configuration) || $configuration instanceof DomainConfiguration) {
            $configuration = new Provider($configuration, $connection, $schema);
        }

        if ($configuration instanceof ProviderInterface) {
            $this->providers[$name] = $configuration;

            return $this;
        }

        throw new InvalidArgumentException(
            "You must provide a configuration array or an instance of Adldap\Connections\ProviderInterface."
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider($name)
    {
        if (array_key_exists($name, $this->providers)) {
            return $this->providers[$name];
        }

        throw new AdldapException("The connection provider '$name' does not exist.");
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultProvider($name = 'default')
    {
        if ($this->getProvider($name) instanceof ProviderInterface) {
            $this->default = $name;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultProvider()
    {
        return $this->getProvider($this->default);
    }

    /**
     * {@inheritdoc}
     */
    public function removeProvider($name)
    {
        unset($this->providers[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function connect($name = null, $username = null, $password = null)
    {
        $provider = $name ? $this->getProvider($name) : $this->getDefaultProvider();

        return $provider->connect($username, $password);
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $parameters)
    {
        $provider = $this->getDefaultProvider();

        if (!$provider->getConnection()->isBound()) {
            // We'll make sure we have a bound connection before
            // allowing dynamic calls on the provider.
            $provider->connect();
        }

        return call_user_func_array([$provider, $method], $parameters);
    }
}
