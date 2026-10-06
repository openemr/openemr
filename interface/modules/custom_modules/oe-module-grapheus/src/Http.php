<?php

/**
 * JSON responses for the module's API.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace Exetazo\Grapheus;

final class Http
{
    /**
     * @param array<string, mixed> $body
     */
    public static function json(int $status, array $body): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($body);
        exit;
    }

    /**
     * Pass a Grapheus answer through to the browser.
     *
     * @param array{status: int, body: array<string, mixed>} $r
     */
    public static function relay(array $r): never
    {
        if ($r['status'] === 401) {
            self::json(401, ['ok' => false, 'code' => 'connect', 'error' => 'Your Grapheus connection has ended. Connect again.']);
        }
        self::json($r['status'] > 0 ? $r['status'] : 502, $r['body']);
    }
}
