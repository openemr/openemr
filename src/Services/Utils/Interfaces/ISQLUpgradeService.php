<?php

/**
 * Interface for SQL Upgrade Service
 *
 * This interface defines the contract for services that handle SQL upgrades
 * within the OpenEMR system. Implementations should provide methods for
 * analyzing, executing, and tracking database schema upgrades.
 *
 * @package    OpenEMR
 * @link       http://www.open-emr.org
 * @subpackage Services
 * @author     Michael A. Smith <michael@opencoreemr.com>
 * @copyright  Copyright (c) 2025 OpenCoreEMR Inc.
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * This interface was generated with the assistance of GitHub Copilot and Claude 3.7 Sonnet
 */

namespace OpenEMR\Services\Utils\Interfaces;

interface ISQLUpgradeService
{
    /**
     * @return bool
     */
    public function isRenderOutputToScreen(): bool;

    /**
     * @param bool $renderOutputToScreen
     * @return $this
     */
    public function setRenderOutputToScreen(bool $renderOutputToScreen);

    /**
     * Get the buffer of output messages when rendering to screen is disabled
     *
     * @return array
     */
    public function getRenderOutputBuffer(): array;

    /**
     * @return bool
     */
    public function isThrowExceptionOnError(): bool;

    /**
     * @param bool $throwExceptionOnError
     * @return $this
     */
    public function setThrowExceptionOnError(bool $throwExceptionOnError);

    /**
     * Upgrade or patch the database with a selected upgrade/patch file
     *
     * @param string $filename Sql upgrade/patch filename
     * @param string $path Path to the SQL file directory
     */
    public function upgradeFromSqlFile($filename, $path = '');

    /**
     * Output string to the screen with flushing to ensure immediate display
     *
     * @param string $string The string to output
     */
    public function flush_echo($string = '');
}
