<?php

/**
 * HTMLPurifier URI filter that keeps http(s) references on the OpenEMR
 * server's own host and rejects everything else. Relative URLs and
 * data: URIs are allowed through unchanged.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Pdf;

use HTMLPurifier_URIFilter;

class SameOriginUriFilter extends HTMLPurifier_URIFilter
{
    private readonly string $allowedHost;

    private readonly ?int $allowedPort;

    public function __construct(string $allowedHostSpec)
    {
        [$this->allowedHost, $this->allowedPort] = self::splitHostPort($allowedHostSpec);
        $this->name = 'OpenEMRSameOrigin';
        $this->always_load = true;
    }

    public function filter(&$uri, $config, $context)
    {
        $scheme = is_string($uri->scheme) ? $uri->scheme : '';
        $host = is_string($uri->host) ? $uri->host : '';

        if ($scheme === '') {
            // Relative URL may still carry an authority (e.g. protocol-relative "//attacker.example/x").
            // When a host is present it must match the allowed host.
            if ($host === '') {
                return true;
            }
            return $this->hostAndPortAllowed($host, $uri->port, 'http');
        }
        if (strcasecmp($scheme, 'data') === 0) {
            return true;
        }
        if (strcasecmp($scheme, 'http') !== 0 && strcasecmp($scheme, 'https') !== 0) {
            return false;
        }
        if ($host === '') {
            return true;
        }
        return $this->hostAndPortAllowed($host, $uri->port, $scheme);
    }

    private function hostAndPortAllowed(string $host, mixed $port, string $scheme): bool
    {
        if ($this->allowedHost === '' || strcasecmp($host, $this->allowedHost) !== 0) {
            return false;
        }
        $default = strcasecmp($scheme, 'https') === 0 ? 443 : 80;
        $uriPort = is_numeric($port) ? (int) $port : $default;
        $expected = $this->allowedPort ?? $default;
        return $uriPort === $expected;
    }

    /**
     * Split "host[:port]" or "[ipv6]:port" into [host, port|null].
     *
     * @return array{string, ?int}
     */
    public static function splitHostPort(string $spec): array
    {
        if (str_starts_with($spec, '[')) {
            $close = strpos($spec, ']');
            if ($close === false) {
                return [$spec, null];
            }
            $host = substr($spec, 0, $close + 1);
            $rest = substr($spec, $close + 1);
            if ($rest === '' || $rest[0] !== ':') {
                return [$host, null];
            }
            return [$host, (int) substr($rest, 1)];
        }
        $colon = strrpos($spec, ':');
        if ($colon === false) {
            return [$spec, null];
        }
        return [substr($spec, 0, $colon), (int) substr($spec, $colon + 1)];
    }
}
