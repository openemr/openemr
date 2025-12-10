<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures\Purger;

interface PurgerInterface
{
    /**
     * Purge data from database table
     */
    public function purge(): void;

    /**
     * Restore purged records back to database table
     */
    public function restore(): void;
}
