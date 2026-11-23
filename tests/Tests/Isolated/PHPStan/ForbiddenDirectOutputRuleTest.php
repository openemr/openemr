<?php

/**
 * Tests for ForbiddenDirectOutputRule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan;

use OpenEMR\PHPStan\Rules\ForbiddenDirectOutputRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenDirectOutputRule>
 */
final class ForbiddenDirectOutputRuleTest extends RuleTestCase
{
    private const FIXTURE = __DIR__ . '/data/direct_output_usage.php';

    private const ECHO = 'echo is forbidden. Return output in a Response or render it with a Twig template.';

    private const PRINT = 'print is forbidden. Return output in a Response or render it with a Twig template.';

    private const HTML = 'Inline HTML outside a PHP block is forbidden. Render markup with a Twig template.';

    private const TIP = 'Return a Symfony\Component\HttpFoundation\Response from a controller, or render markup through a Twig template (OpenEMR\Common\Twig\TwigContainer). Console commands write to the Symfony Console OutputInterface.';

    /** @var list<string> */
    private array $exemptPathFragments = [];

    /**
     * The fixture lives under tests/, which the shipped exemption list
     * skips, so the default instance under test carries no exemptions.
     */
    protected function getRule(): Rule
    {
        return new ForbiddenDirectOutputRule($this->exemptPathFragments);
    }

    public function testFlagsEveryDirectOutputConstruct(): void
    {
        $this->analyse([self::FIXTURE], [
            [self::ECHO, 19, self::TIP],
            [self::ECHO, 20, self::TIP],
            [self::PRINT, 21, self::TIP],
            [self::PRINT, 22, self::TIP],
            ['printf() writes output directly and is forbidden. Build the string with sprintf() and return it in a Response or pass it to a template.', 23, self::TIP],
            ['vprintf() writes output directly and is forbidden. Build the string with vsprintf() and return it in a Response or pass it to a template.', 24, self::TIP],
            ['readfile() writes output directly and is forbidden. Send files with a Symfony BinaryFileResponse.', 25, self::TIP],
            ['fpassthru() writes output directly and is forbidden. Stream file contents with a Symfony StreamedResponse or BinaryFileResponse.', 28, self::TIP],
            [self::ECHO, 31, self::TIP],
            [self::HTML, 46, self::TIP],
            [self::ECHO, 47, self::TIP],
            [self::HTML, 49, self::TIP],
            [self::HTML, 55, self::TIP],
            [self::ECHO, 56, self::TIP],
        ]);
    }

    public function testSkipsShebangOnFirstLineOnly(): void
    {
        $this->analyse([__DIR__ . '/data/direct_output_shebang.php'], [
            [self::HTML, 17, self::TIP],
        ]);
    }

    public function testExemptPathIsIgnored(): void
    {
        $this->exemptPathFragments = ['/PHPStan/data/'];

        $this->analyse([self::FIXTURE], []);
    }

    public function testShippedExemptionsCoverTests(): void
    {
        $this->exemptPathFragments = ForbiddenDirectOutputRule::DEFAULT_EXEMPT_PATH_FRAGMENTS;

        $this->analyse([self::FIXTURE], []);
    }
}
