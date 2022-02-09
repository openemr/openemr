<?php

/**
 * Interface defining the requirements to use the SqlConfigEvent.
 *
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Events\Core\Interfaces;

/**
 * SqlConfigInterface
 */
interface SqlConfigInterface
{
    /**
     * Get the hostname of the database server
     *
     * @return string
     */
    public function getHost(): string;

    /**
     * Get the database port
     *
     * @return string
     */
    public function getPort(): string;

    /**
     * Get the username to connect to the database server
     *
     * @return string
     */
    public function getUser(): string;

    /**
     * Get the password of the user connecting to the database server
     *
     * @return string
     */
    public function getPass(): string;

    /**
     * Get the name of the database
     *
     * @return string
     */
    public function getDatabaseName(): string;

    /**
     * Get the encoding (Probably utf8mb4)
     *
     * @return string
     */
    public function getEncoding(): string;

    /**
     * Get the config status. 1 for configured, 0 for not configured
     *
     * @return integer
     */
    public function getConfig(): int;

    /**
     * Get whether or not to disable UTF-8 (Probably always 0)
     *
     * @return integer
     */
    public function getDisableUTF8(): int;
}
