<?php

namespace Adldap\Auth;

use Adldap\Connections\ConnectionInterface;
use Adldap\Configuration\DomainConfiguration;

class Guard implements GuardInterface
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @var DomainConfiguration
     */
    protected $configuration;

    /**
     * {@inheritdoc}
     */
    public function __construct(ConnectionInterface $connection, DomainConfiguration $configuration)
    {
        $this->connection = $connection;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function attempt($username, $password, $bindAsUser = false)
    {
        $this->validateCredentials($username, $password);

        try {
            $this->bind($username, $password);
        } catch (BindException $e) {
            // We'll catch the BindException here to return false
            // to allow developers to use a simple if / else
            // using the authenticate method.
            return false;
        }

        // If we're not allowed to bind as the user,
        // we'll rebind as administrator.
        if ($bindAsUser === false) {
            // We won't catch any BindException here so
            // developers can catch rebind failures.
            $this->bindAsAdministrator();
        }

        // No bind exceptions, authentication passed.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function bind($username, $password, $prefix = null, $suffix = null)
    {
        // We'll allow binding with a null username and password
        // if their empty. This will allow us to anonymously
        // bind to our servers if needed.
        $username = $username ?: null;
        $password = $password ?: null;

        if ($username) {
            // If the username isn't empty, we'll append the configured
            // account prefix and suffix to bind to the LDAP server.
            $prefix = $prefix ?: $this->configuration->get('account_prefix');
            $suffix = $suffix ?: $this->configuration->get('account_suffix');

            $username = $prefix.$username.$suffix;
        }

        // We'll mute any exceptions / warnings here. All we need to know
        // is if binding failed and we'll throw our own exception.
        if (!@$this->connection->bind($username, $password)) {
            throw new BindException($this->connection->getLastError(), $this->connection->errNo());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bindAsAdministrator()
    {
        $this->bind(
            $this->configuration->get('admin_username'),
            $this->configuration->get('admin_password'),
            $this->configuration->get('admin_account_prefix'),
            $this->configuration->get('admin_account_suffix')
        );
    }

    /**
     * Validates the specified username and password from being empty.
     *
     * @param string $username
     * @param string $password
     *
     * @throws PasswordRequiredException
     * @throws UsernameRequiredException
     */
    protected function validateCredentials($username, $password)
    {
        if (empty($username)) {
            // Check for an empty username.
            throw new UsernameRequiredException('A username must be specified.');
        }

        if (empty($password)) {
            // Check for an empty password.
            throw new PasswordRequiredException('A password must be specified.');
        }
    }
}
