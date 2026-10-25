<?php

declare(strict_types=1);

namespace GeneralDermatology\Models;

use GeneralDermatology\Support\FormContext;
use OpenEMR\Common\Database\QueryUtils;

class MedicalData
{
    public function retrieveMedicalHistory(): string
    {
        $pid = FormContext::fromRuntime()->pid();
        $selectedItems = QueryUtils::querySingleRow(
            'SELECT * FROM `history_data` WHERE `pid` = ? ORDER BY id DESC LIMIT 1',
            [$pid]
        ) ?: [];

        return $this->getSkinDiseases((string) ($selectedItems['Check6'] ?? $selectedItems['check6'] ?? ''));
    }

    private function getSkinDiseases(string $selectedItems): string
    {
        $ids = array_values(array_filter(array_map('intval', explode('|', $selectedItems))));
        if ($ids === []) {
            return 'none';
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = QueryUtils::fetchRecords(
            "SELECT `title` FROM `lists` WHERE `type` = 'skin_disease' AND `id` IN ($placeholders)",
            $ids
        ) ?: [];

        $titles = [];
        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title !== '') {
                $titles[] = $title;
            }
        }

        return $titles !== [] ? implode(', ', $titles) : 'none';
    }
}
