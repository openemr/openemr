<?php

/**
 * Layout and field id checks.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

final class Identifiers
{
    public static function assertFieldId(string $id): string
    {
        if ($id === '' || preg_match('/^[A-Za-z0-9_-]+$/', $id) !== 1) {
            throw new \InvalidArgumentException('Invalid identifier');
        }
        return $id;
    }

    /**
     * list_options.option_id may contain spaces or '+'.
     */
    public static function isSafeStoredOptionId(string $id): bool
    {
        return $id !== '' && !str_contains($id, '..') && !str_contains($id, '/') && !str_contains($id, '\\');
    }
}
