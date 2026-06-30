<?php

/**
 * Isolated tests for the OpenEMR sex → UDS sex classifier.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\UdsSex;
use OpenEMR\FQHC\Reporting\UdsSexClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UdsSexClassifierTest extends TestCase
{
    #[DataProvider('sexProvider')]
    public function testClassify(?string $optionId, ?UdsSex $expected): void
    {
        self::assertSame($expected, (new UdsSexClassifier())->classify($optionId));
    }

    /**
     * @return array<string, array{?string, ?UdsSex}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sexProvider(): array
    {
        return [
            'male' => ['Male', UdsSex::Male],
            'female' => ['Female', UdsSex::Female],
            'unknown is null' => ['UNK', null],
            'null is null' => [null, null],
            'unrecognized is null' => ['other', null],
        ];
    }
}
