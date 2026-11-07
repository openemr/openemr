<?php

/**
 * Isolated tests for CdaTemplateId.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\CdaTemplateId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class CdaTemplateIdTest extends TestCase
{
    /**
     * @param list<string> $expected
     */
    #[DataProvider('rootsProvider')]
    public function testRoots(mixed $templateId, array $expected): void
    {
        $this->assertSame($expected, CdaTemplateId::roots($templateId));
    }

    /**
     * @return array<string, array{mixed, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rootsProvider(): array
    {
        return [
            'one templateId' => [['root' => '2.16.840.1.113883.10.20.22.4.78'], ['2.16.840.1.113883.10.20.22.4.78']],
            'one with an extension' => [['root' => '1.2.3', 'extension' => '2014-06-09'], ['1.2.3']],
            'several, in document order' => [
                [['root' => '1.2.3'], ['root' => '1.2.3', 'extension' => '2014-06-09'], ['root' => '4.5.6']],
                ['1.2.3', '1.2.3', '4.5.6'],
            ],
            'several, one without a root' => [[['extension' => 'x'], ['root' => '4.5.6']], ['4.5.6']],
            'empty root' => [['root' => ''], []],
            'non-string root' => [['root' => 123], []],
            'no templateId' => [null, []],
            'empty array' => [[], []],
            'a string' => ['1.2.3', []],
        ];
    }
}
