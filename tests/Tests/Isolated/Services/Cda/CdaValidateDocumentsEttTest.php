<?php

/**
 * Isolated tests for sending a C-CDA document to an external (ETT) validator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda {

    // A failed request's description goes through the unqualified xlt(), which needs the database. Stub it in
    // the class's namespace, so PHP finds it before the global function; guarded so the isolated suite can share
    // a process.
    if (!function_exists('OpenEMR\\Services\\Cda\\xlt')) {
        function xlt(string $s): string
        {
            return $s;
        }
    }
}

namespace OpenEMR\Tests\Isolated\Services\Cda {

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\ConnectException;
    use GuzzleHttp\Handler\MockHandler;
    use GuzzleHttp\HandlerStack;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;
    use OpenEMR\Core\OEGlobalsBag;
    use OpenEMR\Services\Cda\CdaValidateDocuments;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\Attributes\Group;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\RequestInterface;
    use Psr\Log\NullLogger;

    #[Group('isolated')]
    class CdaValidateDocumentsEttTest extends TestCase
    {
        private const VALIDATOR_URL = 'https://ett.example.test/referenceccdaservice/';

        private const DOCUMENT = '<ClinicalDocument xmlns="urn:hl7-org:v3"/>';

        private const SETTINGS = ['mdht_conformance_server', 'mdht_conformance_server_enable'];

        /** @var array<string, mixed> */
        private array $savedSettings = [];

        protected function setUp(): void
        {
            foreach (self::SETTINGS as $key) {
                $this->savedSettings[$key] = OEGlobalsBag::getInstance()->get($key);
            }
        }

        protected function tearDown(): void
        {
            foreach ($this->savedSettings as $key => $value) {
                OEGlobalsBag::getInstance()->set($key, $value);
            }
        }

        /**
         * @return array<string, array{string}>
         *
         * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
         */
        public static function serverSettingProvider(): array
        {
            return [
                'with a trailing slash' => ['https://ett.example.test/'],
                'without one' => ['https://ett.example.test'],
                'padded with spaces' => ['  https://ett.example.test/ '],
            ];
        }

        #[DataProvider('serverSettingProvider')]
        public function testTheServerSettingBecomesTheValidatorUrl(string $server): void
        {
            OEGlobalsBag::getInstance()->set('mdht_conformance_server', $server);
            OEGlobalsBag::getInstance()->set('mdht_conformance_server_enable', '1');

            $validator = new CdaValidateDocuments(new Client(), new NullLogger());

            $this->assertTrue($validator->externalValidatorEnabled);
            $this->assertSame(self::VALIDATOR_URL, $validator->externalValidatorUrl);
        }

        public function testAWhitespaceServerSettingLeavesTheValidatorOff(): void
        {
            OEGlobalsBag::getInstance()->set('mdht_conformance_server', '   ');
            OEGlobalsBag::getInstance()->set('mdht_conformance_server_enable', '1');

            $validator = new CdaValidateDocuments(new Client(), new NullLogger());

            $this->assertFalse($validator->externalValidatorEnabled);
            $this->assertNull($validator->externalValidatorUrl);
        }

        public function testSendsTheDocumentAndTranslatesTheFindings(): void
        {
            $handler = new MockHandler([new Response(200, [], <<<'JSON'
                {
                    "resultsMetaData": {"resultMetaData": [{"count": 1}]},
                    "ccdaValidationResults": [{
                        "type": "C-CDA MDHT Conformance Error",
                        "description": "SHALL contain exactly one [1..1] title",
                        "documentLineNumber": "12",
                        "xPath": "/ClinicalDocument"
                    }]
                }
                JSON)]);
            $validator = $this->validatorUsing($handler);

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame([
                'errorCount' => 1,
                'warningCount' => 0,
                'ignoredCount' => 0,
                'errors' => [[
                    'type' => 'error',
                    'test' => 'C-CDA MDHT Conformance Error',
                    'description' => 'SHALL contain exactly one [1..1] title',
                    'line' => '12',
                    'path' => '/ClinicalDocument',
                    'context' => 'C-CDA MDHT Conformance Error',
                    'xml' => '',
                ]],
            ], $results);

            $request = $handler->getLastRequest();
            $this->assertInstanceOf(RequestInterface::class, $request);
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame(self::VALIDATOR_URL, (string) $request->getUri());
            $this->assertSame('application/json', $request->getHeaderLine('Accept'));
            $this->assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));
            $body = (string) $request->getBody();
            foreach (
                [
                    'validationObjective' => 'C-CDA_IG_Plus_Vocab',
                    'referenceFileName' => 'noscenariofile',
                    'vocabularyConfig' => 'ccdaReferenceValidatorConfig',
                    'severityLevel' => 'ERROR',
                    'curesUpdate' => '1',
                ] as $field => $value
            ) {
                $this->assertMatchesRegularExpression('/name="' . $field . '"\R.*\R\R' . $value . '\R/', $body);
            }
            $this->assertStringContainsString('name="ccdaFile"; filename="ccda.xml"', $body);
            $this->assertStringContainsString('Content-Type: application/xhtml+xml', $body);
            $this->assertStringContainsString(self::DOCUMENT, $body);

            $options = $handler->getLastOptions();
            $this->assertFalse($options['allow_redirects']);
            $this->assertFalse($options['http_errors']);
            $this->assertSame(10, $options['connect_timeout']);
            $this->assertSame(120, $options['timeout']);
            $this->assertTrue($options['verify']);
        }

        public function testAMalformedServerSettingIsReportedAsOneFinding(): void
        {
            $handler = new MockHandler([new Response(200)]);
            $validator = $this->validatorUsing($handler);
            $validator->externalValidatorUrl = 'http://:8080/referenceccdaservice/';

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame($this->failureResults(0), $results);
            $this->assertCount(1, $handler);
        }

        public function testAServiceErrorInTheReplyIsReportedAsOneFinding(): void
        {
            $validator = $this->validatorUsing(new MockHandler([new Response(200, [], <<<'JSON'
                {
                    "resultsMetaData": {"serviceError": true, "serviceErrorMessage": "Vocabulary unavailable", "resultMetaData": [{"count": 0}]},
                    "ccdaValidationResults": []
                }
                JSON)]));

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame($this->failureResults(200), $results);
        }

        public function testAnHttpErrorIsReportedAsOneFinding(): void
        {
            $validator = $this->validatorUsing(new MockHandler([new Response(500, [], 'Internal Server Error')]));

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame($this->failureResults(500), $results);
        }

        public function testAnUnreachableValidatorIsReportedAsOneFinding(): void
        {
            $validator = $this->validatorUsing(new MockHandler([
                new ConnectException('Connection refused', new Request('POST', self::VALIDATOR_URL)),
            ]));

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame($this->failureResults(0), $results);
        }

        public function testAReplyThatIsNotJsonIsReportedAsOneFinding(): void
        {
            $validator = $this->validatorUsing(new MockHandler([new Response(200, [], '<html>Gateway error</html>')]));

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame($this->failureResults(200), $results);
        }

        public function testJsonWithoutAFindingsListIsReportedAsOneFinding(): void
        {
            $validator = $this->validatorUsing(new MockHandler([
                new Response(200, [], '{"resultsMetaData": {"resultMetaData": [{"count": 0}]}}'),
            ]));

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame($this->failureResults(200), $results);
        }

        public function testANumericLineNumberIsReportedAsText(): void
        {
            $validator = $this->validatorUsing(new MockHandler([new Response(200, [], <<<'JSON'
                {
                    "resultsMetaData": {"resultMetaData": [{"count": 1}]},
                    "ccdaValidationResults": [{"type": "t", "description": "d", "documentLineNumber": 7, "xPath": "/x"}]
                }
                JSON)]));

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame([
                'errorCount' => 1,
                'warningCount' => 0,
                'ignoredCount' => 0,
                'errors' => [[
                    'type' => 'error',
                    'test' => 't',
                    'description' => 'd',
                    'line' => '7',
                    'path' => '/x',
                    'context' => 't',
                    'xml' => '',
                ]],
            ], $results);
        }

        public function testAnEmptyDocumentIsNotSent(): void
        {
            $handler = new MockHandler([new Response(500)]);

            $results = $this->validatorUsing($handler)->ettValidateCcda('');

            $this->assertSame(['errorCount' => 0, 'warningCount' => 0, 'ignoredCount' => 0], $results);
            $this->assertCount(1, $handler);
        }

        public function testARedirectIsNotFollowed(): void
        {
            // The handler holds one reply, so following the redirect would fail on a second request.
            $handler = new MockHandler([new Response(302, ['Location' => 'https://elsewhere.example.test/'])]);
            $validator = $this->validatorUsing($handler);

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertCount(0, $handler);
            $this->assertSame($this->failureResults(302), $results);
        }

        /**
         * @return array{errorCount: 1, warningCount: 0, ignoredCount: 0, errors: list<array<string, string>>}
         */
        private function failureResults(int $status): array
        {
            return [
                'errorCount' => 1,
                'warningCount' => 0,
                'ignoredCount' => 0,
                'errors' => [$this->failureFinding($status)],
            ];
        }

        /**
         * @return array<string, string>
         */
        private function failureFinding(int $status): array
        {
            return [
                'type' => 'error',
                'test' => 'error',
                'description' => 'Validation Request failed Request Status: ' . $status,
                'line' => '',
                'path' => '',
                'context' => 'error',
                'xml' => '',
            ];
        }

        private function validatorUsing(MockHandler $handler): CdaValidateDocuments
        {
            $stack = HandlerStack::create($handler);
            $validator = new CdaValidateDocuments(new Client(['handler' => $stack, 'http_errors' => false]), new NullLogger());
            $validator->externalValidatorUrl = self::VALIDATOR_URL;
            return $validator;
        }
    }
}
