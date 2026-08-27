<?php

/**
 * Write lbf_data rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Common\Database\QueryUtils;

class LbfWriter
{
    /**
     * @param array<string, string> $newValues
     * @param array<string, string> $oldValues
     * @param list<array{field_id:string}> $actions
     */
    public function write(int $instanceId, array $newValues, array $oldValues, array $actions): void
    {
        $touched = [];
        foreach ($actions as $action) {
            $fid = $action['field_id'];
            if ($fid === '') {
                continue;
            }
            Identifiers::assertFieldId($fid);
            $touched[$fid] = true;
        }
        foreach (array_keys($touched) as $fieldId) {
            $value = $newValues[$fieldId] ?? '';
            $old = $oldValues[$fieldId] ?? null;
            if ($value === '') {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM lbf_data WHERE form_id = ? AND field_id = ?",
                    [$instanceId, $fieldId]
                );
                continue;
            }
            if ($old === null) {
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO lbf_data (form_id, field_id, field_value) VALUES (?,?,?)",
                    [$instanceId, $fieldId, $value]
                );
                continue;
            }
            QueryUtils::sqlStatementThrowException(
                "REPLACE INTO lbf_data SET field_value = ?, form_id = ?, field_id = ?",
                [$value, $instanceId, $fieldId]
            );
        }
    }
}
