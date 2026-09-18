<?php

/**
 * Switch the `allow_debug_language` default from '1' to '0' in
 * library/globals.inc.php so production releases don't ship with the
 * dummy/debug language enabled. Walks the AST to find the array entry
 * keyed 'allow_debug_language' and updates the third element of its
 * value array (the default).
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
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\NodeFinder;

final readonly class GlobalsIncMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'library/globals.inc.php';
    private const TARGET_KEY = 'allow_debug_language';
    private const DEFAULT_INDEX = 2;
    private const NEW_DEFAULT = '0';

    public function name(): string
    {
        return 'library/globals.inc.php (allow_debug_language → 0)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $editor = new AstSourceEditor();
        $updated = $editor->edit($contents, function (array $ast): array {
            $finder = new NodeFinder();
            // Find every array entry whose key is the literal 'allow_debug_language'.
            $entries = $finder->find($ast, fn(Node $node): bool => $node instanceof ArrayItem
                && $node->key instanceof Scalar\String_
                && $node->key->value === self::TARGET_KEY);
            if ($entries === []) {
                throw new \RuntimeException(
                    "Did not find an entry keyed '" . self::TARGET_KEY . "' in globals.inc.php",
                );
            }
            $ranges = [];
            foreach ($entries as $entry) {
                if (!$entry instanceof ArrayItem || !$entry->value instanceof Expr\Array_) {
                    continue;
                }
                $defaultItem = $entry->value->items[self::DEFAULT_INDEX] ?? null;
                if (!$defaultItem instanceof ArrayItem || !$defaultItem->value instanceof Scalar\String_) {
                    continue;
                }
                $literal = $defaultItem->value;
                if ($literal->value === self::NEW_DEFAULT) {
                    continue;
                }
                $ranges[] = [
                    $literal->getStartFilePos(),
                    $literal->getEndFilePos(),
                    "'" . self::NEW_DEFAULT . "'",
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
