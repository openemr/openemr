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
    use GuzzleHttp\Middleware;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;
    use OpenEMR\Services\Cda\CdaValidateDocuments;
    use PHPUnit\Framework\Attributes\Group;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\RequestInterface;

    #[Group('isolated')]
    class CdaValidateDocumentsEttTest extends TestCase
    {
        private const VALIDATOR_URL = 'https://ett.example.test/referenceccdaservice/';

        private const DOCUMENT = '<ClinicalDocument xmlns="urn:hl7-org:v3"/>';

        public function testSendsTheDocumentAndTranslatesTheFindings(): void
        {
            $requests = [];
            $validator = $this->validatorReplying(new Response(200, [], (string) json_encode([
                'resultsMetaData' => ['resultMetaData' => [['count' => 1]]],
                'ccdaValidationResults' => [[
                    'type' => 'C-CDA MDHT Conformance Error',
                    'description' => 'SHALL contain exactly one [1..1] title',
                    'documentLineNumber' => '12',
                    'xPath' => '/ClinicalDocument',
                ]],
            ])), $requests);

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame(1, $results['errorCount']);
            $this->assertSame([[
                'type' => 'error',
                'test' => 'C-CDA MDHT Conformance Error',
                'description' => 'SHALL contain exactly one [1..1] title',
                'line' => '12',
                'path' => '/ClinicalDocument',
                'context' => 'C-CDA MDHT Conformance Error',
                'xml' => '',
            ]], $results['errors']);

            $this->assertCount(1, $requests);
            $request = $requests[0];
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame(self::VALIDATOR_URL, (string) $request->getUri());
            $this->assertSame('application/json', $request->getHeaderLine('Accept'));
            $this->assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));
            $body = (string) $request->getBody();
            $this->assertStringContainsString('name="validationObjective"', $body);
            $this->assertStringContainsString('C-CDA_IG_Plus_Vocab', $body);
            $this->assertStringContainsString('name="ccdaFile"; filename="ccda.xml"', $body);
            $this->assertStringContainsString('Content-Type: application/xhtml+xml', $body);
            $this->assertStringContainsString(self::DOCUMENT, $body);
        }

        public function testAnHttpErrorIsReportedAsOneFinding(): void
        {
            $validator = $this->validatorReplying(new Response(500, [], 'Internal Server Error'));

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame(1, $results['errorCount']);
            $this->assertSame([$this->failureFinding(500)], $results['errors']);
        }

        public function testAnUnreachableValidatorIsReportedAsOneFinding(): void
        {
            $validator = $this->validatorReplying(
                new ConnectException('Connection refused', new Request('POST', self::VALIDATOR_URL))
            );

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame(1, $results['errorCount']);
            $this->assertSame([$this->failureFinding(0)], $results['errors']);
        }

        public function testAReplyThatIsNotJsonIsReportedAsOneFinding(): void
        {
            $validator = $this->validatorReplying(new Response(200, [], '<html>Gateway error</html>'));

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertSame(1, $results['errorCount']);
            $this->assertSame([$this->failureFinding(200)], $results['errors']);
        }

        public function testARedirectIsNotFollowed(): void
        {
            $requests = [];
            $validator = $this->validatorReplying(
                new Response(302, ['Location' => 'https://elsewhere.example.test/']),
                $requests
            );

            $results = $validator->ettValidateCcda(self::DOCUMENT);

            $this->assertCount(1, $requests);
            $this->assertSame([$this->failureFinding(302)], $results['errors']);
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

        /**
         * @param list<RequestInterface> $requests
         */
        private function validatorReplying(Response|ConnectException $reply, array &$requests = []): CdaValidateDocuments
        {
            $stack = HandlerStack::create(new MockHandler([$reply]));
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) use (&$requests): RequestInterface {
                $requests[] = $request;
                return $request;
            }));
            $validator = new CdaValidateDocuments(new Client(['handler' => $stack, 'http_errors' => false]));
            $validator->externalValidatorUrl = self::VALIDATOR_URL;
            return $validator;
        }
    }
}
