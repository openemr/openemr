<?php

/**
 * Isolated tests for the UDS data-quality gap enum.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting\DataQuality;

use OpenEMR\FQHC\Reporting\DataQuality\UdsDataQualityGap;
use PHPUnit\Framework\TestCase;

final class UdsDataQualityGapTest extends TestCase
{
    public function testEveryGapHasALabel(): void
    {
        foreach (UdsDataQualityGap::cases() as $gap) {
            self::assertNotSame('', $gap->label());
        }
    }
}
