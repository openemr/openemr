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
use PhpParser\Node\Stmt\Declare_;
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
        foreach ($stmts as $index => $stmt) {
            if ($this->isImport($stmt)) {
                $useIndexes[] = $index;
            }
        }

        // Nothing to gather, or the imports already form one contiguous run.
        $count = count($useIndexes);
        if ($count < 2 || ($useIndexes[$count - 1] - $useIndexes[0] + 1) === $count) {
            return null;
        }

        $uses = [];
        $rest = [];
        foreach ($stmts as $stmt) {
            if ($this->isImport($stmt)) {
                $uses[] = $stmt;
            } else {
                $rest[] = $stmt;
            }
        }

        // `declare(strict_types=1)` must remain the very first statement, so
        // any leading declares stay ahead of the imports.
        $leading = [];
        while ($rest !== [] && $rest[0] instanceof Declare_) {
            $leading[] = array_shift($rest);
        }

        // With a leading declare the file docblock already sits at the top of
        // the file, attached to that declare; only hoist when imports lead.
        if ($leading === []) {
            $this->hoistFileDocblock($rest, $uses[0]);
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
     */
    private function hoistFileDocblock(array $rest, Use_|GroupUse $firstUse): void
    {
        if ($rest === [] || $firstUse->getComments() !== []) {
            return;
        }

        $first = $rest[0];
        $comments = $first->getComments();
        if ($comments === [] || !$comments[0] instanceof Doc) {
            return;
        }

        $docblock = array_shift($comments);
        $first->setAttribute('comments', $comments);
        $firstUse->setAttribute('comments', [$docblock]);
    }
}
