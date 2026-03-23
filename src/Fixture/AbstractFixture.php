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

use Webmozart\Assert\Assert;

/**
 * @template TRecord of array
 */
abstract class AbstractFixture implements FixtureInterface
{
    protected array $records = [];

    public function __construct(
        private readonly array $filenames,
    ) {
    }

    protected function reset(): void
    {
        $this->records = [];
    }

    public function load(): void
    {
        $this->reset();

        foreach ($this->filenames as $filename) {
            $this->loadFromFile($filename);
        }
    }

    protected function loadFromFile(string $filename): void
    {
        $filepath = sprintf(
            '%s/%s',
            $this->getDataDir(),
            $filename,
        );

        $records = array_merge($this->records, json_decode(
            file_get_contents($filepath),
            true,
            512,
            \JSON_THROW_ON_ERROR,
        ));

        foreach ($records as $record) {
            $record = $this->loadRecord($record);

            if ($this instanceof RemovableFixtureInterface) {
                Assert::keyExists($record, 'id', 'Expected to have ID at record');

                $this->records[$record['id']] = $record;
            } else {
                $this->records[] = $record;
            }
        }
    }

    abstract protected function loadRecord(array $record): array;

    protected function getDataDir(): string
    {
        return sprintf('%s/data', __DIR__);
    }
}
