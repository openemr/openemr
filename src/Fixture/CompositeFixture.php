<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Fixture;

class CompositeFixture implements FixtureInterface
{
    /**
     * @param array<FixtureInterface> $fixtures
     */
    public function __construct(
        private readonly array $fixtures,
    ) {
    }

    /**
     * @return array<FixtureInterface>
     */
    public function getFixtures(): array
    {
        return $this->fixtures;
    }

    public function load(): void
    {
        foreach ($this->fixtures as $fixture) {
            $fixture->load();
        }
    }
}
