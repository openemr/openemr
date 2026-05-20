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
 * with `parse_str()`, and merges the results. `post_max_size` still caps the
 * body PHP populates `php://input` with, so memory remains bounded.
 *
 * Use only for `application/x-www-form-urlencoded` POSTs. Multipart bodies
 * (file uploads) cannot be re-parsed this way: PHP consumes them before
 * `php://input` is populated. The constructor enforces this.
 *
 * Designed as an instance class so each request gets its own cache and tests
 * get fresh state per test method. Inject at the controller boundary; do not
 * call from inside services.
 *
 * `$_SERVER['CONTENT_TYPE']` is read intentionally as a transitional pattern
 * at the legacy-script boundary. Do not add further superglobal reads here.
 *
 * Limitation: the merge step uses `array_replace_recursive`, which does not
 * append-merge auto-indexed arrays (`foo[]=1&foo[]=2`). Both target endpoints
 * use explicit indices (`fld[1][id]`, `Payment3`), so this is not a concern
 * in practice; callers that emit `foo[]=…` patterns over the chunk boundary
 * should switch to explicit indices.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Alanis <progradedteam@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

class RawPostParser
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
    ) {
    }

    /**
     * Factory using the request's Content-Type header and the default
     * php://input stream — the production path. Tests should construct
     * directly.
     */
    public static function fromGlobals(): self
    {
        // filter_input(INPUT_SERVER, ...) is the project-blessed boundary
        // pattern for reading $_SERVER without tripping the forbidden-
        // globals PHPStan rule. Header is normalised by PHP to CONTENT_TYPE.
        $contentType = filter_input(INPUT_SERVER, 'CONTENT_TYPE') ?? '';
        return new self(new RawRequestBodyReader(), (string) $contentType);
    }

    /**
     * Returns the POST body re-parsed without max_input_vars truncation.
     * Result is cached per instance.
     *
     * @return array<array-key, mixed>
     *
     * @throws RawPostParserException if the request is multipart/form-data
     *                                (php://input is not populated by PHP),
     *                                or the raw body cannot be read.
     */
    public function parse(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        if (stripos($this->contentType, 'multipart/form-data') !== false) {
            throw new RawPostParserException(
                'RawPostParser cannot read multipart/form-data bodies; '
                . 'PHP consumes them before php://input is available.',
            );
        }

        $raw = $this->reader->read();
        if ($raw === '') {
            return $this->cache = [];
        }
        // Implementation note: chunked parse_str() below tolerates bodies past
        // the runtime max_input_vars ceiling. See class docblock for the full
        // rationale; the chunk size is computed at parse time so a runtime
        // ini change is honoured.

        $chunkSize = $this->chunkSize();
        $pairs = explode('&', $raw);
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
     * (`$_POST[...]`, `formData()`, `trimPost()`) see the full body. Returns
     * the parsed array so callers can also read it directly without making a
     * second `$_POST` access at the call site. Concentrating the mutation
     * inside this abstraction class is the project-sanctioned pattern for
     * the `openemr.forbiddenRequestGlobals` rule.
     *
     * @return array<array-key, mixed>
     *
     * @throws RawPostParserException — see parse().
     */
    public function applyToGlobals(): array
    {
        $parsed = $this->parse();
        $_POST = $parsed;
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
