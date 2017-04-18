<?php

namespace Adldap\Connections;

use InvalidArgumentException;
use Adldap\Auth\Guard;
use Adldap\Query\Builder;
use Adldap\Auth\GuardInterface;
use Adldap\Schemas\ActiveDirectory;
use Adldap\Schemas\SchemaInterface;
use Adldap\Models\Factory as ModelFactory;
use Adldap\Search\Factory as SearchFactory;
use Adldap\Configuration\DomainConfiguration;

class Provider implements ProviderInterface
{
    /**
     * The providers connection.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * The providers configuration.
     *
     * @var DomainConfiguration
     */
    protected $configuration;

    /**
     * The providers schema.
     *
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * The providers auth guard instance.
     *
     * @var GuardInterface
     */
    protected $guard;

    /**
     * {@inheritdoc}
     */
    public function __construct($configuration = [], ConnectionInterface $connection = null, SchemaInterface $schema = null)
    {
        $this->setConfiguration($configuration)
            ->setConnection($connection)
            ->setSchema($schema);
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        if (
            $this->connection instanceof ConnectionInterface &&
            $this->connection->isBound()
        ) {
            $this->connection->close();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration($configuration = [])
    {
        if (is_array($configuration)) {
            // Construct a configuration instance if an array is given.
            $configuration = new DomainConfiguration($configuration);
        } elseif (!$configuration instanceof DomainConfiguration) {
            $class = DomainConfiguration::class;

            throw new InvalidArgumentException("Configuration must be either an array or instance of $class");
        }

        $this->configuration = $configuration;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection(ConnectionInterface $connection = null)
    {
        // We'll create a standard connection if one isn't given.
        $this->connection = $connection ?: new Ldap();

        // Prepare the connection.
        $this->prepareConnection();

        // Instantiate the LDAP connection.
        $this->connection->connect(
            $this->configuration->get('domain_controllers'),
            $this->configuration->get('port')
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSchema(SchemaInterface $schema = null)
    {
        $this->schema = $schema ?: new ActiveDirectory();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setGuard(GuardInterface $guard)
    {
        $this->guard = $guard;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getGuard()
    {
        if (!$this->guard instanceof GuardInterface) {
            $this->setGuard($this->getDefaultGuard($this->connection, $this->configuration));
        }

        return $this->guard;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGuard(ConnectionInterface $connection, DomainConfiguration $configuration)
    {
        return new Guard($connection, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function make()
    {
        return $this->newModelFactory($this->search()->getQuery(), $this->schema);
    }

    /**
     * {@inheritdoc}
     */
    public function search()
    {
        return $this->newSearchFactory(
            $this->connection,
            $this->schema,
            $this->configuration->get('base_dn')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function auth()
    {
        return $this->getGuard();
    }

    /**
     * {@inheritdoc}
     */
    public function connect($username = null, $password = null)
    {
        // Get the default guard instance.
        $guard = $this->getGuard();

        if (is_null($username) && is_null($password)) {
            // If both the username and password are null, we'll connect to the server
            // using the configured administrator username and password.
            $guard->bindAsAdministrator();
        } else {
            // Bind to the server with the specified username and password otherwise.
            $guard->bind($username, $password);
        }

        return $this;
    }

    /**
     * Creates a new model factory.
     *
     * @param Builder         $builder
     * @param SchemaInterface $schema
     *
     * @return ModelFactory
     */
    protected function newModelFactory(Builder $builder, SchemaInterface $schema)
    {
        return new ModelFactory($builder, $schema);
    }

    /**
     * Creates a new search factory.
     *
     * @param ConnectionInterface $connection
     * @param SchemaInterface     $schema
     * @param string              $baseDn
     *
     * @return SearchFactory
     */
    protected function newSearchFactory(ConnectionInterface $connection, SchemaInterface $schema, $baseDn)
    {
        return new SearchFactory($connection, $schema, $baseDn);
    }

    /**
     * Prepares the connection by setting configured parameters.
     *
     * @return void
     */
    protected function prepareConnection()
    {
        if ($this->configuration->get('use_ssl')) {
            $this->connection->ssl();
        } elseif ($this->configuration->get('use_tls')) {
            $this->connection->tls();
        }

        $options = array_replace(
            $this->configuration->get('custom_options'),
            [
                LDAP_OPT_PROTOCOL_VERSION => $this->configuration->get('version'),
                LDAP_OPT_NETWORK_TIMEOUT => $this->configuration->get('timeout'),
                LDAP_OPT_REFERRALS => $this->configuration->get('follow_referrals')
            ]
        );

        $this->connection->setOptions($options);
    }
}
