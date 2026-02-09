<?php

/**
 * Custom PHPStan Rule to block certain static method calls.
 *
 * @package   OpenEMR
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use OpenEMR\Common\Database\QueryUtils;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<StaticCall>
 */
class ForbiddenStaticMethodsRule implements Rule
{
    /**
     * Map of forbidden classes and static methods to their error messages
     */
    private const FORBIDDEN_METHODS = [
        QueryUtils::class => [
            'startTransaction' => 'Use QueryUtils::inTransaction() wrapper instead.',
            'commitTransaction' => 'Use QueryUtils::inTransaction() wrapper instead.',
            'rollbackTransaction' => 'Use QueryUtils::inTransaction() wrapper instead.',
        ],
    ];

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!($node->name instanceof Name)) {
            return [];
        }
        if (!($node->class instanceof Name)) {
            return [];
        }

        $className = $node->class->toString();
        $functionName = $node->name->toString();

        // Check if the class has any deprecated methods
        if (!array_key_exists($className, self::FORBIDDEN_METHODS)) {
            return [];
        }

        // If it does, check if the actual call is one of them
        $forbiddenClassMethods = self::FORBIDDEN_METHODS[$className];
        if (!array_key_exists($functionName, $forbiddenClassMethods)) {
            return [];
        }

        $message = sprintf(
            '%s::%s() is deprecated. %s',
            $className,
            $functionName,
            $forbiddenClassMethods[$functionName],
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.deprecatedSqlFunction')
                ->build()
        ];
    }
}
