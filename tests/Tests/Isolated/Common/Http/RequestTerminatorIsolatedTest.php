<?php

/**
 * Isolated RequestTerminator Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use LogicException;
use OpenEMR\Common\Http\RequestTerminator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class RequestTerminatorIsolatedTest extends TestCase
{
    /**
     * Terminators must never return, so the test double throws instead of
     * exiting; the exception code carries the exit code it received.
     */
    private static function throwingTerminator(): RequestTerminator
    {
        return new RequestTerminator(static function (int $exitCode): never {
            throw new LogicException('terminated', $exitCode);
        });
    }

    /**
     * @return array<string, array{int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function statusToExitCodeProvider(): array
    {
        return [
            'success is exit zero' => [Response::HTTP_OK, 0],
            'redirect is exit zero' => [Response::HTTP_FOUND, 0],
            'client error is exit one' => [Response::HTTP_BAD_REQUEST, 1],
            'unauthorized is exit one' => [Response::HTTP_UNAUTHORIZED, 1],
            'server error is exit one' => [Response::HTTP_INTERNAL_SERVER_ERROR, 1],
        ];
    }

    #[DataProvider('statusToExitCodeProvider')]
    public function testRespondPassesExitCodeForStatus(int $statusCode, int $expectedExitCode): void
    {
        $this->expectOutputString('body');
        $this->expectException(LogicException::class);
        $this->expectExceptionCode($expectedExitCode);

        self::throwingTerminator()->respond(new Response('body', $statusCode));
    }

    public function testErrorSendsMessageWithStatus(): void
    {
        $this->expectOutputString('Cannot read the fax cache.');
        $this->expectException(LogicException::class);
        $this->expectExceptionCode(1);

        self::throwingTerminator()->error(Response::HTTP_INTERNAL_SERVER_ERROR, 'Cannot read the fax cache.');
    }
}
