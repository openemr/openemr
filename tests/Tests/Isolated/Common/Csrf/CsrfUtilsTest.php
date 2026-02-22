<?php

/**
 * Isolated tests for CsrfUtils
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Csrf;

use OpenEMR\Common\Csrf\CsrfUtils;
use PHPUnit\Framework\TestCase;

class CsrfUtilsTest extends TestCase
{
    public function testCsrfViolationSetsHttp403(): void
    {
        CsrfUtils::csrfViolation(toScreen: false, toLog: false);
        $this->assertSame(403, http_response_code());
    }
}
