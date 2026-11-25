<?php

/**
 * The single record a patient-file delete is asking to remove.
 *
 * deleter.php used to honor the first populated query parameter. A document
 * delete that also sent the patient id was handled as a patient deletion.
 * This value is that decision, made once, before any confirmation or write.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PatientFile\Deletion;

use Symfony\Component\HttpFoundation\InputBag;

final readonly class DeletionRequest
{
    private function __construct(
        public DeletionKind $kind,
        public string $id,
        public int $documentPid,
    ) {
    }

    /**
     * Parse the delete query into exactly one record.
     *
     * Returns null when the query names nothing, names more than one record,
     * or names a record with a value this page cannot use. document_pid is
     * context for a document, including 0 for a file that is not on a chart,
     * and is not itself a record.
     *
     * @param InputBag<string> $query
     */
    public static function parse(InputBag $query): ?self
    {
        /** @var array<string, mixed> $parameters */
        $parameters = $query->all();
        /** @var list<array{DeletionKind, string}> $found */
        $found = [];

        $patient = self::positiveInt($parameters, 'patient');
        if ($patient === false) {
            return null;
        }
        if ($patient !== null) {
            $found[] = [DeletionKind::Patient, (string) $patient];
        }

        $encounter = self::positiveInt($parameters, 'encounterid');
        if ($encounter === false) {
            return null;
        }
        if ($encounter !== null) {
            $found[] = [DeletionKind::Encounter, (string) $encounter];
        }

        $form = self::positiveInt($parameters, 'formid');
        if ($form === false) {
            return null;
        }
        if ($form !== null) {
            $found[] = [DeletionKind::Form, (string) $form];
        }

        $issue = self::issueList($parameters);
        if ($issue === false) {
            return null;
        }
        if ($issue !== null) {
            $found[] = [DeletionKind::Issue, $issue];
        }

        $document = self::positiveInt($parameters, 'document');
        if ($document === false) {
            return null;
        }
        $documentPid = self::documentContext($parameters);
        if ($documentPid === false) {
            return null;
        }
        if ($document !== null) {
            $found[] = [DeletionKind::Document, (string) $document];
        }

        $payment = self::token($parameters, 'payment');
        if ($payment === false) {
            return null;
        }
        if ($payment !== null) {
            $found[] = [DeletionKind::Payment, $payment];
        }

        $billing = self::token($parameters, 'billing');
        if ($billing === false) {
            return null;
        }
        if ($billing !== null) {
            $found[] = [DeletionKind::Billing, $billing];
        }

        $transaction = self::positiveInt($parameters, 'transaction');
        if ($transaction === false) {
            return null;
        }
        if ($transaction !== null) {
            $found[] = [DeletionKind::Transaction, (string) $transaction];
        }

        if (count($found) !== 1) {
            return null;
        }

        [$kind, $id] = $found[0];

        return new self(
            $kind,
            $id,
            $kind === DeletionKind::Document ? $documentPid : 0,
        );
    }

    /**
     * The query parameters that name this record and nothing else.
     *
     * The caller adds the CSRF token. A document also carries document_pid
     * so the file is checked against the chart it was opened from.
     *
     * @return array<string, string>
     */
    public function queryParameters(): array
    {
        return match ($this->kind) {
            DeletionKind::Patient => ['patient' => $this->id],
            DeletionKind::Encounter => ['encounterid' => $this->id],
            DeletionKind::Form => ['formid' => $this->id],
            DeletionKind::Issue => ['issue' => $this->id],
            DeletionKind::Document => [
                'document' => $this->id,
                'document_pid' => (string) $this->documentPid,
            ],
            DeletionKind::Payment => ['payment' => $this->id],
            DeletionKind::Billing => ['billing' => $this->id],
            DeletionKind::Transaction => ['transaction' => $this->id],
        };
    }

    /**
     * How many records this request removes. An issue list is the only kind
     * that can name more than one id.
     */
    public function recordCount(): int
    {
        if ($this->kind !== DeletionKind::Issue) {
            return 1;
        }

        return substr_count($this->id, ',') + 1;
    }

    /**
     * A positive integer parameter, null when absent, false when unusable.
     *
     * Zero and an empty value are absent. The old page treated those as "not
     * this record" because filter_input's failure value was collapsed with ?:.
     *
     * @param array<string, mixed> $parameters
     * @return int|false|null
     */
    private static function positiveInt(array $parameters, string $key): int|false|null
    {
        $raw = self::scalar($parameters, $key);
        if ($raw === null || $raw === '' || $raw === '0') {
            return null;
        }
        if ($raw === false || preg_match('/\A[1-9][0-9]{0,17}\z/', $raw) !== 1) {
            return false;
        }

        return (int) $raw;
    }

    /**
     * The chart a document was opened from. Zero is a file that is not filed
     * under a patient. A non-integer value refuses the whole request.
     *
     * @param array<string, mixed> $parameters
     * @return int|false
     */
    private static function documentContext(array $parameters): int|false
    {
        $raw = self::scalar($parameters, 'document_pid');
        if ($raw === null || $raw === '') {
            return 0;
        }
        if ($raw === false || preg_match('/\A(?:0|[1-9][0-9]{0,17})\z/', $raw) !== 1) {
            return false;
        }

        return (int) $raw;
    }

    /**
     * Comma-separated issue ids, null when absent, false when unusable.
     *
     * @param array<string, mixed> $parameters
     */
    private static function issueList(array $parameters): string|false|null
    {
        $raw = self::scalar($parameters, 'issue');
        if ($raw === null || $raw === '') {
            return null;
        }
        if ($raw === false || preg_match('/\A[1-9][0-9]{0,17}(?:,[1-9][0-9]{0,17})*\z/', $raw) !== 1) {
            return false;
        }

        return $raw;
    }

    /**
     * A payment or billing token, null when absent, false when not text.
     *
     * @param array<string, mixed> $parameters
     */
    private static function token(array $parameters, string $key): string|false|null
    {
        $raw = self::scalar($parameters, $key);
        if ($raw === null || $raw === '' || $raw === '0') {
            return null;
        }
        if ($raw === false) {
            return false;
        }

        return $raw;
    }

    /**
     * One query value as text. Null when the key is absent, false when the
     * value is not a string or an int.
     *
     * @param array<string, mixed> $parameters
     */
    private static function scalar(array $parameters, string $key): string|false|null
    {
        if (!array_key_exists($key, $parameters)) {
            return null;
        }
        $value = $parameters[$key];
        if (is_int($value)) {
            return (string) $value;
        }
        if (!is_string($value)) {
            return false;
        }

        return $value;
    }
}
