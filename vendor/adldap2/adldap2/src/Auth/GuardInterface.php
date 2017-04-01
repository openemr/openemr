<?php

namespace Adldap\Auth;

use Adldap\Connections\ConnectionInterface;
use Adldap\Configuration\DomainConfiguration;

interface GuardInterface
{
    /**
     * Constructor.
     *
     * @param ConnectionInterface $connection
     * @param DomainConfiguration $configuration
     */
    public function __construct(ConnectionInterface $connection, DomainConfiguration $configuration);

    /**
     * Authenticates a user using the specified credentials.
     *
     * @param string $username   The users AD username.
     * @param string $password   The users AD password.
     * @param bool   $bindAsUser Whether or not to bind as the user.
     *
     * @throws \Adldap\Auth\BindException
     * @throws \Adldap\Auth\UsernameRequiredException
     * @throws \Adldap\Auth\PasswordRequiredException
     *
     * @return bool
     */
    public function attempt($username, $password, $bindAsUser = false);

    /**
     * Binds to the current connection using the
     * inserted credentials.
     *
     * @param string $username
     * @param string $password
     * @param string $prefix
     * @param string $suffix
     *
     * @returns void
     *
     * @throws \Adldap\Auth\BindException
     */
    public function bind($username, $password, $prefix = null, $suffix = null);

    /**
     * Binds to the current LDAP server using the
     * configuration administrator credentials.
     *
     * @throws \Adldap\Auth\BindException
     */
    public function bindAsAdministrator();
}
