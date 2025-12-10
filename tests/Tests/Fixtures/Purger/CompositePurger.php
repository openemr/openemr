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

/**
 * Remove and restore records from/to multiple
 * tables by using multiple child purgers
 */
class CompositePurger implements PurgerInterface
{
    /**
     * @param array<PurgerInterface> $purgers
     */
    public function __construct(
        private readonly array $purgers,
    ) {
    }

    public function purge(): void
    {
        foreach ($this->purgers as $purger) {
            $purger->purge();
        }
    }

    public function restore(): void
    {
        foreach ($this->purgers as $purger) {
            $purger->restore();
        }
    }
}
