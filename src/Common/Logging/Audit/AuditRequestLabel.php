<?php

/**
 * A short description of the user action being audited.
 *
 * The label is the page or command, not the SQL. Secrets that ride on the
 * query string are left out.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

use OpenEMR\Common\Http\CurrentRequest;

final readonly class AuditRequestLabel
{
    private const int LIMIT = 500;

    /** @var list<string> */
    private const array SECRET_NAMES = [
        'authpass',
        'clearpass',
        'client_secret',
        'csrf_token_form',
        'new_pass',
        'old_pass',
        'pass',
        'password',
        'secret',
        'token',
    ];

    public function __construct(public string $label)
    {
    }

    public static function fromParts(?string $method, ?string $uri, ?string $script): self
    {
        if ($uri === null || $uri === '') {
            $base = ($script !== null && $script !== '') ? basename($script) : 'cli';

            return new self(self::cap('cli ' . $base));
        }

        $verb = ($method !== null && $method !== '') ? strtoupper($method) . ' ' : '';

        return new self(self::cap($verb . self::withoutSecrets($uri)));
    }

    /**
     * The current web request or command. This is the audit boundary, so the
     * server values are read here rather than passed through every caller.
     */
    public static function fromServer(): self
    {
        $request = CurrentRequest::get();

        return self::fromParts(
            $request->getMethod(),
            $request->getRequestUri(),
            $request->server->getString('SCRIPT_NAME'),
        );
    }

    private static function withoutSecrets(string $uri): string
    {
        $parts = parse_url($uri);
        if (!is_array($parts)) {
            return $uri;
        }

        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';
        if ($query === '') {
            return $path === '' ? $uri : $path;
        }

        $params = [];
        parse_str($query, $params);
        $clean = [];
        foreach ($params as $name => $value) {
            if (!is_string($name) || self::isSecret($name) || !is_scalar($value)) {
                continue;
            }
            $clean[$name] = (string) $value;
        }
        if ($clean === []) {
            return $path === '' ? '/' : $path;
        }

        return $path . '?' . http_build_query($clean);
    }

    private static function isSecret(string $name): bool
    {
        $lower = strtolower($name);
        if (in_array($lower, self::SECRET_NAMES, true)) {
            return true;
        }

        return str_contains($lower, 'password') || str_contains($lower, 'passwd');
    }

    private static function cap(string $label): string
    {
        if (strlen($label) <= self::LIMIT) {
            return $label;
        }

        return substr($label, 0, self::LIMIT - 3) . '...';
    }
}
