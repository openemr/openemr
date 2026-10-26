<?php

/**
 * Isolated tests for EmailQueueFilterBuilder.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Reports\Email;

use OpenEMR\Reports\Email\EmailQueueFilterBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailQueueFilterBuilderTest extends TestCase
{
    private EmailQueueFilterBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new EmailQueueFilterBuilder();
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string|null}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function filterValueProvider(): array
    {
        return [
            'missing key' => [[], 'search', null],
            'empty string' => [['search' => '  '], 'search', null],
            'trimmed string' => [['search' => '  hello  '], 'search', 'hello'],
            'int cast to string' => [['status' => 1], 'status', '1'],
            'array rejected' => [['search' => ['x']], 'search', null],
            'null rejected' => [['search' => null], 'search', null],
            'bool true' => [['flag' => true], 'flag', '1'],
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    #[DataProvider('filterValueProvider')]
    public function testGetFilterValue(array $filters, string $key, ?string $expected): void
    {
        $this->assertSame($expected, $this->builder->getFilterValue($filters, $key));
    }

    /**
     * @return array<string, array{mixed, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function normalizeIntProvider(): array
    {
        return [
            'int' => [42, 42],
            'float' => [3.9, 3],
            'numeric string' => ['17', 17],
            'non-numeric string' => ['abc', 0],
            'null' => [null, 0],
            'array' => [[1], 0],
        ];
    }

    #[DataProvider('normalizeIntProvider')]
    public function testNormalizeIntValue(mixed $value, int $expected): void
    {
        $this->assertSame($expected, $this->builder->normalizeIntValue($value));
    }

    public function testBuildFilterClauseEmpty(): void
    {
        [$where, $params] = $this->builder->buildFilterClause([]);
        $this->assertSame('', $where);
        $this->assertSame([], $params);
    }

    public function testBuildFilterClauseSearch(): void
    {
        [$where, $params] = $this->builder->buildFilterClause(['search' => 'alice']);
        $this->assertStringContainsString('recipient LIKE ?', $where);
        $this->assertSame(['%alice%', '%alice%', '%alice%'], $params);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function statusClauseProvider(): array
    {
        return [
            'sent' => ['sent', 'sent = 1 AND error = 0'],
            'pending' => ['pending', 'sent = 0 AND error = 0'],
            'failed' => ['failed', 'error = 1'],
        ];
    }

    #[DataProvider('statusClauseProvider')]
    public function testBuildFilterClauseStatus(string $status, string $expectedFragment): void
    {
        [$where, $params] = $this->builder->buildFilterClause(['status' => $status]);
        $this->assertStringStartsWith('WHERE ', $where);
        $this->assertStringContainsString($expectedFragment, $where);
        $this->assertSame([], $params);
    }

    public function testBuildFilterClauseIgnoresUnknownStatus(): void
    {
        [$where, $params] = $this->builder->buildFilterClause(['status' => 'nope']);
        $this->assertSame('', $where);
        $this->assertSame([], $params);
    }

    public function testBuildFilterClauseCombined(): void
    {
        [$where, $params] = $this->builder->buildFilterClause([
            'search' => 'bob',
            'status' => 'failed',
            'template_name' => 'welcome',
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        $this->assertStringContainsString('recipient LIKE ?', $where);
        $this->assertStringContainsString('error = 1', $where);
        $this->assertStringContainsString('template_name = ?', $where);
        $this->assertStringContainsString('datetime_queued >= ?', $where);
        $this->assertStringContainsString('datetime_queued <= ?', $where);
        $this->assertSame(
            ['%bob%', '%bob%', '%bob%', 'welcome', '2026-01-01 00:00:00', '2026-01-31 23:59:59'],
            $params
        );
    }
}
