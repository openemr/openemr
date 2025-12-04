<?php

/**
 * Isolated tests for USPSBase class
 *
 * Tests the USPSBase class functionality without requiring database connections
 * or external dependencies. Validates base API functionality, configuration management,
 * error handling, response processing, and XML operations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Generated Tests
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\USPS;

use OpenEMR\USPS\USPSBase;
use PHPUnit\Framework\TestCase;

class USPSBaseTest extends TestCase
{
    private USPSBase $uspsBase;

    protected function setUp(): void
    {
        $this->uspsBase = new USPSBase('testuser');
    }

    public function testConstructorSetsUsername(): void
    {
        $uspsBase = new TestableUSPSBase('myusername');
        // Since username is protected, we test through getPostData which uses it
        $postData = $uspsBase->getPostData();
        $this->assertNotFalse(strpos((string) $postData['XML'], 'myusername'));
    }

    public function testConstructorWithEmptyUsername(): void
    {
        $uspsBase = new TestableUSPSBase();
        $postData = $uspsBase->getPostData();
        $this->assertNotFalse(strpos((string) $postData['XML'], 'USERID=""'));
    }

    public function testSetUsername(): void
    {
        $testBase = new TestableUSPSBase();
        $testBase->setUsername('newuser');
        $postData = $testBase->getPostData();
        $this->assertNotFalse(strpos((string) $postData['XML'], 'newuser'));
    }

    public function testSetApiVersion(): void
    {
        $testBase = new TestableUSPSBase();
        $testBase->setApiVersion('RateV2');
        $postData = $testBase->getPostData();
        $this->assertEquals('RateV2', $postData['API']);
    }

    public function testGetPostData(): void
    {
        $testBase = new TestableUSPSBase();
        $testBase->setApiVersion('Verify');
        $postData = $testBase->getPostData();

        $this->assertIsArray($postData);
        $this->assertArrayHasKey('API', $postData);
        $this->assertArrayHasKey('XML', $postData);
        $this->assertEquals('Verify', $postData['API']);
        $this->assertIsString($postData['XML']);
    }

    public function testSetTestMode(): void
    {
        $this->uspsBase->setTestMode(true);
        $this->assertTrue(USPSBase::$testMode);

        $this->uspsBase->setTestMode(false);
        $this->assertFalse(USPSBase::$testMode);
    }

    public function testGetResponseApiName(): void
    {
        $this->uspsBase->setApiVersion('Verify');
        $responseApiName = $this->uspsBase->getResponseApiName();
        $this->assertEquals('AddressValidateResponse', $responseApiName);

        $this->uspsBase->setApiVersion('RateV2');
        $responseApiName = $this->uspsBase->getResponseApiName();
        $this->assertEquals('RateV2Response', $responseApiName);
    }

    public function testGetEndpointLiveMode(): void
    {
        $this->uspsBase->setTestMode(false);
        $endpoint = $this->uspsBase->getEndpoint();
        $this->assertEquals(USPSBase::LIVE_API_URL, $endpoint);
    }

    public function testSetAndGetResponse(): void
    {
        $response = '<xml>test response</xml>';
        $result = $this->uspsBase->setResponse($response);

        $this->assertInstanceOf(USPSBase::class, $result);
        $this->assertEquals($response, $this->uspsBase->getResponse());
    }

    public function testSetAndGetHeaders(): void
    {
        $headers = ['http_code' => 200, 'content_type' => 'text/xml'];
        $result = $this->uspsBase->setHeaders($headers);

        $this->assertInstanceOf(USPSBase::class, $result);
        $this->assertEquals($headers, $this->uspsBase->getHeaders());
    }

    public function testSetAndGetErrorCode(): void
    {
        $errorCode = 42;
        $result = $this->uspsBase->setErrorCode($errorCode);

        $this->assertInstanceOf(USPSBase::class, $result);
        $this->assertEquals($errorCode, $this->uspsBase->getErrorCode());
    }

    public function testSetAndGetErrorMessage(): void
    {
        $errorMessage = 'Test error message';
        $result = $this->uspsBase->setErrorMessage($errorMessage);

        $this->assertInstanceOf(USPSBase::class, $result);
        $this->assertEquals($errorMessage, $this->uspsBase->getErrorMessage());
    }

    public function testSetAndGetArrayResponse(): void
    {
        $arrayResponse = ['key' => 'value', 'nested' => ['inner' => 'data']];
        $this->uspsBase->setArrayResponse($arrayResponse);

        $this->assertEquals($arrayResponse, $this->uspsBase->getArrayResponse());
    }

    public function testIsErrorWithHttpError(): void
    {
        $this->uspsBase->setHeaders(['http_code' => 404]);
        $this->assertTrue($this->uspsBase->isError());
    }

    public function testIsErrorWithErrorInResponse(): void
    {
        $this->uspsBase->setHeaders(['http_code' => 200]);
        $this->uspsBase->setArrayResponse(['Error' => ['Number' => '123', 'Description' => 'Test error']]);
        $this->assertTrue($this->uspsBase->isError());
    }

    public function testIsErrorWithErrorStringInResponse(): void
    {
        $this->uspsBase->setHeaders(['http_code' => 200]);
        $this->uspsBase->setResponse('<xml><Error>Something went wrong</Error></xml>');
        $this->assertTrue($this->uspsBase->isError());
    }

    public function testIsErrorReturnsFalseForSuccess(): void
    {
        $this->uspsBase->setHeaders(['http_code' => 200]);
        $this->uspsBase->setResponse('<xml><Success>All good</Success></xml>');
        $this->uspsBase->setArrayResponse(['Success' => 'All good']);
        $this->assertFalse($this->uspsBase->isError());
    }

    public function testIsSuccess(): void
    {
        $this->uspsBase->setHeaders(['http_code' => 200]);
        $this->uspsBase->setResponse('<xml><Success>All good</Success></xml>');
        $this->uspsBase->setArrayResponse(['Success' => 'All good']);
        $this->assertTrue($this->uspsBase->isSuccess());

        $this->uspsBase->setHeaders(['http_code' => 404]);
        $this->assertFalse($this->uspsBase->isSuccess());
    }

    public function testConvertResponseToArrayWithValidXml(): void
    {
        $xmlResponse = '<?xml version="1.0"?><root><item>value</item></root>';
        $this->uspsBase->setResponse($xmlResponse);

        $result = $this->uspsBase->convertResponseToArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('root', $result);
    }

    public function testConvertResponseToArrayWithEmptyResponse(): void
    {
        $this->uspsBase->setResponse('');
        $result = $this->uspsBase->convertResponseToArray();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testCurlOptsConstants(): void
    {
        $this->assertIsArray(USPSBase::$CURL_OPTS);
        $this->assertArrayHasKey(CURLOPT_CONNECTTIMEOUT, USPSBase::$CURL_OPTS);
        $this->assertArrayHasKey(CURLOPT_TIMEOUT, USPSBase::$CURL_OPTS);
        $this->assertArrayHasKey(CURLOPT_RETURNTRANSFER, USPSBase::$CURL_OPTS);
        $this->assertEquals(30, USPSBase::$CURL_OPTS[CURLOPT_CONNECTTIMEOUT]);
        $this->assertEquals(60, USPSBase::$CURL_OPTS[CURLOPT_TIMEOUT]);
        $this->assertTrue(USPSBase::$CURL_OPTS[CURLOPT_RETURNTRANSFER]);
    }

    public function testApiCodesConstant(): void
    {
        // Using reflection to access protected property for testing
        $reflection = new \ReflectionClass($this->uspsBase);
        $apiCodesProperty = $reflection->getProperty('apiCodes');
        $apiCodes = $apiCodesProperty->getValue($this->uspsBase);

        $this->assertIsArray($apiCodes);
        $this->assertArrayHasKey('Verify', $apiCodes);
        $this->assertArrayHasKey('RateV2', $apiCodes);
        $this->assertEquals('AddressValidateRequest', $apiCodes['Verify']);
        $this->assertEquals('RateV2Request', $apiCodes['RateV2']);
    }
}

// Create a concrete implementation for testing protected methods
class TestableUSPSBase extends USPSBase
{
    protected $apiVersion = 'Verify';

    public function getPostFields(): array
    {
        return [];
    }

    public function getValueByKeyPublic($array, $key)
    {
        return $this->getValueByKey($array, $key);
    }
}

class USPSBaseTestExtended extends TestCase
{
    private TestableUSPSBase $uspsBase;

    protected function setUp(): void
    {
        $this->uspsBase = new TestableUSPSBase('testuser');
    }

    public function testGetValueByKeyFindsValue(): void
    {
        $array = [
            'level1' => [
                'level2' => [
                    'target' => 'found it'
                ]
            ]
        ];

        $result = $this->uspsBase->getValueByKeyPublic($array, 'target');
        $this->assertEquals('found it', $result);
    }

    public function testGetValueByKeyReturnsNullWhenNotFound(): void
    {
        $array = ['other' => 'value'];
        $result = $this->uspsBase->getValueByKeyPublic($array, 'nonexistent');
        $this->assertNull($result);
    }

    public function testGetValueByKeyWithDirectMatch(): void
    {
        $array = ['direct' => 'match'];
        $result = $this->uspsBase->getValueByKeyPublic($array, 'direct');
        $this->assertEquals('match', $result);
    }
}
