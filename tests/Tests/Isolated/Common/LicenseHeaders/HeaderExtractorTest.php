<?php

/**
 * Tests which comments count as the file header, and how tags are read from them
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\LicenseHeaders;

use OpenEMR\Common\LicenseHeaders\FileType;
use OpenEMR\Common\LicenseHeaders\HeaderExtractor;
use OpenEMR\Common\LicenseHeaders\HeaderTag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class HeaderExtractorTest extends TestCase
{
    /**
     * @param list<array{string, string, int}> $expected name, value, and line of each tag
     */
    #[DataProvider('headerProvider')]
    public function testExtractsHeaderTags(FileType $type, string $source, array $expected): void
    {
        $tags = (new HeaderExtractor())->extract($type, $source);
        $actual = array_map(static fn(HeaderTag $tag): array => [$tag->name, $tag->value, $tag->line], $tags);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{FileType, string, list<array{string, string, int}>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function headerProvider(): array
    {
        return [
            'php docblock' => [FileType::Php, <<<'PHP'
                <?php

                /**
                 * Does a thing
                 *
                 * @package   OpenEMR
                 * @license   https://example.com/LICENSE GPL 3
                 */

                echo 'hi';
                PHP, [['package', 'OpenEMR', 6], ['license', 'https://example.com/LICENSE GPL 3', 7]]],
            'php shebang' => [FileType::Php, <<<'PHP'
                #!/usr/bin/env php
                <?php
                /** @license X */
                PHP, [['license', 'X', 3]]],
            'php declare and namespace come first' => [FileType::Php, <<<'PHP'
                <?php
                declare(strict_types=1);
                namespace Foo;
                /** @license X */
                PHP, [['license', 'X', 4]]],
            'php license on a class docblock after the imports' => [FileType::Php, <<<'PHP'
                <?php
                namespace Foo;
                use Bar\Baz;
                use Bar\{One, Two};
                /**
                 * @license X
                 */
                final class Qux {}
                PHP, [['license', 'X', 6]]],
            'php braced namespace body is code' => [FileType::Php, <<<'PHP'
                <?php
                namespace Foo {
                /** @license X */
                }
                PHP, []],
            'php code before the comment' => [FileType::Php, <<<'PHP'
                <?php
                require 'x.php';
                /** @license X */
                PHP, []],
            'php html before the open tag' => [FileType::Php, <<<'PHP'
                <html>
                <?php /** @license X */
                PHP, []],
            'php hash and slash comments' => [FileType::Php, <<<'PHP'
                <?php
                # @author A
                // @license X
                PHP, [['author', 'A', 2], ['license', 'X', 3]]],
            'wrapped value joins its continuation line' => [FileType::Php, <<<'PHP'
                <?php
                /**
                 * @license https://example.com/LICENSE GNU General
                 * Public License 3
                 */
                PHP, [['license', 'https://example.com/LICENSE GNU General Public License 3', 3]]],
            'blank comment line ends the tag' => [FileType::Php, <<<'PHP'
                <?php
                /**
                 * @license X
                 *
                 * Some more description.
                 */
                PHP, [['license', 'X', 3]]],
            'tag name is lowercased and loses a trailing colon' => [FileType::Twig, <<<'TWIG'
                {#
                   @License: Public domain
                #}
                TWIG, [['license', 'Public domain', 2]]],
            'empty tag' => [FileType::Php, <<<'PHP'
                <?php
                /**
                 * @author
                 */
                PHP, [['author', '', 3]]],
            'js block comment' => [FileType::CStyle, <<<'JS'

                /**
                 * @license X
                 */
                const a = 1;
                JS, [['license', 'X', 3]]],
            'js line comments and shebang' => [FileType::CStyle, <<<'JS'
                #!/usr/bin/env node
                // @author A
                // @license X
                run();
                JS, [['author', 'A', 2], ['license', 'X', 3]]],
            'js code before the comment' => [FileType::CStyle, <<<'JS'
                import x from 'y';
                /** @license X */
                JS, []],
            'js unterminated comment' => [FileType::CStyle, <<<'JS'
                /**
                 * @license X
                JS, [['license', 'X', 2]]],
            'scss comment after another comment' => [FileType::CStyle, <<<'SCSS'
                /* Theme */
                /* @license X */
                SCSS, [['license', 'X', 2]]],
            'twig header' => [FileType::Twig, <<<'TWIG'
                {#
                 # Template
                 #
                 # @license X
                 #}
                <div></div>
                TWIG, [['license', 'X', 4]]],
            'twig markup before the comment' => [FileType::Twig, <<<'TWIG'
                {% extends 'base.html.twig' %}
                {# @license X #}
                TWIG, []],
        ];
    }

    public function testSkipsByteOrderMark(): void
    {
        $source = pack('H*', 'efbbbf') . '/** @license X */';
        $tags = (new HeaderExtractor())->extract(FileType::CStyle, $source);
        self::assertSame([['license', 'X', 1]], array_map(static fn(HeaderTag $tag): array => [$tag->name, $tag->value, $tag->line], $tags));
    }

    public function testNormalizesWhitespace(): void
    {
        self::assertSame('a b c', HeaderTag::normalizeValue("  a \t b\r\n  c "));
    }
}
