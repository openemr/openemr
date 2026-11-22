<?php

/**
 * Isolated tests for formatting a GitHub Actions error annotation
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\ComposerAnnotations;

use OpenEMR\Common\ComposerAnnotations\Annotation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class AnnotationTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function messageProvider(): array
    {
        return [
            'plain' => ['composer.json failed', 'composer.json failed'],
            'percent sign' => ['100% of the time', '100%25 of the time'],
            'carriage return' => ['from a CRLF line' . chr(13), 'from a CRLF line%0D'],
            'line feed' => ['first' . chr(10) . 'second', 'first%0Asecond'],
            'escape sequence already in the text' => ['%0A', '%250A'],
        ];
    }

    #[DataProvider('messageProvider')]
    public function testTheMessageIsEscapedForTheWorkflowCommand(string $message, string $escaped): void
    {
        $annotation = new Annotation('composer.lock', 'composer validate', $message);

        self::assertSame('::error file=composer.lock,title=composer validate::' . $escaped, $annotation->toWorkflowCommand());
    }

    public function testALineGoesBeforeTheTitle(): void
    {
        $annotation = new Annotation('composer.json', 'composer normalize', 'not normalized', 42);

        self::assertSame('::error file=composer.json,line=42,title=composer normalize::not normalized', $annotation->toWorkflowCommand());
    }

    public function testARangeEndsWithItsLastLine(): void
    {
        $annotation = new Annotation('composer.json', 'composer normalize', 'not normalized', 7, 16);

        self::assertSame('::error file=composer.json,line=7,endLine=16,title=composer normalize::not normalized', $annotation->toWorkflowCommand());
    }

    public function testARangeOfOneLineNamesOnlyThatLine(): void
    {
        $annotation = new Annotation('composer.json', 'composer normalize', 'not normalized', 41, 41);

        self::assertSame('::error file=composer.json,line=41,title=composer normalize::not normalized', $annotation->toWorkflowCommand());
    }

    public function testPropertiesAlsoEscapeTheirSeparators(): void
    {
        $annotation = new Annotation('a,b.json', 'Check: 100%', 'Keep: a, b');

        self::assertSame('::error file=a%2Cb.json,title=Check%3A 100%25::Keep: a, b', $annotation->toWorkflowCommand());
    }
}
