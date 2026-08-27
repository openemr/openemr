<?php

/**
 * Write the paragraph onto one LBF field.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

class StatementApplier
{
    /**
     * @param array<string, string> $current
     * @param list<array<string, mixed>> $actions
     * @return array<string, string>
     */
    public function apply(
        array $current,
        array $actions,
        string $mode,
        string $paragraphField,
        ?string $paragraphOverride = null
    ): array {
        Identifiers::assertFieldId($paragraphField);
        $paragraph = $paragraphOverride !== null
            ? $paragraphOverride
            : StatementParagraph::fromActions($actions);
        $existing = $current[$paragraphField] ?? '';
        $out = $current;
        if ($mode === 'overwrite') {
            $out[$paragraphField] = $paragraph;
        } elseif ($paragraph !== '') {
            $out[$paragraphField] = $this->appendText($existing, $paragraph);
        }
        return $out;
    }

    /**
     * @return list<array{field_id:string}>
     */
    public function writeActions(string $paragraphField): array
    {
        return [['field_id' => $paragraphField]];
    }

    private function appendText(string $existing, string $add): string
    {
        if ($existing === '') {
            return $add;
        }
        if (str_contains($existing, $add)) {
            return $existing;
        }
        return rtrim($existing) . ' ' . $add;
    }
}
