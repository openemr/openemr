<?php

/**
 * Custom PHPStan Rule to Forbid catch Blocks That Would Suppress Specific Types
 *
 * Maintains a list of throwable types that should never be suppressed by a
 * `catch` block. A catch declaration is flagged whenever its declared type
 * would actually catch one of the forbidden types at runtime — i.e. the
 * declared type and a forbidden type are related by inheritance in either
 * direction. Examples with `\Error` in the list:
 *   - `catch (\Throwable $e)` → flagged (Throwable would catch Error)
 *   - `catch (\Error $e)` → flagged (exact match)
 *   - `catch (\TypeError $e)` → flagged (TypeError is-a Error)
 *   - `catch (\RuntimeException $e)` → not flagged (no relation to Error)
 *
 * Extend {@see self::FORBIDDEN} to add more types the project should not
 * suppress.
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
use PhpParser\Node\Expr\Throw_ as ThrowExpr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Expression as ExpressionStmt;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Catch_>
 */
class ForbiddenCatchTypeRule implements Rule
{
    /**
     * Types that catch blocks must not suppress. A catch is flagged when its
     * declared type and any entry here share an inheritance relationship in
     * either direction (catch would capture the forbidden type at runtime).
     *
     * @var list<class-string>
     */
    private const FORBIDDEN = [
        \Error::class,
        \ErrorException::class,
    ];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Catch_::class;
    }

    /**
     * @param Catch_ $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->endsWithThrow($node->stmts)) {
            return [];
        }
        $errors = [];
        foreach ($node->types as $typeName) {
            $error = $this->checkType($typeName);
            if ($error !== null) {
                $errors[] = $error;
            }
        }
        return $errors;
    }

    /**
     * A catch that ends in an unconditional `throw` (re-raise or wrap) is
     * exempt — the failure still propagates to the global exception handler.
     *
     * @param Node[] $stmts
     */
    private function endsWithThrow(array $stmts): bool
    {
        $last = end($stmts);
        if ($last === false) {
            return false;
        }
        return $last instanceof ExpressionStmt && $last->expr instanceof ThrowExpr;
    }

    private function checkType(Name $typeName): ?IdentifierRuleError
    {
        $declaredFqn = ltrim($typeName->toString(), '\\');
        if (!$this->reflectionProvider->hasClass($declaredFqn)) {
            return null;
        }
        $declaredRef = $this->reflectionProvider->getClass($declaredFqn);

        foreach (self::FORBIDDEN as $forbiddenFqn) {
            if (!$this->reflectionProvider->hasClass($forbiddenFqn)) {
                continue;
            }
            $forbiddenRef = $this->reflectionProvider->getClass($forbiddenFqn);

            if ($declaredRef->is($forbiddenFqn) || $forbiddenRef->is($declaredFqn)) {
                return RuleErrorBuilder::message(sprintf(
                    'catch (%s) would suppress %s, which is forbidden.',
                    $declaredFqn,
                    $forbiddenFqn,
                ))
                    ->identifier('openemr.forbiddenCatchType')
                    ->line($typeName->getStartLine())
                    ->tip('Narrow the catch to a type unrelated to the forbidden one, or re-throw (throw;) after any observation/logging so the failure propagates to the global exception handler.')
                    ->build();
            }
        }

        return null;
    }
}
