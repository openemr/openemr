<?php

/**
 * Tests for UrlencodeConcatToQueryStringRector
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rector\Rules;

use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

class UrlencodeConcatToQueryStringRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/FixtureUrlencodeConcatToQueryString');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/urlencode-concat-to-query-string.php';
    }
}
