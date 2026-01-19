<?php

/**
 * Custom PHPStan Rule to Forbid Legacy Functions in Modern Code
 *
 * This rule prevents use of:
 * - Legacy sql.inc.php functions (use QueryUtils or DatabaseQueryTrait instead)
 * - Legacy call_user_func and call_user_func_array (use modern PHP syntax instead)
 *
 * @package   OpenEMR
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<FuncCall>
 */
class ForbiddenFunctionsRule implements Rule
{
    /**
     * Map of forbidden functions to their error messages
     */
    private const FORBIDDEN_FUNCTIONS = [
        'generate_id' => 'Use QueryUtils::generateId() instead.',
        'edi_generate_id' => 'Use QueryUtils::ediGenerateId() instead.',
        'sqlQuery' => 'Use QueryUtils::querySingleRow() or QueryUtils::fetchRecords() instead of sqlQuery().',
        'sqlStatement' => 'Use QueryUtils::sqlStatementThrowException() or QueryUtils::fetchRecords() instead of sqlStatement().',
        'sqlInsert' => 'Use QueryUtils::sqlInsert() instead of sqlInsert().',
        'sqlFetchArray' => 'Use QueryUtils::fetchRecords() or QueryUtils::fetchArrayFromResultSet() instead of sqlFetchArray().',
        'sqlBeginTrans' => 'Use QueryUtils::inTransaction() instead of sqlBeginTrans().',
        'sqlCommitTrans' => 'Use QueryUtils::inTransaction() instead of sqlCommitTrans().',
        'sqlRollbackTrans' => 'Use QueryUtils::inTransaction() instead of sqlRollbackTrans().',
        'sqlStatementNoLog' => 'Use QueryUtils::fetchRecordsNoLog() instead of sqlStatementNoLog().',
        'sqlStatementThrowException' => 'Use QueryUtils::sqlStatementThrowException() instead of global sqlStatementThrowException().',
        'sqlQueryNoLog' => 'Use QueryUtils::querySingleRow() instead of sqlQueryNoLog().',
        'call_user_func' => 'Use uniform variable syntax $callable(...$args) or the argument unpacking operator instead of call_user_func().',
        'call_user_func_array' => 'Use uniform variable syntax $callable(...$args) or the argument unpacking operator instead of call_user_func_array().',
    ];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @param FuncCall $node
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!($node->name instanceof Name)) {
            return [];
        }

        $functionName = $node->name->toString();

        // Only check if it's a forbidden function
        if (!isset(self::FORBIDDEN_FUNCTIONS[$functionName])) {
            return [];
        }

        $message = self::FORBIDDEN_FUNCTIONS[$functionName];

        // Determine error identifier and tip based on function type
        if (in_array($functionName, ['call_user_func', 'call_user_func_array'])) {
            return [
                RuleErrorBuilder::message($message)
                    ->identifier('openemr.legacyCallUserFunc')
                    ->tip('Example: $myFunction(...$args) or [$object, \'method\'](...$args)')
                    ->build()
            ];
        }

        // Default for SQL functions
        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.deprecatedSqlFunction')
                ->tip('Or use DatabaseQueryTrait in your class')
                ->build()
        ];
    }
}
