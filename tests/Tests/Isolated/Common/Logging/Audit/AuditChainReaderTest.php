<?php

/**
 * The tamper report's date window.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use PHPUnit\Framework\TestCase;

class AuditChainReaderTest extends TestCase
{
    public function testDateWindowIncludesAChainRowWhoseVersionWasLowered(): void
    {
        $source = file_get_contents(dirname(__DIR__, 6) . '/src/Common/Logging/Audit/AuditChainReader.php');

        self::assertIsString($source);
        self::assertStringContainsString(
            "WHERE (c.version >= 5 OR (c.chain IS NOT NULL AND c.chain <> ''))",
            $source,
        );
    }
}
