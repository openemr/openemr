<?php

/**
 * Master-only: scaffold the next sql/X_Y_Z-to-A_B_C_upgrade.sql file by
 * copying the comment-meta-language header from the most recent upgrade
 * file. The "from" version is read from the current version.php; the
 * "to" version comes from the conductor-supplied target version.
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
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

final readonly class SqlUpgradeSkeletonMutator implements MutatorInterface
{
    public function name(): string
    {
        return 'sql/<from>-to-<to>_upgrade.sql (scaffold)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $sqlDir = $context->projectDir . '/sql';
        $fromVersion = $this->readVersionPhp($context->projectDir);
        $toVersion = $context->versionString();
        if ($fromVersion === $toVersion) {
            // Master is already at the target version; nothing to scaffold.
            return MutatorResult::noop();
        }

        $skeletonName = sprintf(
            '%s-to-%s_upgrade.sql',
            str_replace('.', '_', $fromVersion),
            str_replace('.', '_', $toVersion),
        );
        $skeletonPath = $sqlDir . '/' . $skeletonName;
        if (file_exists($skeletonPath)) {
            return MutatorResult::noop();
        }

        $sourcePath = $this->latestUpgradeFile($sqlDir);
        if ($sourcePath === null) {
            throw new \RuntimeException('No existing sql/*-to-*_upgrade.sql to copy header from');
        }
        $sourceContents = file_get_contents($sourcePath);
        if ($sourceContents === false) {
            throw new \RuntimeException('Cannot read ' . $sourcePath);
        }
        $headerOnly = $this->extractCommentHeader($sourceContents);
        if (file_put_contents($skeletonPath, $headerOnly) === false) {
            throw new \RuntimeException('Cannot write ' . $skeletonPath);
        }
        return new MutatorResult(['sql/' . $skeletonName]);
    }

    private function readVersionPhp(string $projectDir): string
    {
        $contents = file_get_contents($projectDir . '/version.php');
        if ($contents === false) {
            throw new \RuntimeException('Cannot read version.php');
        }
        $ast = (new ParserFactory())->createForNewestSupportedVersion()->parse($contents);
        if ($ast === null) {
            throw new \RuntimeException('Cannot parse version.php');
        }
        $found = ['v_major' => null, 'v_minor' => null, 'v_patch' => null];
        foreach ($ast as $stmt) {
            if (!$stmt instanceof Stmt\Expression || !$stmt->expr instanceof Expr\Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (
                !$assign->var instanceof Expr\Variable
                || !is_string($assign->var->name)
                || !array_key_exists($assign->var->name, $found)
                || !$assign->expr instanceof Scalar\String_
            ) {
                continue;
            }
            $found[$assign->var->name] = $assign->expr->value;
        }
        foreach ($found as $name => $value) {
            if ($value === null) {
                throw new \RuntimeException('version.php missing $' . $name);
            }
        }
        return $found['v_major'] . '.' . $found['v_minor'] . '.' . $found['v_patch'];
    }

    private function latestUpgradeFile(string $sqlDir): ?string
    {
        $files = glob($sqlDir . '/*-to-*_upgrade.sql');
        if ($files === false || $files === []) {
            return null;
        }
        // Sort by the "to" version using version_compare so 8_0_10 sorts
        // after 8_0_9. Lexical sort would silently bite when patch hits
        // double digits.
        usort($files, fn(string $a, string $b): int => version_compare(
            $this->toVersionFromFilename($a),
            $this->toVersionFromFilename($b),
        ));
        return $files[count($files) - 1];
    }

    private function toVersionFromFilename(string $path): string
    {
        $name = basename($path);
        if (preg_match('/-to-(\d+_\d+_\d+)_upgrade\.sql$/', $name, $m) !== 1) {
            // glob already matched *-to-*_upgrade.sql, but the version
            // segments might not be numeric; fall back to a version that
            // sorts last so a malformed filename doesn't accidentally
            // become "latest".
            return '0.0.0';
        }
        return str_replace('_', '.', $m[1]);
    }

    /**
     * Keep only the leading run of comment-meta-language `--` lines and
     * blank lines. The first non-comment, non-blank line ends the
     * header; everything after it is real upgrade SQL from the source
     * release that we don't want to copy.
     */
    private function extractCommentHeader(string $contents): string
    {
        $lines = preg_split("/\r?\n/", $contents);
        if ($lines === false) {
            return $contents;
        }
        $kept = [];
        foreach ($lines as $line) {
            if ($line === '' || str_starts_with($line, '--')) {
                $kept[] = $line;
                continue;
            }
            break;
        }
        return implode("\n", $kept) . "\n";
    }
}
