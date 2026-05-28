<?php

/**
 * Prepend (or replace, on re-run) the top-of-file section for the
 * target version in repo-root CHANGELOG.md. Drives a real diff in the
 * release-prep PR even when version.php and the docker pin are already
 * at target — without this, all-no-op mutator runs open a zero-diff PR
 * which GitHub immediately closes and the conductor stalls without a
 * tag (see openemr/openemr#12291).
 *
 * The mutator stays pure: the conductor workflow gathers PR data via
 * `gh`, encodes it as a Manifest on the MutatorContext, and the
 * mutator only renders. If no manifest is supplied, the mutator
 * no-ops — the workflow opts in.
 *
 * Idempotence: the renderer emits byte-stable output, so re-running
 * with the same manifest produces no diff. Re-running with a changed
 * manifest (new PRs merged between conductor runs) replaces the
 * existing top section in place rather than stacking duplicates.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use OpenEMR\Common\Command\ReleasePrep\ReleaseNotes\Renderer;

final readonly class ChangelogMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'CHANGELOG.md';
    private const HEADER_LINE = "# CHANGELOG.md\n";

    public function __construct(private Renderer $renderer = new Renderer())
    {
    }

    public function name(): string
    {
        return 'CHANGELOG.md (prepend release section)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $manifest = $context->releaseNotes;
        if ($manifest === null) {
            return new MutatorResult([], ['CHANGELOG.md: no release-notes manifest supplied; skipped']);
        }
        if ($manifest->version !== $context->versionString()) {
            throw new \RuntimeException(sprintf(
                'CHANGELOG.md: manifest version %s does not match target version %s',
                $manifest->version,
                $context->versionString(),
            ));
        }

        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $rendered = $this->renderer->render($manifest);
        $updated = $this->replaceOrPrepend($contents, $manifest->version, $rendered);
        if ($updated === $contents) {
            return MutatorResult::noop();
        }
        if (file_put_contents($path, $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }

    private function replaceOrPrepend(string $contents, string $version, string $rendered): string
    {
        $headerPos = strpos($contents, self::HEADER_LINE);
        if ($headerPos !== 0) {
            throw new \RuntimeException(
                'CHANGELOG.md: expected to start with "# CHANGELOG.md" header line',
            );
        }
        $afterHeader = strlen(self::HEADER_LINE);

        // Skip any blank lines between the header and the first section so
        // the rendered block lands flush against a single blank line.
        $cursor = $afterHeader;
        $length = strlen($contents);
        while ($cursor < $length && $contents[$cursor] === "\n") {
            $cursor++;
        }

        $tail = substr($contents, $cursor);
        $expectedHeading = sprintf('## [%s]', $version);
        if (str_starts_with($tail, $expectedHeading)) {
            // Replace existing top section: trim from its heading up to the
            // next `## ` heading (or EOF).
            $nextHeadingPos = strpos($tail, "\n## ", strlen($expectedHeading));
            $tail = $nextHeadingPos === false
                ? ''
                : substr($tail, $nextHeadingPos + 1);
        }

        return self::HEADER_LINE . "\n" . $rendered . ($tail === '' ? '' : "\n" . $tail);
    }
}
