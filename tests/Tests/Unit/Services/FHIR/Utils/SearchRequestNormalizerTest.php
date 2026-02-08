<?php

/*
 * SearchRequestNormalizerTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Services\FHIR\Utils;

use Monolog\Level;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FHIR\Utils\SearchRequestNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;

class SearchRequestNormalizerTest extends TestCase
{
    public function testNormalizeSearchRequest(): void
    {
        $normalizer = new SearchRequestNormalizer(new SystemLogger(Level::Emergency));
        $request = HttpRestRequest::create('https://localhost/apis/default/fhir/Patient/_search', 'POST', [
            'name' => 'John'
            ,'date' => 'gt1980-01-01'
        ], [], [], [
            // TODO: figure out if we are fully mocking all these values for the request
            'HTTP_HOST' => 'localhost',
            'HTTPS' => 'on',
            'REQUEST_SCHEME' => 'https',
            'REQUEST_URI' => '/apis/default/fhir/Patient/_search',
            'SCRIPT_FILENAME' => '/var/www/html/openemr/apis/dispatch.php',
            'SCRIPT_NAME' => '/apis/dispatch.php',
            'PATH_INFO' => '/fhir/Patient/_search',
        ]);
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $request->setRequestUser($uuid, [
            'id' => 1
            ,'username' => 'testuser'
            ,'uuid' => $uuid
        ]);
        $session = $this->createMock(Session::class);
        $request->setRequestSite("default");
        $request->setSession($session);
        $request->setRequestUserRole('users');
        $request->setResource('Patient');
        $request->setAccessTokenId('some-access-token-id');
        $request->setAccessTokenScopes([
            'user/patient.read'
            ,'user/Patient.read'
            ,'api:fhir'
            ,'api:oemr'
        ]);
        $request->setQueryParams([
            'name' => 'Jason'
            ,'date' => 'lt2000-01-01'
        ]);
        $normalizedRequest = $normalizer->normalizeSearchRequest($request);
        $this->assertEquals('GET', $normalizedRequest->getMethod());
        $this->assertEquals('/default/fhir/Patient', $normalizedRequest->getRequestPath());
//        $expectedQueryString = urlencode('date[0]=lt2000-01-01&date[1]=gt1980-01-01&name[0]=John&name[1]=Jason');
        $dataParams = [
            'date' => ['lt2000-01-01', 'gt1980-01-01']
            ,'name' => ['Jason', 'John']
        ];
        $expectedQueryString = http_build_query($dataParams);
        $this->assertEquals($expectedQueryString, $normalizedRequest->getQueryString());
        $this->assertEquals($dataParams, $normalizedRequest->getQueryParams());
        $this->assertEquals([], $normalizedRequest->getPayload()->all());
        $this->assertEquals($uuid, $normalizedRequest->getRequestUserUUIDString());
        $this->assertEquals(['id' => 1, 'username' => 'testuser', 'uuid' => $uuid], $normalizedRequest->getRequestUser());
        $this->assertEquals('users', $normalizedRequest->getRequestUserRole());
        $this->assertEquals('Patient', $normalizedRequest->getResource());
        $this->assertEquals($session, $normalizedRequest->getSession());
        $this->assertEquals('default', $normalizedRequest->getRequestSite());
        $this->assertEquals('/fhir/Patient', $normalizedRequest->getRequestPathWithoutSite());
    }
}
