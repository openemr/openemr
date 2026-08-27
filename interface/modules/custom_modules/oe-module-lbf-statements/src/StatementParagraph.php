<?php

/**
 * Join sentences into one paragraph.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

final class StatementParagraph
{
    /**
     * @param list<array<string, mixed>> $actions
     */
    public static function fromActions(array $actions): string
    {
        $sentences = [];
        foreach ($actions as $action) {
            $s = trim(Values::asString($action['sentence'] ?? ''));
            if ($s === '') {
                continue;
            }
            if (preg_match('/[.!?]$/', $s) !== 1) {
                $s .= '.';
            }
            $sentences[] = $s;
        }
        return implode(' ', $sentences);
    }
}
