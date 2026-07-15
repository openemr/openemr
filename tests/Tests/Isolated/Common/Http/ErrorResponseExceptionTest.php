<?php

/**
 * Isolated tests for ErrorResponseException
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\ErrorResponseException;
use OpenEMR\Common\Http\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class ErrorResponseExceptionTest extends TestCase
{
    private function makeResponse(int $statusCode): ResponseInterface
    {
        return (new Psr17Factory())->createResponse($statusCode);
    }

    #[DataProvider('nonErrorStatusProvider')]
    public function testThrowIfErrorReturnsSameResponseForNonErrorStatus(int $status): void
    {
        $response = $this->makeResponse($status);

        self::assertSame(
            $response,
            ErrorResponseException::throwIfError($response),
            'A sub-400 response should be returned unchanged for inline use',
        );
    }

    /**
     * @return array<string, array{int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonErrorStatusProvider(): array
    {
        return [
            'ok' => [200],
            'created' => [201],
            'moved permanently' => [301],
            'found' => [302],
            'just below threshold' => [399],
        ];
    }

    #[DataProvider('errorStatusProvider')]
    public function testThrowIfErrorThrowsAndCarriesResponseForErrorStatus(int $status): void
    {
        $response = $this->makeResponse($status);

        try {
            ErrorResponseException::throwIfError($response);
            self::fail("Expected ErrorResponseException for status $status");
        } catch (ErrorResponseException $e) {
            self::assertSame(
                $response,
                $e->response,
                'The exception should carry the response that triggered it',
            );
            self::assertSame(
                $status,
                $e->getCode(),
                'The exception code should be the response status',
            );
            self::assertStringContainsString(
                (string) $status,
                $e->getMessage(),
                'The message should mention the status without leaking body content',
            );
        }
    }

    /**
     * @return array<string, array{int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function errorStatusProvider(): array
    {
        return [
            'threshold' => [400],
            'not found' => [404],
            'unprocessable' => [422],
            'server error' => [500],
            'unavailable' => [503],
        ];
    }

    public function testThrownExceptionIsCatchableAsPsr18ClientException(): void
    {
        try {
            ErrorResponseException::throwIfError($this->makeResponse(500));
            self::fail('Expected ErrorResponseException to be thrown');
        } catch (ClientExceptionInterface $e) {
            self::assertInstanceOf(
                ErrorResponseException::class,
                $e,
                'The exception must be catchable via the PSR-18 marker interface',
            );
        }
    }
}
