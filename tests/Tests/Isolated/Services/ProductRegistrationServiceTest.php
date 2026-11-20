<?php

/**
 * Isolated tests for the product registration opt-in request.
 *
 * A successful opt-in writes to the database, so these cover the request and every reply that
 * fails before that write.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OpenEMR\Core\OEEnvBag;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\ProductRegistrationService;
use OpenEMR\Services\SoftwareVersion;
use OpenEMR\Services\VersionServiceInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

#[Group('isolated')]
final class ProductRegistrationServiceTest extends TestCase
{
    private const REGISTRATION_URL = 'https://reg.open-emr.org/api/registration';

    private bool $savedDisableTranslation = false;

    protected function setUp(): void
    {
        $this->savedDisableTranslation = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', $this->savedDisableTranslation);
    }

    public function testOptInPostsTheEmailAndVersionAndRejectsAFailedReply(): void
    {
        $handler = new MockHandler([new Response(500)]);
        $service = $this->serviceUsing($handler);

        $error = null;
        try {
            $service->registerProduct('admin@example.test');
        } catch (\GenericProductRegistrationException $e) {
            $error = $e;
        }

        // A reply other than 201 is an error.
        $this->assertInstanceOf(\GenericProductRegistrationException::class, $error);
        $this->assertSame('Server error: try again later', $error->getMessage());
        $request = $handler->getLastRequest();
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::REGISTRATION_URL, (string) $request->getUri());
        $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        parse_str((string) $request->getBody(), $form);
        $this->assertSame('admin@example.test', $form['email'] ?? null);
        $this->assertSame('8.1.0', $form['version'] ?? null);
        $this->assertArrayNotHasKey('distribution', $form);
        $options = $handler->getLastOptions();
        $this->assertFalse($options['allow_redirects']);
        $this->assertTrue($options['verify']);
    }

    public function testAnUnreachableServerIsAnErrorThatKeepsTheCause(): void
    {
        $handler = new MockHandler([new ConnectException('Connection refused', new Request('POST', self::REGISTRATION_URL))]);
        $service = $this->serviceUsing($handler);

        $error = null;
        try {
            $service->registerProduct('admin@example.test');
        } catch (\GenericProductRegistrationException $e) {
            $error = $e;
        }

        $this->assertInstanceOf(\GenericProductRegistrationException::class, $error);
        $this->assertSame('Server error: try again later', $error->getMessage());
        $this->assertInstanceOf(ConnectException::class, $error->getPrevious());
    }

    public function testOptInNamesTheDockerDistribution(): void
    {
        $handler = new MockHandler([new Response(500)]);
        $service = $this->serviceUsing($handler, new OEEnvBag(['OPENEMR_DOCKER_ENV_TAG' => 'flex-3.22']));

        $this->expectException(\GenericProductRegistrationException::class);
        try {
            $service->registerProduct('admin@example.test');
        } finally {
            $request = $handler->getLastRequest();
            $this->assertInstanceOf(RequestInterface::class, $request);
            parse_str((string) $request->getBody(), $form);
            $this->assertSame('flex-3.22', $form['distribution'] ?? null);
        }
    }

    public function testARedirectIsNotFollowed(): void
    {
        // The handler holds one reply, so following the redirect would fail on a second request.
        $handler = new MockHandler([new Response(302, ['Location' => 'https://elsewhere.example.test/'])]);

        $service = $this->serviceUsing($handler);

        $this->expectException(\GenericProductRegistrationException::class);
        try {
            $service->registerProduct('admin@example.test');
        } finally {
            $this->assertCount(0, $handler);
        }
    }

    public function testOptInHonorsTheCertificateVerificationSetting(): void
    {
        $handler = new MockHandler([new Response(500)]);
        $service = $this->serviceUsing($handler, globals: new OEGlobalsBag(['http_verify_ssl' => false]));

        $this->expectException(\GenericProductRegistrationException::class);
        try {
            $service->registerProduct('admin@example.test');
        } finally {
            $this->assertFalse($handler->getLastOptions()['verify']);
        }
    }

    private function serviceUsing(
        MockHandler $handler,
        OEEnvBag $env = new OEEnvBag(),
        OEGlobalsBag $globals = new OEGlobalsBag(['http_verify_ssl' => true]),
    ): ProductRegistrationService {
        $versionService = $this->createStub(VersionServiceInterface::class);
        $versionService->method('getSoftwareVersion')->willReturn(new SoftwareVersion(8, 1, 0, '', 0, 0, 0));
        $client = new Client(['handler' => HandlerStack::create($handler), 'http_errors' => false]);
        return new ProductRegistrationService($client, $versionService, $env, $globals);
    }
}
