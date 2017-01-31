<?php

namespace Adldap;

use Adldap\Schemas\SchemaInterface;
use Adldap\Connections\ProviderInterface;
use Adldap\Connections\ConnectionInterface;

interface AdldapInterface
{
    /**
     * Add a provider by the specified name.
     *
     * @param ProviderInterface|array $configuration
     * @param string                  $name
     * @param ConnectionInterface     $connection
     * @param SchemaInterface         $schema
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addProvider($configuration = [], $name, ConnectionInterface $connection = null, SchemaInterface $schema = null);

    /**
     * Returns all of the connection providers.
     *
     * @return array
     */
    public function getProviders();

    /**
     * Retrieves a Provider using it's specified name.
     *
     * @param string $name
     *
     * @throws AdldapException
     *
     * @return ProviderInterface
     */
    public function getProvider($name);

    /**
     * Sets the default provider.
     *
     * @param string $name
     *
     * @throws AdldapException
     */
    public function setDefaultProvider($name);

    /**
     * Retrieves the first provider.
     *
     * @throws AdldapException
     *
     * @return ProviderInterface
     */
    public function getDefaultProvider();

    /**
     * Removes a provider by the specified name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function removeProvider($name);

    /**
     * Connects to the specified provider.
     *
     * If no username and password is given, then providers
     * configured admin credentials are used.
     *
     * @param string|null $name
     * @param string|null $username
     * @param string|null $password
     *
     * @return ProviderInterface
     */
    public function connect($name = null, $username = null, $password = null);

    /**
     * Call methods upon the default provider dynamically.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters);
}
