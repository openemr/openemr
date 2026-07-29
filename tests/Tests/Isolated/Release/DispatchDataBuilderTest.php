<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\DispatchDataBuilder;
use OpenEMR\Release\DispatchRequest;
use OpenEMR\Release\OptionReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

final class DispatchDataBuilderTest extends TestCase
{
    public function testRelCutBuildsBranchVersionPrev(): void
    {
        $builder = new DispatchDataBuilder($this->reader([
            '--branch' => 'rel-820',
            '--release-version' => '8.2.0',
            '--prev-release' => '8.0.0',
        ]));
        self::assertSame(
            ['branch' => 'rel-820', 'version' => '8.2.0', 'prev_release' => '8.0.0'],
            $builder->build(DispatchRequest::EVENT_REL_CUT),
        );
    }

    public function testTagBuildsTagBranchVersionPrev(): void
    {
        $builder = new DispatchDataBuilder($this->reader([
            '--tag' => 'v8_2_0',
            '--branch' => 'rel-820',
            '--release-version' => '8.2.0',
            '--prev-release' => '8.0.0',
        ]));
        self::assertSame(
            [
                'tag' => 'v8_2_0',
                'branch' => 'rel-820',
                'version' => '8.2.0',
                'prev_release' => '8.0.0',
            ],
            $builder->build(DispatchRequest::EVENT_TAG),
        );
    }

    public function testReleaseTargetsChangedBuildsEmptyData(): void
    {
        $builder = new DispatchDataBuilder($this->reader([]));
        self::assertSame(
            [],
            $builder->build(DispatchRequest::EVENT_RELEASE_TARGETS_CHANGED),
        );
    }

    public function testUnknownEventThrows(): void
    {
        $builder = new DispatchDataBuilder($this->reader([]));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Unknown dispatch event: bogus/');
        $builder->build('bogus');
    }

    /**
     * @param array<string, string> $values
     */
    private function reader(array $values): OptionReader
    {
        $definition = new InputDefinition([
            new InputOption('branch', null, InputOption::VALUE_REQUIRED),
            new InputOption('release-version', null, InputOption::VALUE_REQUIRED),
            new InputOption('prev-release', null, InputOption::VALUE_REQUIRED),
            new InputOption('tag', null, InputOption::VALUE_REQUIRED),
        ]);
        $input = new ArrayInput($values, $definition);
        return new OptionReader($input);
    }
}
