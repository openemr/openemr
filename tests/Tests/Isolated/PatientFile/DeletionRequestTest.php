<?php

/**
 * A patient-file delete names exactly one record.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFile;

use OpenEMR\PatientFile\Deletion\DeletionKind;
use OpenEMR\PatientFile\Deletion\DeletionRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;

final class DeletionRequestTest extends TestCase
{
    /**
     * One well-formed record is accepted, and the confirmation query names only that record.
     */
    #[DataProvider('singleRecordProvider')]
    public function testOneRecordIsAccepted(string $key, string $value, DeletionKind $kind, int $documentPid): void
    {
        $query = [$key => $value];
        if ($kind === DeletionKind::Document) {
            $query['document_pid'] = (string) $documentPid;
        }

        $parsed = DeletionRequest::parse(self::query($query));

        $this->assertInstanceOf(DeletionRequest::class, $parsed);
        $this->assertSame($kind, $parsed->kind);
        $this->assertSame($value, $parsed->id);
        $this->assertSame($documentPid, $parsed->documentPid);
        $this->assertSame($query, $parsed->queryParameters());
        $this->assertSame(1, $parsed->recordCount());
    }

    /**
     * A zero id is absent, so a document filed under no chart is still one record.
     */
    public function testAnUnfiledDocumentKeepsAZeroChart(): void
    {
        $parsed = DeletionRequest::parse(self::query([
            'patient' => '0',
            'document' => '15',
            'document_pid' => '0',
        ]));

        $this->assertInstanceOf(DeletionRequest::class, $parsed);
        $this->assertSame(DeletionKind::Document, $parsed->kind);
        $this->assertSame('15', $parsed->id);
        $this->assertSame(0, $parsed->documentPid);
        $this->assertSame(
            ['document' => '15', 'document_pid' => '0'],
            $parsed->queryParameters(),
        );
    }

    /**
     * An issue list is one request that removes each listed issue.
     */
    public function testAnIssueListCountsEachId(): void
    {
        $parsed = DeletionRequest::parse(self::query(['issue' => '4,5,9']));

        $this->assertInstanceOf(DeletionRequest::class, $parsed);
        $this->assertSame(DeletionKind::Issue, $parsed->kind);
        $this->assertSame(3, $parsed->recordCount());
        $this->assertArrayNotHasKey('patient', $parsed->queryParameters());
    }

    /**
     * The document screen used to send the chart id in the patient parameter.
     * That address names two records and must not choose either of them.
     *
     * @param InputBag<string> $query
     */
    #[DataProvider('refusedQueryProvider')]
    public function testAMixedOrUnusableQueryIsRefused(InputBag $query): void
    {
        $this->assertNull(DeletionRequest::parse($query));
    }

    /**
     * @return array<string, array{string, string, DeletionKind, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function singleRecordProvider(): array
    {
        return [
            'patient' => ['patient', '8', DeletionKind::Patient, 0],
            'encounter' => ['encounterid', '44', DeletionKind::Encounter, 0],
            'form' => ['formid', '3', DeletionKind::Form, 0],
            'issue' => ['issue', '12', DeletionKind::Issue, 0],
            'document' => ['document', '90', DeletionKind::Document, 8],
            'payment' => ['payment', '8.20261124120000.2', DeletionKind::Payment, 0],
            'billing' => ['billing', '8.44', DeletionKind::Billing, 0],
            'transaction' => ['transaction', '6', DeletionKind::Transaction, 0],
        ];
    }

    /**
     * @return array<string, array{InputBag<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedQueryProvider(): array
    {
        return [
            'nothing' => [self::query([])],
            'zeros only' => [self::query([
                'patient' => '0',
                'document' => '0',
                'document_pid' => '0',
            ])],
            'patient and document' => [self::query([
                'patient' => '8',
                'document' => '90',
                'document_pid' => '8',
            ])],
            'document and encounter' => [self::query([
                'document' => '90',
                'document_pid' => '8',
                'encounterid' => '44',
            ])],
            'document and form' => [self::query(['document' => '90', 'formid' => '3'])],
            'document and issue' => [self::query(['document' => '90', 'issue' => '4,5'])],
            'document and payment' => [self::query(['document' => '90', 'payment' => '8.20261124120000.2'])],
            'document and billing' => [self::query(['document' => '90', 'billing' => '8.44'])],
            'document and transaction' => [self::query(['document' => '90', 'transaction' => '6'])],
            'patient and encounter' => [self::query(['patient' => '8', 'encounterid' => '44'])],
            'malformed patient' => [self::query(['patient' => '8abc'])],
            'malformed patient beside a document' => [self::query(['patient' => '1.5', 'document' => '90'])],
            'negative document context' => [self::query(['document' => '90', 'document_pid' => '-1'])],
            'word as document context' => [self::query(['document' => '90', 'document_pid' => 'chart'])],
            'issue zero' => [self::query(['issue' => '0'])],
            'issue with a gap' => [self::query(['issue' => '4,,5'])],
        ];
    }

    /**
     * Build a query bag from text parameters.
     *
     * @param array<string, string> $parameters
     * @return InputBag<string>
     */
    private static function query(array $parameters): InputBag
    {
        /** @var InputBag<string> $bag */
        $bag = new InputBag($parameters);

        return $bag;
    }
}
