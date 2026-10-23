<?php

/**
 * Collects SELECT audit events until the user action is finished.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final class SelectRequestSummary
{
    private const int TABLE_LIMIT = 30;

    /**
     * Clinical categories, most specific first. The summary uses the first
     * one that the action actually read.
     *
     * @var list<string>
     */
    private const array PRIORITY = [
        'patient-record',
        'scheduling',
        'order',
        'lab-order',
        'lab-results',
        'security-administration',
        'other',
    ];

    private int $count = 0;

    private int $failures = 0;

    /** @var array<string, true> */
    private array $tables = [];

    private bool $tablesTruncated = false;

    /** @var array<string, true> */
    private array $categories = [];

    /** @var array<string, true> */
    private array $details = [];

    private ?int $patientId = null;

    public function isEmpty(): bool
    {
        return $this->count === 0;
    }

    public function add(string $table, string $category, bool $success, ?int $patientId, string $detail = ''): void
    {
        $this->count++;
        if (!$success) {
            $this->failures++;
        }
        if ($category !== '') {
            $this->categories[$category] = true;
        }
        if ($detail !== '' && $detail !== $category) {
            $this->details[$detail] = true;
        }
        if ($table !== '' && !isset($this->tables[$table])) {
            if (count($this->tables) >= self::TABLE_LIMIT) {
                $this->tablesTruncated = true;
            } else {
                $this->tables[$table] = true;
            }
        }
        if ($this->patientId === null && $patientId !== null && $patientId > 0) {
            $this->patientId = $patientId;
        }
    }

    /**
     * The summary of what was collected, then the collector is empty again.
     */
    public function consume(string $label): SelectAuditSummary
    {
        $category = 'other';
        foreach (self::PRIORITY as $candidate) {
            if (isset($this->categories[$candidate])) {
                $category = $candidate;
                break;
            }
        }

        $tables = array_keys($this->tables);
        $tableList = $tables === [] ? 'none' : implode(',', $tables);
        if ($this->tablesTruncated) {
            $tableList .= ',...';
        }

        $comments = 'user action ' . $label
            . '; selects=' . $this->count
            . '; failed=' . $this->failures
            . '; tables=' . $tableList;
        if ($this->details !== []) {
            $comments .= '; data=' . implode(',', array_keys($this->details));
        }

        $summary = new SelectAuditSummary(
            event: $category . '-select',
            category: $category,
            comments: $comments,
            patientId: $this->patientId,
            success: $this->failures === 0 ? 1 : 0,
        );

        $this->count = 0;
        $this->failures = 0;
        $this->tables = [];
        $this->tablesTruncated = false;
        $this->categories = [];
        $this->details = [];
        $this->patientId = null;

        return $summary;
    }
}
