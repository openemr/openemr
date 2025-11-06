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
        'sqlQuery' => 'Use QueryUtils::querySingleRow() or QueryUtils::fetchRecords() instead of sqlQuery().',
        'sqlStatement' => 'Use QueryUtils::sqlStatementThrowException() or QueryUtils::fetchRecords() instead of sqlStatement().',
        'sqlInsert' => 'Use QueryUtils::sqlInsert() instead of sqlInsert().',
        'sqlFetchArray' => 'Use QueryUtils::fetchRecords() or QueryUtils::fetchArrayFromResultSet() instead of sqlFetchArray().',
        'sqlBeginTrans' => 'Use QueryUtils::startTransaction() instead of sqlBeginTrans().',
        'sqlCommitTrans' => 'Use QueryUtils::commitTransaction() instead of sqlCommitTrans().',
        'sqlRollbackTrans' => 'Use QueryUtils::rollbackTransaction() instead of sqlRollbackTrans().',
        'sqlStatementNoLog' => 'Use QueryUtils::fetchRecordsNoLog() instead of sqlStatementNoLog().',
        'sqlStatementThrowException' => 'Use QueryUtils::sqlStatementThrowException() instead of global sqlStatementThrowException().',
        'sqlQueryNoLog' => 'Use QueryUtils::querySingleRow() instead of sqlQueryNoLog().',
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

        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.deprecatedSqlFunction')
                ->tip('Or use DatabaseQueryTrait in your class')
                ->build()
        ];
    }
}
