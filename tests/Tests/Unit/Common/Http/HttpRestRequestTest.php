<?php

/*
 * HttpRestRequestTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Http;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ResourceScopeEntityList;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Http\HttpRestRequest;
use PHPUnit\Framework\TestCase;

class HttpRestRequestTest extends TestCase
{
    /**
     * Store original superglobals to restore after each test
     */
    private array $originalGet;
    private array $originalServer;

    protected function setUp(): void
    {
        parent::setUp();

        // Store original superglobals
        $this->originalGet = $_GET;
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        // Restore original superglobals
        $_GET = $this->originalGet;
        $_SERVER = $this->originalServer;

        parent::tearDown();
    }

    /**
     * Test createFromGlobals with _REWRITE_COMMAND parameter
     */
    public function testCreateFromGlobalsWithRewriteCommand(): void
    {
        // Simulate .htaccess rewrite: /default/fhir/Patient/123?name=john -> dispatch.php?_REWRITE_COMMAND=default/fhir/Patient/123&name=john
        $_GET = [
            '_REWRITE_COMMAND' => 'default/fhir/Patient/123',
            'name' => 'john'
        ];

        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'DOCUMENT_ROOT' => '/var/www/html/openemr/',
            'REQUEST_URI' => '/apis/dispatch.php?_REWRITE_COMMAND=default/fhir/Patient/123&name=john',
            'QUERY_STRING' => '_REWRITE_COMMAND=default/fhir/Patient/123&name=john',
            'SCRIPT_NAME' => '/apis/dispatch.php',
            'SCRIPT_FILENAME' => '/var/www/html/openemr/apis/dispatch.php'
        ];

        $request = HttpRestRequest::createFromGlobals();

        // Verify that _REWRITE_COMMAND is removed from $_GET
        $this->assertArrayNotHasKey('_REWRITE_COMMAND', $_GET, '_REWRITE_COMMAND should be removed from $_GET');

        // Verify that other query parameters remain
        $this->assertArrayHasKey('name', $_GET, 'Other query parameters should remain');
        $this->assertEquals('john', $_GET['name'], 'Query parameter value should be preserved');

        // Verify PATH_INFO is set correctly
        $this->assertEquals('/default/fhir/Patient/123', $_SERVER['PATH_INFO'], 'PATH_INFO should be set to the rewrite path');

        // Verify REQUEST_URI is updated to clean path
        $this->assertEquals('/apis/dispatch.php/default/fhir/Patient/123?name=john', $_SERVER['REQUEST_URI'], 'REQUEST_URI should be clean path with remaining query params');

        // Verify SCRIPT_NAME points to dispatch.php
        $this->assertEquals('/apis/dispatch.php', $_SERVER['SCRIPT_NAME'], 'SCRIPT_NAME should point to dispatch.php');

        // Verify QUERY_STRING excludes _REWRITE_COMMAND
        $this->assertEquals('name=john', $_SERVER['QUERY_STRING'], 'QUERY_STRING should exclude _REWRITE_COMMAND');

        // Verify PHP_SELF is constructed correctly
        $this->assertEquals('/apis/dispatch.php/default/fhir/Patient/123', $_SERVER['PHP_SELF'], 'PHP_SELF should combine SCRIPT_NAME and PATH_INFO');

        // Verify the request object has the correct path info
        $this->assertEquals('/default/fhir/Patient/123', $request->getPathInfo(), 'Request should have correct path info');

        // Verify query parameters are accessible through the request
        $this->assertEquals('john', $request->getQueryParam('name'), 'Query parameters should be accessible');
        $this->assertNull($request->getQueryParam('_REWRITE_COMMAND'), '_REWRITE_COMMAND should not be accessible as query param');
    }

    /**
     * Test createFromGlobals with _REWRITE_COMMAND but no additional query parameters
     */
    public function testCreateFromGlobalsWithRewriteCommandNoQueryParams(): void
    {
        $_GET = [
            '_REWRITE_COMMAND' => 'default/fhir/Patient/123'
        ];

        $_SERVER = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'localhost',
            'DOCUMENT_ROOT' => '/var/www/html/openemr/',
            'REQUEST_URI' => '/apis/dispatch.php?_REWRITE_COMMAND=default/fhir/Patient/123',
            'QUERY_STRING' => '_REWRITE_COMMAND=default/fhir/Patient/123',
            'SCRIPT_NAME' => '/apis/dispatch.php',
            'SCRIPT_FILENAME' => '/var/www/html/openemr/apis/dispatch.php'
        ];

        $request = HttpRestRequest::createFromGlobals();

        // Verify $_GET is empty after removing _REWRITE_COMMAND
        $this->assertEmpty($_GET, '$_GET should be empty after removing _REWRITE_COMMAND');

        // Verify PATH_INFO is set correctly
        $this->assertEquals('/default/fhir/Patient/123', $_SERVER['PATH_INFO'], 'PATH_INFO should be set to the rewrite path');

        // Verify REQUEST_URI has no query string
        $this->assertEquals('/apis/dispatch.php/default/fhir/Patient/123', $_SERVER['REQUEST_URI'], 'REQUEST_URI should have no query string');

        // Verify QUERY_STRING is empty
        $this->assertEquals('', $_SERVER['QUERY_STRING'], 'QUERY_STRING should be empty');

        // Verify request method is preserved
        $this->assertEquals('POST', $request->getMethod(), 'Request method should be preserved');
    }

    /**
     * Test createFromGlobals without _REWRITE_COMMAND (normal request)
     */
    public function testCreateFromGlobalsWithoutRewriteCommand(): void
    {
        $_GET = [
            'param1' => 'value1',
            'param2' => 'value2'
        ];

        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/some/path?param1=value1&param2=value2',
            'QUERY_STRING' => 'param1=value1&param2=value2',
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/some/path'
        ];

        $originalGet = $_GET;
        $originalServer = $_SERVER;

        $request = HttpRestRequest::createFromGlobals();

        // Verify superglobals are unchanged when no _REWRITE_COMMAND
        $this->assertEquals($originalGet, $_GET, '$_GET should be unchanged when no _REWRITE_COMMAND');
        $this->assertEquals($originalServer['REQUEST_URI'], $_SERVER['REQUEST_URI'], 'REQUEST_URI should be unchanged');
        $this->assertEquals($originalServer['PATH_INFO'], $_SERVER['PATH_INFO'], 'PATH_INFO should be unchanged');

        // Verify request object works normally
        $this->assertEquals('value1', $request->getQueryParam('param1'), 'Normal query parameters should work');
        $this->assertEquals('/some/path', $request->getPathInfo(), 'Normal path info should work');
    }

    /**
     * Test createFromGlobals with _REWRITE_COMMAND and leading/trailing slashes
     */
    public function testCreateFromGlobalsWithRewriteCommandSlashHandling(): void
    {
        $_GET = [
            '_REWRITE_COMMAND' => '/default/fhir/Patient/123/' // with leading and trailing slashes
        ];

        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'DOCUMENT_ROOT' => '/var/www/html',
            'REQUEST_URI' => '/apis/dispatch.php?_REWRITE_COMMAND=/default/fhir/Patient/123/',
            'QUERY_STRING' => '_REWRITE_COMMAND=/default/fhir/Patient/123/',
            'SCRIPT_NAME' => '/apis/dispatch.php',
            'SCRIPT_FILENAME' => '/var/www/html/openemr/apis/dispatch.php'
        ];

        $request = HttpRestRequest::createFromGlobals();

        // Verify PATH_INFO handles leading slash correctly (should normalize to single leading slash)
        $this->assertEquals('/apis/dispatch.php/default/fhir/Patient/123/', $_SERVER['REQUEST_URI'], "REQUEST_URI should be handled correcty");
        $this->assertEquals('/default/fhir/Patient/123/', $_SERVER['PATH_INFO'], 'PATH_INFO should handle slashes correctly');
        $this->assertEquals('/default/fhir/Patient/123/', $request->getPathInfo(), 'Request path info should handle slashes correctly');
    }

    /**
     * Test createFromGlobals with empty _REWRITE_COMMAND
     */
    public function testCreateFromGlobalsWithEmptyRewriteCommand(): void
    {
        $_GET = [
            '_REWRITE_COMMAND' => '',
            'other' => 'param'
        ];

        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'DOCUMENT_ROOT' => '/var/www/html',
            'REQUEST_URI' => '/dispatch.php?_REWRITE_COMMAND=&other=param',
            'QUERY_STRING' => '_REWRITE_COMMAND=&other=param',
            'SCRIPT_NAME' => '/dispatch.php'
        ];

        $request = HttpRestRequest::createFromGlobals();

        // Verify _REWRITE_COMMAND is still removed even if empty
        $this->assertArrayNotHasKey('_REWRITE_COMMAND', $_GET, '_REWRITE_COMMAND should be removed even if empty');

        // Verify PATH_INFO is set to root when empty
        $this->assertEquals('/', $_SERVER['PATH_INFO'], 'PATH_INFO should be / when rewrite command is empty');

        // Verify other parameters are preserved
        $this->assertEquals('param', $_GET['other'], 'Other parameters should be preserved');
    }

    /**
     * Test that APICSRFTOKEN header sets isLocalApi flag
     */
    public function testCreateFromGlobalsWithApiCsrfToken(): void
    {
        $_GET = [];
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'HTTP_APICSRFTOKEN' => 'some-token-value'
        ];

        $request = HttpRestRequest::createFromGlobals();

        $this->assertTrue($request->isLocalApi(), 'Request should be marked as local API when APICSRFTOKEN header is present');
    }

    /**
     * Test default patientRequest value
     */
    public function testCreateFromGlobalsDefaultPatientRequest(): void
    {
        $_GET = [];
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost'
        ];

        $request = HttpRestRequest::createFromGlobals();

        $this->assertFalse($request->isPatientRequest(), 'Patient request should default to false');
    }

    public function testRequestHasScopeReadAccessWithBackwardsCompatability(): void
    {
        $request = HttpRestRequest::create("/api/fhir/Patient/123");
        $scopeResourceList = [];
        $scopeResourceList['patient/Patient'] = new ResourceScopeEntityList('patient/Patient');
        $scopeResourceList['patient/Patient'][] = ScopeEntity::createFromString("patient/Patient.read");
        $request->setAccessTokenScopeValidationArray($scopeResourceList);
        $this->assertTrue($request->requestHasScope('patient/Patient.r'), 'patient/Patient.r should be a valid scope for Patient.read access');
        $this->assertTrue($request->requestHasScope('patient/Patient.s'), 'patient/Patient.s should be a valid scope for Patient.read access');
        $this->assertTrue($request->requestHasScope('patient/Patient.read'), 'patient/Patient.read should be a valid scope for Patient.read access');
        $this->assertTrue($request->requestHasScope('patient/Patient.rs'), "HTTPRestRequest->requestHasScope should be valid for Patient.read access");
    }

    public function testRequestHasScopeWriteAccessWithBackwardsCompatability(): void
    {
        $request = HttpRestRequest::create("/api/fhir/Patient/123");
        $scopeResourceList = [];
        $scopeResourceList['patient/Patient'] = new ResourceScopeEntityList('patient/Patient');
        $scopeResourceList['patient/Patient'][] = ScopeEntity::createFromString("patient/Patient.write");
        $request->setAccessTokenScopeValidationArray($scopeResourceList);
        $this->assertTrue($request->requestHasScope('patient/Patient.c'), "patient/Patient.c should be a valid scope for Patient.write access");
        $this->assertTrue($request->requestHasScope('patient/Patient.u'), "patient/Patient.u should be a valid scope for Patient.write access");
        $this->assertTrue($request->requestHasScope('patient/Patient.d'), "patient/Patient.d should be a valid scope for Patient.write access");
        $this->assertTrue($request->requestHasScope('patient/Patient.write'), "patient/Patient.write should be a valid scope for Patient.write access");

        // requests will typically just get a single operation... but we can still support multiple operations
        $this->assertTrue($request->requestHasScope('patient/Patient.cu'), "HTTPRestRequest->requestHasScope should be valid for Patient.write access");
        $this->assertTrue($request->requestHasScope('patient/Patient.cd'), "HTTPRestRequest->requestHasScope should be valid for Patient.write access");
        $this->assertTrue($request->requestHasScope('patient/Patient.cud'), "HTTPRestRequest->requestHasScope should be valid for Patient.write access");
    }

    public function testRequestHasScopeThrowsInvalidArgumentExceptionForInvalidScope(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid permission string: sr");

        $request = HttpRestRequest::create("/api/fhir/Patient/123");
        $request->requestHasScope('patient/Patient.sr');
    }
}
