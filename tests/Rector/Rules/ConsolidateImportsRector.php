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
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;
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
 * An import block sitting *below* leading code is the third trigger, and the
 * only one that breaks at runtime rather than in a linter. PHP applies an
 * import to the code that follows it, not to the whole file, so shortening a
 * fully-qualified name above the `use` block -- exactly what name importing
 * does -- turns `\OpenEMR\Core\OEGlobalsBag::getInstance()` in a leading
 * `require_once` into an unresolvable `OEGlobalsBag` and the file fatals on
 * load. Moving the block to the top is always safe: imports are resolved at
 * compile time and have no runtime effect of their own.
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

        $count = count($uses);
        $contiguous = ($useIndexes[$count - 1] - $useIndexes[0] + 1) === $count;

        // `declare(strict_types=1)` must remain the very first statement, so
        // any leading declares stay ahead of the imports.
        $leading = [];
        while ($rest !== [] && $rest[0] instanceof Declare_) {
            $leading[] = array_shift($rest);
        }

        // A single run of imports that already opens the file (behind nothing
        // but declares) needs no reordering -- at most its docblock is
        // stranded below it. Behind a leading `declare` even that is fine:
        // the docblock sits at the top already, attached to that declare.
        if ($contiguous && $useIndexes[0] === count($leading)) {
            if ($leading !== []) {
                // PSR-12 puts the file header above `declare`, so a docblock
                // left on an import belongs on the declare, not on $uses[0].
                return $this->hoistDocblockOnto($leading[0], $uses, 0) ? $node : null;
            }

            if ($this->hoistDocblockFromLaterImport($uses)) {
                return $node;
            }

            return $this->hoistFileDocblock($rest, $uses[0]) ? $node : null;
        }

        // A single run of imports parked below leading code only has to move
        // when that leading code depends on one of those imports to resolve --
        // an unqualified name matching something in the block below it. That
        // is the shape name importing leaves behind and the one that fatals.
        //
        // A fully-qualified leading name resolves on its own and a bootstrap
        // `require_once` of a string literal names nothing at all; relocating
        // either would churn hundreds of files that are in no danger.
        // Non-contiguous imports are exempt from this test: that shape is the
        // phpcbf data-loss hazard and has to be gathered regardless of what
        // precedes it.
        if (
            $contiguous
            && !$this->dependsOnImports(array_slice($stmts, 0, $useIndexes[0]), $uses)
        ) {
            return null;
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
        if ($leading === [] && !$this->hoistDocblockFromLaterImport($uses)) {
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
     * Whether any statement resolves a class through one of the imports below
     * it. Only unqualified names qualify: `\Vendor\Alpha::run()` stands on its
     * own, `Alpha::run()` needs the `use Vendor\Alpha` that follows it -- and
     * PHP will not look ahead for it.
     *
     * Only class positions are inspected. A `ConstFetch` for `true` and a call
     * to a global function both carry a Name node too, and neither resolves
     * through an import.
     *
     * @param list<Node\Stmt>          $stmts
     * @param list<Use_|GroupUse>      $uses
     */
    private function dependsOnImports(array $stmts, array $uses): bool
    {
        $aliases = $this->importedAliases($uses);
        if ($aliases === []) {
            return false;
        }

        $found = (new NodeFinder())->findFirst(
            $stmts,
            function (Node $node) use ($aliases): bool {
                if ($node instanceof Catch_) {
                    foreach ($node->types as $type) {
                        if ($this->isImportedAlias($type, $aliases)) {
                            return true;
                        }
                    }

                    return false;
                }

                if (
                    $node instanceof New_
                    || $node instanceof StaticCall
                    || $node instanceof StaticPropertyFetch
                    || $node instanceof ClassConstFetch
                    || $node instanceof Instanceof_
                ) {
                    return $node->class instanceof Name
                        && $this->isImportedAlias($node->class, $aliases);
                }

                return false;
            }
        );

        return $found instanceof Node;
    }

    /**
     * Rector resolves every Name to its fully-qualified form before a rule
     * sees it and keeps the name as written under `originalName`. This test
     * is about what the source says, not what it means, so read the original
     * -- against the resolved name every leading class reference looks
     * qualified and nothing would ever match.
     *
     * @param array<string, true> $aliases
     */
    private function isImportedAlias(Name $name, array $aliases): bool
    {
        $written = $name->getAttribute('originalName');
        if ($written instanceof Name) {
            $name = $written;
        }

        return $name->isUnqualified()
            && isset($aliases[strtolower($name->toString())]);
    }

    /**
     * The short name each *class* import binds, keyed lowercase because PHP
     * resolves class names case-insensitively.
     *
     * Function and constant imports bind into their own symbol tables, so a
     * `use function Vendor\Alpha` does nothing for a leading `Alpha::run()` --
     * that still means global `\Alpha`, and relocating the block on its
     * account would be a false trigger.
     *
     * A mixed group (`use Vendor\{Beta, function Alpha}`) carries
     * TYPE_UNKNOWN on the group and the real type on each entry, so the entry
     * wins where it says anything; a group with no keyword at all leaves both
     * unknown, which means a plain class import.
     *
     * @param list<Use_|GroupUse> $uses
     *
     * @return array<string, true>
     */
    private function importedAliases(array $uses): array
    {
        $aliases = [];
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                $type = $useUse->type === Use_::TYPE_UNKNOWN ? $use->type : $useUse->type;
                if ($type === Use_::TYPE_FUNCTION || $type === Use_::TYPE_CONSTANT) {
                    continue;
                }

                $aliases[strtolower($useUse->getAlias()->toString())] = true;
            }
        }

        return $aliases;
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
     * Move a docblock stranded on a later import up onto the first one.
     *
     * Nothing documents a `use` statement, so a docblock attached to one is
     * the file header that ended up in the wrong place. phpcbf produces this:
     * once the header is a single block it sorts the imports alphabetically,
     * and a docblock riding on the import that sorts last lands in the middle
     * of the block, which `PSR12.Files.FileHeader` then rejects.
     *
     * An analyser annotation is the exception -- it is aimed at the import it
     * sits on and moving it would silently retarget the suppression.
     *
     * @param list<Use_|GroupUse> $uses
     *
     * @return bool whether a docblock moved
     */
    private function hoistDocblockFromLaterImport(array $uses): bool
    {
        return $this->hoistDocblockOnto($uses[0], $uses, 1);
    }

    /**
     * Move the first stranded docblock found among `$uses` (from `$offset` on)
     * onto `$target`, which must not already carry comments of its own.
     *
     * @param list<Use_|GroupUse> $uses
     */
    private function hoistDocblockOnto(Node\Stmt $target, array $uses, int $offset): bool
    {
        if ($target->getComments() !== []) {
            return false;
        }

        foreach (array_slice($uses, $offset) as $use) {
            $comments = $use->getComments();
            if ($comments === [] || !$comments[0] instanceof Doc) {
                continue;
            }

            if ($this->isAnalyserAnnotation($comments[0])) {
                continue;
            }

            $docblock = array_shift($comments);
            $use->setAttribute('comments', $comments);
            $target->setAttribute('comments', [$docblock]);

            return true;
        }

        return false;
    }

    private function isAnalyserAnnotation(Doc $docblock): bool
    {
        $text = strtolower($docblock->getText());

        return str_contains($text, '@phpstan-') || str_contains($text, '@psalm-');
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
