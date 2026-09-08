<?php

/**
 * Classifier for AST nodes that represent OpenEMR SQL execution entry points.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules\Sql;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;

/**
 * Returns true when an AST call node corresponds to a known SQL execution
 * function or method whose first argument is a SQL string.
 *
 * Maintained as a closed list: when OpenEMR introduces a new SQL entry point
 * (e.g. a new helper on QueryUtils), add it here so the rule analyzes it.
 */
final readonly class SqlSinkResolver
{
    /**
     * Function names (case-sensitive) whose first argument is a SQL string.
     *
     * @var array<string, true>
     */
    private const FUNCTIONS = [
        'sqlStatement' => true,
        'sqlStatementNoLog' => true,
        'sqlStatementThrowException' => true,
        'sqlQuery' => true,
        'sqlQueryNoLog' => true,
        'sqlQueryNoLogIgnoreError' => true,
        'sqlInsert' => true,
        'sqlInsertClean_audit' => true,
        'privQuery' => true,
        'privStatement' => true,
        'idSqlStatement' => true,
    ];

    /**
     * "Class::method" entries whose first argument is a SQL string. Matched
     * against the fully-qualified class name + method name.
     *
     * QueryUtils::selectHelper takes a partial SQL fragment (only the
     * SELECT-list-through-table portion); it's intentionally omitted
     * because the parser can't reliably tokenize incomplete statements.
     *
     * @var array<string, true>
     */
    private const STATIC_METHODS = [
        'OpenEMR\Common\Database\QueryUtils::fetchRecords' => true,
        'OpenEMR\Common\Database\QueryUtils::fetchRecordsNoLog' => true,
        'OpenEMR\Common\Database\QueryUtils::fetchSingleValue' => true,
        'OpenEMR\Common\Database\QueryUtils::fetchTableColumn' => true,
        'OpenEMR\Common\Database\QueryUtils::fetchTableColumnAssoc' => true,
        'OpenEMR\Common\Database\QueryUtils::querySingleRow' => true,
        'OpenEMR\Common\Database\QueryUtils::sqlInsert' => true,
        'OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException' => true,
    ];

    /**
     * Instance-method names exposed by DatabaseQueryTrait. Classes that use
     * the trait call these as `$this->fetchRecords(...)`. Matched by name
     * only rather than by receiver type -- the (reserved ∩ schema-id) gate
     * downstream filters any spurious hit on an unrelated class that happens
     * to define a method with the same name and a string first argument.
     *
     * @var array<string, true>
     */
    private const TRAIT_METHODS = [
        'fetchRecords' => true,
        'fetchRecordsNoLog' => true,
        'fetchSingleValue' => true,
        'fetchTableColumn' => true,
        'fetchTableColumnAssoc' => true,
        'querySingleRow' => true,
        'sqlInsert' => true,
        'sqlStatementThrowException' => true,
    ];

    public function isSink(Node $node): bool
    {
        if ($node instanceof FuncCall) {
            if (!($node->name instanceof Name)) {
                return false;
            }
            // Use the unqualified last part so calls from within namespaces
            // (which PhpParser sees as fully-qualified) still match.
            $shortName = $node->name->getLast();
            return isset(self::FUNCTIONS[$shortName]);
        }

        if ($node instanceof StaticCall) {
            if (!($node->class instanceof Name) || !($node->name instanceof Node\Identifier)) {
                return false;
            }
            $class = $node->class->toString();
            $method = $node->name->toString();
            return isset(self::STATIC_METHODS["{$class}::{$method}"]);
        }

        if ($node instanceof MethodCall) {
            if (!($node->name instanceof Node\Identifier)) {
                return false;
            }
            return isset(self::TRAIT_METHODS[$node->name->toString()]);
        }

        return false;
    }
}
