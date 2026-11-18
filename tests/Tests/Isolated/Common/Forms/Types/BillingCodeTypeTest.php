<?php

/**
 * Isolated tests for BillingCodeType's handling of edit option E
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms\Types;

use OpenEMR\Common\Forms\Types\BillingCodeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../../library/htmlspecialchars.inc.php';

class BillingCodeTypeTest extends TestCase
{
    #[DataProvider('editOptionEProvider')]
    public function testEditOptionEClassFollowsTheEOption(?string $editOptions, bool $expectMultiple): void
    {
        $frow = [
            'field_id' => 'codes',
            'max_length' => 0,
            'fld_length' => 20,
            'edit_options' => $editOptions,
        ];

        $html = (new BillingCodeType())->buildFormView($frow, '');

        self::assertSame($expectMultiple, str_contains($html, 'EditOptionE'));
    }

    /**
     * @return array<string, array{?string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function editOptionEProvider(): array
    {
        return [
            'JSON E'                  => ['["E"]', true],
            'JSON E with option 2'    => ['["2","E"]', true],
            'JSON EP only'            => ['["EP"]', false],
            'JSON EP with option 2'   => ['["2","EP"]', false],
            'legacy E'                => ['E', true],
            'legacy 2E'               => ['2E', true],
            'legacy 2 without E'      => ['2', false],
            'no options'              => [null, false],
        ];
    }
}
