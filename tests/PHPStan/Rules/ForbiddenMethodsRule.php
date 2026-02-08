<?php

/**
 * Custom PHPStan Rule to Forbid Legacy SQL Functions in Modern Code
 *
 * This rule prevents use of legacy sql.inc.php functions in the src/ directory.
 * Contributors should use QueryUtils or DatabaseQueryTrait instead.
 *
 * @package   OpenEMR
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<MethodCall>
 */
class ForbiddenMethodsRule implements Rule
{
    /**
     * Map of forbidden methods to their error messages
     *
     * (Ideally, these would be scoped to a specific class/interface, but most are
     * targeting globals lacking sufficient type info)
     */
    private const FORBIDDEN_METHODS = [
        'GenID' => 'Use QueryUtils::generateId() or QueryUtils::ediGenerateId() instead.',
    ];

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param FuncCall $node
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!($node->name instanceof Identifier)) {
            return [];
        }

        $functionName = $node->name->toString();

        // Only check if it's a forbidden method
        if (!isset(self::FORBIDDEN_METHODS[$functionName])) {
            return [];
        }

        $message = sprintf(
            '%s is deprecated. %s',
            $functionName,
            self::FORBIDDEN_METHODS[$functionName],
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.deprecatedSqlFunction')
                ->tip('Or use DatabaseQueryTrait in your class')
                ->build()
        ];
    }
}
