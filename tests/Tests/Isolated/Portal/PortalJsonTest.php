<?php

/**
 * The portal/patient JSON responses: the success body as given, the Phreeze-shaped 401 error,
 * and a value with invalid UTF-8 sent with U+FFFD instead of failing the response.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Controllers\Portal\Patient\PortalJson;
use PHPUnit\Framework\TestCase;

final class PortalJsonTest extends TestCase
{
    public function testOkSendsTheDataAsJson(): void
    {
        $response = PortalJson::ok(['id' => '5', 'docType' => 'Intake']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"id":"5","docType":"Intake"}', $response->getContent());
    }

    public function testErrorKeepsTheOldShapeAndStatus(): void
    {
        $response = PortalJson::error('Please check the form for errors', ['fname' => 'Required']);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(
            '{"success":false,"message":"Please check the form for errors","errors":{"fname":"Required"}}',
            $response->getContent(),
        );
    }

    public function testInvalidUtf8IsReplacedRatherThanFailingTheResponse(): void
    {
        $response = PortalJson::ok(['fullDocument' => "one bad \xE9 byte"]);

        $decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['fullDocument' => "one bad \u{FFFD} byte"], $decoded);
    }
}
