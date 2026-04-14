<?php

/**
 * Custom PHPStan Rule to Forbid exit/die Inside catch or finally Blocks
 *
 * An `exit` or `die` inside a `catch` block swallows the caught exception
 * (losing stack trace and chained causes); when bare or given a string
 * argument it also terminates the process with status code 0, masking
 * failures. The same hazard applies inside a `finally` block. Sites should
 * re-throw (`throw;`) or wrap the exception so the global exception handler
 * can log and respond.
 *
 * Nested function/closure bodies are excluded: an `exit` defined inside a
 * closure declared within a catch frame only runs when that closure is
 * later invoked, which is a separate concern from the caught-exception
 * context.
 *
 * @package   OpenEMR
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<TryCatch>
 */
class ForbiddenExitInCatchFinallyRule implements Rule
{
    public function getNodeType(): string
    {
        return TryCatch::class;
    }

    /**
     * @param TryCatch $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($node->catches as $catch) {
            foreach ($this->findExits($catch->stmts) as $exit) {
                $errors[] = $this->error($exit, 'catch');
            }
        }
        if ($node->finally !== null) {
            foreach ($this->findExits($node->finally->stmts) as $exit) {
                $errors[] = $this->error($exit, 'finally');
            }
        }
        return $errors;
    }

    /**
     * @param Node[] $stmts
     * @return list<Exit_>
     */
    private function findExits(array $stmts): array
    {
        $visitor = new class extends NodeVisitorAbstract {
            /** @var list<Exit_> */
            public array $exits = [];

            public function enterNode(Node $node): ?int
            {
                if ($node instanceof FunctionLike) {
                    return NodeVisitor::DONT_TRAVERSE_CHILDREN;
                }
                if ($node instanceof Exit_) {
                    $this->exits[] = $node;
                }
                return null;
            }
        };
        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($stmts);
        return $visitor->exits;
    }

    private function error(Exit_ $exit, string $block): IdentifierRuleError
    {
        return RuleErrorBuilder::message(
            sprintf('exit/die inside a %s block swallows the caught exception and aborts the process.', $block)
        )
            ->identifier('openemr.exitInCatchOrFinally')
            ->line($exit->getStartLine())
            ->tip('Re-throw (throw;) or wrap the exception (throw new …Exception(…, previous: $e)) so the global exception handler can log and respond.')
            ->build();
    }
}
