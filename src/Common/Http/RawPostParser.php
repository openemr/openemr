<?php

/**
 * RawPostParser — re-parses the raw POST body, sidestepping the
 * `max_input_vars` truncation that drops fields from `$_POST` when a form
 * posts more variables than the PHP configured ceiling.
 *
 * The browser sends the full body for an `application/x-www-form-urlencoded`
 * POST; PHP just refuses to expose more than `max_input_vars` of it through
 * `$_POST`. `parse_str()` is bound by the same limit (PHP 7+), so the parser
 * splits the body into chunks below the configured ceiling, parses each chunk
 * with `parse_str()`, and merges the results.
 *
 * `post_max_size` still caps how much of the body PHP ever exposes via
 * `php://input`. If the body exceeds that ceiling PHP silently discards the
 * tail and `parse_str()` would happily parse the truncated bytes; the parser
 * compares the bytes it read against the request's `Content-Length` and
 * throws when they diverge, so post_max_size truncation never reaches the
 * caller as a quiet partial result.
 *
 * Use only for `application/x-www-form-urlencoded` POSTs. Multipart bodies
 * (file uploads) cannot be re-parsed this way: PHP consumes them before
 * `php://input` is populated. The constructor enforces this.
 *
 * Designed as an instance class so each request gets its own cache and tests
 * get fresh state per test method. Inject at the controller boundary; do not
 * call from inside services.
 *
 * `$_SERVER['CONTENT_TYPE']` and `$_SERVER['CONTENT_LENGTH']` are read
 * intentionally as a transitional pattern at the legacy-script boundary. Do
 * not add further superglobal reads here.
 *
 * Limitation: the merge step uses `array_replace_recursive`, which does not
 * append-merge bracket-bare auto-indexed arrays (`foo[]=1&foo[]=2`). Rather
 * than silently produce a different shape from native `$_POST` parsing for
 * such inputs, the parser rejects them up front; callers must use explicit
 * indices (`foo[0]`, `foo[1]`, ...) instead.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Alanis <progradedteam@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

final class RawPostParser
{
    /**
     * Fraction of `max_input_vars` used as the parse_str() chunk size. Leaves
     * headroom for parser overhead and prevents the per-call limit from
     * tripping at the exact boundary.
     */
    private const CHUNK_SAFETY_FACTOR = 0.5;

    /**
     * Floor for the per-call chunk size. Honoured even when
     * `max_input_vars` is configured pathologically low; below this the
     * parser would issue thousands of tiny parse_str() calls.
     */
    private const MIN_CHUNK_SIZE = 50;

    /**
     * Fallback chunk size when `ini_get('max_input_vars')` is empty/unset.
     * Matches PHP's compiled-in default.
     */
    private const DEFAULT_MAX_INPUT_VARS = 1000;

    /**
     * Cached parsed result. `array-key` because `parse_str()` coerces numeric
     * top-level keys to integers (e.g. a body containing `42=foo` yields
     * `[42 => 'foo']`), matching the shape `$_POST` would have had.
     *
     * @var array<array-key, mixed>|null
     */
    private ?array $cache = null;

    public function __construct(
        private readonly RawRequestBodyReader $reader,
        private readonly string $contentType,
        private readonly ?int $contentLength = null,
    ) {
    }

    /**
     * Factory using the request's Content-Type / Content-Length headers and
     * the default php://input stream — the production path. Tests should
     * construct directly.
     */
    public static function fromGlobals(): self
    {
        // filter_input(INPUT_SERVER, ...) is the project-blessed boundary
        // pattern for reading $_SERVER without tripping the forbidden-
        // globals PHPStan rule. Header is normalised by PHP to CONTENT_TYPE.
        $contentType = filter_input(INPUT_SERVER, 'CONTENT_TYPE') ?? '';
        $contentLength = filter_input(INPUT_SERVER, 'CONTENT_LENGTH', FILTER_VALIDATE_INT);
        return new self(
            new RawRequestBodyReader(),
            (string) $contentType,
            is_int($contentLength) ? $contentLength : null,
        );
    }

    /**
     * Returns the POST body re-parsed without max_input_vars truncation.
     * Result is cached per instance.
     *
     * @return array<array-key, mixed>
     *
     * @throws RawPostParserException if the request is multipart/form-data
     *                                (php://input is not populated by PHP),
     *                                if the raw body cannot be read, if the
     *                                bytes read are shorter than the request's
     *                                Content-Length (post_max_size truncation),
     *                                or if the body contains a bracket-bare
     *                                auto-indexed key.
     */
    public function parse(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        if (stripos($this->contentType, 'multipart/form-data') !== false) {
            throw new RawPostParserException(
                'RawPostParser cannot read multipart/form-data bodies; PHP consumes them before php://input is available.',
            );
        }

        $raw = $this->reader->read();

        // post_max_size truncation guard. PHP silently discards body bytes
        // past post_max_size before php://input is populated; parse_str
        // would then happily parse the truncated bytes and we'd produce a
        // partial-but-plausible $_POST. Compare bytes read against the
        // request's advertised length so the failure surfaces loudly.
        if ($this->contentLength !== null && strlen($raw) < $this->contentLength) {
            throw new RawPostParserException(
                sprintf(
                    'Raw body truncated: read %d bytes, Content-Length %d (likely exceeds post_max_size)',
                    strlen($raw),
                    $this->contentLength,
                ),
            );
        }

        if ($raw === '') {
            return $this->cache = [];
        }

        $pairs = explode('&', $raw);

        // Reject bracket-bare auto-indexed keys (foo[]=1&foo[]=2) up front
        // rather than silently produce a different shape from native $_POST
        // via array_replace_recursive. Forces callers onto explicit indices.
        foreach ($pairs as $pair) {
            if ($pair === '') {
                continue;
            }
            $keyPart = explode('=', $pair, 2)[0];
            if (str_contains($keyPart, '[]')) {
                throw new RawPostParserException(
                    'RawPostParser does not support bracket-bare auto-indexed keys (e.g. foo[]=1). Use explicit indices (foo[0], foo[1], ...).',
                );
            }
        }

        $chunkSize = $this->chunkSize();
        $parsed = [];
        foreach (array_chunk($pairs, $chunkSize) as $chunk) {
            $chunkParsed = [];
            parse_str(implode('&', $chunk), $chunkParsed);
            $parsed = array_replace_recursive($parsed, $chunkParsed);
        }
        return $this->cache = $parsed;
    }

    /**
     * Boundary helper for legacy scripts: parse the raw body and write the
     * result into `$_POST` so the downstream code's existing reads
     * (`$_POST[...]`, `formData()`, `trimPost()`) see the full body. Also
     * rebuilds `$_REQUEST` from `$_GET`, the new `$_POST`, and `$_COOKIE`
     * because PHP builds `$_REQUEST` once at request start from the
     * (truncated) `$_POST` and never re-derives it; legacy handlers that
     * read loop bounds or money-disposition fields out of `$_REQUEST` would
     * otherwise still see the pre-truncation values.
     *
     * Refuses to overwrite a non-empty `$_POST` with an empty parsed array,
     * which would happen if `php://input` was already consumed by another
     * layer in the request lifecycle. That branch returns the existing
     * `$_POST` unchanged rather than wiping the user's submission.
     *
     * @return array<array-key, mixed>
     *
     * @throws RawPostParserException — see parse().
     */
    public function applyToGlobals(): array
    {
        $parsed = $this->parse();

        if ($parsed === [] && $_POST !== []) {
            // php://input was already consumed by some earlier layer and
            // we'd be silently clobbering PHP's truncated $_POST with
            // nothing. That is strictly worse than the truncation bug; bail.
            return $_POST;
        }

        $_POST = $parsed;

        // Re-derive $_REQUEST from the new $_POST plus existing $_GET and
        // $_COOKIE. PHP's request_order ini setting governs the order; the
        // default "GP" is honoured by walking GET first, then POST. We
        // intentionally do not include $_COOKIE here unless request_order
        // mentions 'C', matching PHP's own builder.
        $order = ini_get('request_order') ?: ini_get('variables_order') ?: 'GP';
        $request = [];
        for ($i = 0, $n = strlen($order); $i < $n; $i++) {
            $source = match ($order[$i]) {
                'G' => $_GET,
                'P' => $_POST,
                'C' => $_COOKIE,
                default => null,
            };
            if (is_array($source)) {
                $request = array_replace($request, $source);
            }
        }
        $_REQUEST = $request;

        return $parsed;
    }

    /**
     * Returns a chunk size for parse_str() that stays below the runtime
     * `max_input_vars` ceiling. Reads ini at parse time so tests and
     * production both see the live configuration.
     *
     * @return positive-int
     */
    private function chunkSize(): int
    {
        $configured = (int) ini_get('max_input_vars');
        if ($configured <= 0) {
            $configured = self::DEFAULT_MAX_INPUT_VARS;
        }
        $scaled = (int) ($configured * self::CHUNK_SAFETY_FACTOR);
        return max(self::MIN_CHUNK_SIZE, $scaled);
    }
}
