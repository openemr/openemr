<?php

/**
 * On rel-* push: normalise version.php to the target release version
 * and strip `-dev` from $v_tag. Drives the actual `OpenEMR <X.Y.Z>`
 * string the application reports.
 *
 * Uses AST traversal (nikic/php-parser) rather than regex so a typo or
 * formatting drift in version.php would surface as a parse error
 * rather than a silent regex miss.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\AstSourceEditor;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

final readonly class VersionPhpMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'version.php';

    public function name(): string
    {
        return 'version.php (strip -dev)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $targets = [
            'v_major' => (string) $context->major,
            'v_minor' => (string) $context->minor,
            'v_patch' => (string) $context->patch,
            'v_tag' => '',
        ];

        $editor = new AstSourceEditor();
        $updated = $editor->edit($contents, function (array $ast) use ($targets): array {
            $ranges = [];
            foreach ($ast as $stmt) {
                if (!$stmt instanceof Stmt\Expression || !$stmt->expr instanceof Expr\Assign) {
                    continue;
                }
                $assign = $stmt->expr;
                if (!$assign->var instanceof Expr\Variable || !is_string($assign->var->name)) {
                    continue;
                }
                if (!array_key_exists($assign->var->name, $targets) || !$assign->expr instanceof Scalar\String_) {
                    continue;
                }
                $ranges[] = [
                    $assign->expr->getStartFilePos(),
                    $assign->expr->getEndFilePos(),
                    "'" . $targets[$assign->var->name] . "'",
                ];
            }
            return $ranges;
        });

        if ($updated === $contents) {
            return MutatorResult::noop();
        }
        if (file_put_contents($path, $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }
}
