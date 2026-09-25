<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\Data;

use OpenEMR\Common\Http\QueryString;

function handBuilt(string $id, int $pid, mixed $raw, string $name, string $value): array
{
    return [
        'x.php?id=' . urlencode($id),
        'x.php?id=' . urlencode($id) . '&pid=' . $pid,
        "x.php?raw=$raw",
        '&' . urlencode($name) . '=' . urlencode($value),
        "&{$name}={$value}",
        'x.php?' . QueryString::build(['id' => $id]),
        'Are you sure? a=' . $id,
        'x.php?a=1&b=2',
        'x.php?' . $raw,
    ];
}

function continued(string $url, string $id): array
{
    return [
        $url . 'document_id=' . urlencode($id),
        'document_id=' . urlencode($id),
        'width=' . $id,
    ];
}

function javascriptAssignments(string $name, string $value): array
{
    return [
        'type_options_js[' . $name . ']=' . $value,
        'df.' . $name . '.value=' . $value,
    ];
}

function sqlColumnSuffix(string $table, string $type, string $id): string
{
    return 'DELETE FROM ' . $table . ' WHERE ' . $type . '_id=' . $id;
}

interface QuotingDb
{
    public function Quote(string $value): string;
}

function sqlQuotedCondition(QuotingDb $db, string $where, string $name): string
{
    return 'SELECT id FROM t ' . $where . 'name=' . $db->Quote($name);
}
