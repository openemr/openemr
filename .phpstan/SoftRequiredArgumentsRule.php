<?php

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use OpenEMR\Common\Database\QueryUtils;

/**
 * @implements Rule<Node\Expr>
 */
final class SoftRequiredArgumentsRule implements Rule
{
    private const REQUIRED_ARGS = [
        'sqlStatement' => ['statement', 'binds'],
        QueryUtils::class . '::fetchRecordsNoLog' => ['sqlStatement', 'binds'],
    ];

    public function getNodeType(): string
    {
        return Node\Expr::class;
    }

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

    private function checkFunctionCall(FuncCall $call): array
    {
        if (!$call->name instanceof Node\Name) {
            return [];
        }

        $name = $call->name->toString();

        return $this->check(
            $name,
            count($call->args),
            $call
        );
    }

    private function checkStaticCall(StaticCall $call): array
    {
        if (
            !$call->class instanceof Node\Name
            || !$call->name instanceof Node\Identifier
        ) {
            return [];
        }

        $name = $call->class->toString() . '::' . $call->name->toString();

        return $this->check(
            $name,
            count($call->args),
            $call
        );
    }

    /**
     * @return list<string>
     */
    private function check(string $symbol, int $argCount, Node $node): array
    {
        if (!isset(self::REQUIRED_ARGS[$symbol])) {
            return [];
        }

        $required = self::REQUIRED_ARGS[$symbol];
        $requiredCount = count($required);

        if ($argCount >= $requiredCount) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s requires %d arguments (%s), %d provided.',
                $symbol,
                $requiredCount,
                implode(', ', $required),
                $argCount,
            ))
            ->identifier('blah')
            ->build(),
        ];
    }
}
