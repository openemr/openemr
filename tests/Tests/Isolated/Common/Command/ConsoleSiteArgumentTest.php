<?php

/**
 * Isolated tests for reading --site from bin/console's arguments.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use OpenEMR\Common\Command\ConsoleSiteArgument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

#[Group('isolated')]
class ConsoleSiteArgumentTest extends TestCase
{
    /**
     * @param list<string> $argv
     */
    #[DataProvider('argvProvider')]
    public function testFromArgv(array $argv, string $expected): void
    {
        $this->assertSame($expected, ConsoleSiteArgument::fromArgv($argv));
    }

    /**
     * bin/console bootstraps the site this returns, and the command then
     * reads its own --site through Symfony: the two must agree.
     *
     * @param list<string> $argv
     */
    #[DataProvider('symfonyAgreementProvider')]
    public function testAgreesWithSymfonysParsing(array $argv): void
    {
        $definition = new InputDefinition([
            new InputArgument('command'),
            new InputOption('site', null, InputOption::VALUE_REQUIRED, '', 'default'),
        ]);
        $symfony = (new ArgvInput($argv, $definition))->getOption('site');

        $this->assertSame($symfony, ConsoleSiteArgument::fromArgv($argv));
    }

    /**
     * @return array<string, array{list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function symfonyAgreementProvider(): array
    {
        return [
            'none' => [['bin/console', 'x']],
            'equals form' => [['bin/console', 'x', '--site=clinic2']],
            'space form' => [['bin/console', 'x', '--site', 'clinic2']],
            'repeated' => [['bin/console', 'x', '--site=clinic2', '--site=clinic3']],
            'repeated, space form' => [['bin/console', 'x', '--site', 'clinic2', '--site', 'clinic3']],
            'repeated, mixed forms' => [['bin/console', 'x', '--site=clinic2', '--site', 'clinic3']],
        ];
    }

    /**
     * @return array<string, array{list<string>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function argvProvider(): array
    {
        return [
            'no site' => [['bin/console', 'background:services', 'run'], 'default'],
            'equals form' => [['bin/console', 'background:services', 'run', '--site=clinic2'], 'clinic2'],
            'space form' => [['bin/console', 'background:services', 'run', '--site', 'clinic2'], 'clinic2'],
            'before the command' => [['bin/console', '--site=clinic2', 'background:services', 'run'], 'clinic2'],
            'repeated: last one wins' => [['bin/console', 'x', '--site=clinic2', '--site=clinic3'], 'clinic3'],
            'repeated, mixed forms' => [['bin/console', 'x', '--site', 'clinic2', '--site=clinic3'], 'clinic3'],
            'space form without a value' => [['bin/console', 'x', '--site'], 'default'],
            'empty equals form' => [['bin/console', 'x', '--site='], 'default'],
            'after the options terminator' => [['bin/console', 'x', '--', '--site=clinic2'], 'default'],
            'similar option name' => [['bin/console', 'x', '--sites=clinic2'], 'default'],
        ];
    }
}
