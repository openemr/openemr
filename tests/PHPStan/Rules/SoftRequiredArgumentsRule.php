<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Require arguments in static analysis that are defined as optional in
 * execution. This allows progress on making parameters required without
 * actually breaking existing code.
 *
 * @implements Rule<Node\Expr>
 */
final class SoftRequiredArgumentsRule implements Rule
{
    /**
     * @var array<string, list<string>>
     */
    private const REQUIRED_ARGS = [
        // 'sqlStatement' => ['statement', 'binds'],
        // 'sqlStatementNoLog' => ['statement', 'binds'],
        // 'sqlQuery' => ['statement', 'binds'],
        // QueryUtils::class . '::fetchRecordsNoLog' => ['sqlStatement', 'binds'],
        // ... more to come
    ];

    public function getNodeType(): string
    {
        return Node\Expr::class;
    }

    /**
     * @param Node\Expr $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof FuncCall) {
            return $this->checkFunctionCall($node);
        }

        if ($node instanceof StaticCall) {
            return $this->checkStaticCall($node);
        }

        return [];
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function checkFunctionCall(FuncCall $call): array
    {
        if (!$call->name instanceof Node\Name) {
            return [];
        }

        $args = array_values(array_filter($call->args, fn($arg) => $arg instanceof Arg));
        return $this->check($call->name->toString(), $args);
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function checkStaticCall(StaticCall $call): array
    {
        if (
            !$call->class instanceof Node\Name
            || !$call->name instanceof Node\Identifier
        ) {
            return [];
        }

        $args = array_values(array_filter($call->args, fn($arg) => $arg instanceof Arg));
        return $this->check($call->name->toString(), $args);
    }

    /**
     * @param list<Arg> $args
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function check(string $symbol, array $args): array
    {
        // @phpstan-ignore function.impossibleType (REQUIRED_ARGS is intentionally empty pending migration)
        if (!array_key_exists($symbol, self::REQUIRED_ARGS)) {
            return [];
        }

        // @phpstan-ignore deadCode.unreachable (will be reachable once REQUIRED_ARGS is populated)
        $required = self::REQUIRED_ARGS[$symbol];

        // Map the calls (by index or name) to the required args
        $named = [];
        $positionalCount = 0;

        foreach ($args as $arg) {
            if ($arg->name !== null) {
                $named[$arg->name->toString()] = true;
            } else {
                $positionalCount++;
            }
        }

        $missing = [];

        foreach ($required as $index => $name) {
            if (isset($named[$name])) {
                continue;
            }

            if ($positionalCount > $index) {
                continue;
            }

            $missing[] = $name;
        }

        if ($missing === []) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s is missing required arguments: $%s',
                $symbol,
                implode(', $', $missing),
            ))
            // argument.missing (existing)?
            ->identifier('openemr.futureRequiredArgument')
            ->build(),
        ];
    }
}
