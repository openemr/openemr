<?php

/**
 * Rector rule to gather scattered use statements into a single block
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rector\Rules;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Legacy global-namespace files often carry a `use` block below a bootstrap
 * `require_once`. Name importing inserts new imports directly after `<?php`,
 * leaving two disjoint `use` regions with code between them.
 *
 * That shape is actively dangerous, not merely untidy: the phpcbf fixer for
 * SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses rewrites the whole
 * span from the first `use` to the last and silently deletes every statement in
 * between. On openemr/openemr that destroyed 26 files in one pass, including the
 * X-Frame-Options and CSP frame-ancestors headers in interface/login/login.php.
 * phpcs does not report the two-region shape, so nothing catches it.
 *
 * Collecting the imports into one contiguous block makes the sort a no-op
 * hazard-wise, and puts the file docblock back above them. Relative order is
 * preserved -- phpcbf sorts safely once there is a single region.
 *
 * Restoring the header is an independent trigger, not a step of the gathering
 * path. A file that had no imports at all gets exactly one region inserted
 * above its docblock, so there is nothing to gather and yet the header is
 * still inverted -- PSR12.Files.FileHeader reports "the file-level docblock
 * must follow the opening PHP tag in the file header" on its own.
 *
 * @see \OpenEMR\Rector\Rules\ConsolidateImportsRectorTest
 */
class ConsolidateImportsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Gather scattered use statements into a single contiguous block below the file docblock',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Vendor\Beta;

/**
 * File docblock.
 */

require_once __DIR__ . '/globals.php';

use Vendor\Alpha;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/**
 * File docblock.
 */

use Vendor\Beta;
use Vendor\Alpha;

require_once __DIR__ . '/globals.php';
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Vendor\Alpha;

/**
 * File docblock.
 */

require_once __DIR__ . '/globals.php';
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/**
 * File docblock.
 */
use Vendor\Alpha;

require_once __DIR__ . '/globals.php';
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class, Namespace_::class];
    }

    /**
     * @param FileNode|Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $stmts = $node->stmts;

        $useIndexes = [];
        $uses = [];
        $rest = [];
        foreach ($stmts as $index => $stmt) {
            if ($this->isImport($stmt)) {
                $useIndexes[] = $index;
                $uses[] = $stmt;
            } else {
                $rest[] = $stmt;
            }
        }

        if ($uses === []) {
            return null;
        }

        // The imports already form one contiguous run, so there is nothing to
        // gather -- but the docblock can still be stranded below them, and
        // only a hoist fixes that. Restrict it to imports that open the file:
        // behind a leading `declare` the docblock is already at the top,
        // attached to that declare.
        $count = count($uses);
        if (($useIndexes[$count - 1] - $useIndexes[0] + 1) === $count) {
            if ($useIndexes[0] !== 0) {
                return null;
            }

            return $this->hoistFileDocblock($rest, $uses[0]) ? $node : null;
        }

        // `declare(strict_types=1)` must remain the very first statement, so
        // any leading declares stay ahead of the imports.
        $leading = [];
        while ($rest !== [] && $rest[0] instanceof Declare_) {
            $leading[] = array_shift($rest);
        }

        // PSR-12 wants class, then function, then const imports grouped by
        // kind. Partition stably so relative order survives within each group.
        $byType = [];
        foreach ($uses as $use) {
            // A GroupUse without an explicit `function`/`const` keyword carries
            // TYPE_UNKNOWN; for ordering it is a plain class import.
            $type = $use->type === Use_::TYPE_UNKNOWN ? Use_::TYPE_NORMAL : $use->type;
            $byType[$type][] = $use;
        }
        ksort($byType);
        $uses = array_merge(...array_values($byType));

        // Hoist only after partitioning: grouping by kind can promote a later
        // import ahead of the one that led in statement order, and the docblock
        // has to land on whichever import actually ends up first.
        //
        // With a leading declare the file docblock already sits at the top of
        // the file, attached to that declare; only hoist when imports lead.
        if ($leading === []) {
            $this->hoistFileDocblock($rest, $uses[0]);
        }

        // Drop the original-node link so the printer re-emits the imports
        // fresh. Otherwise it preserves the blank line that used to separate
        // the two regions, and PSR12.Files.FileHeader still sees two blocks.
        // GroupUse keeps its link so multi-line groups are not reflowed onto
        // one line, which would be pure diff noise.
        foreach ($uses as $use) {
            if (!$use instanceof GroupUse) {
                $use->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
        }

        $node->stmts = [...$leading, ...$uses, ...$rest];

        return $node;
    }

    /**
     * `use A\B;` parses as Use_, `use A\{B, C};` as GroupUse. Both are imports
     * and both count toward the non-contiguous shape phpcbf mishandles, so
     * both have to be gathered.
     */
    private function isImport(Node $stmt): bool
    {
        return $stmt instanceof Use_ || $stmt instanceof GroupUse;
    }

    /**
     * Move the file-level docblock onto the first import so it stays at the top
     * of the file. Only a leading doc comment moves; trailing line comments
     * describe the statement they sit on and stay with it.
     *
     * @param list<Node\Stmt> $rest
     *
     * @return bool whether the docblock moved
     */
    private function hoistFileDocblock(array $rest, Use_|GroupUse $firstUse): bool
    {
        if ($rest === [] || $firstUse->getComments() !== []) {
            return false;
        }

        $first = $rest[0];
        $comments = $first->getComments();
        if ($comments === [] || !$comments[0] instanceof Doc) {
            return false;
        }

        if ($this->isCodeLevelDocblock($comments[0], $first)) {
            return false;
        }

        $docblock = array_shift($comments);
        $first->setAttribute('comments', $comments);
        $firstUse->setAttribute('comments', [$docblock]);

        return true;
    }

    /**
     * A docblock that documents the statement it sits on is not the file
     * header, and PSR12.Files.FileHeader agrees -- it stops treating a
     * docblock as file-level once the code it precedes declares a symbol of
     * its own or the block carries an `@var` annotation. Hoisting one of those
     * would retitle the file with a class's description and leave the
     * declaration undocumented.
     */
    private function isCodeLevelDocblock(Doc $docblock, Node\Stmt $stmt): bool
    {
        return $stmt instanceof ClassLike
            || $stmt instanceof Function_
            || str_contains(strtolower($docblock->getText()), '@var');
    }
}
