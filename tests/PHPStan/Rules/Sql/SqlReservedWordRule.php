<?php

/**
 * PHPStan rule: forbid unbacktick'd reserved-word column references in SQL.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules\Sql;

use PhpMyAdmin\SqlParser\Components\Condition;
use PhpMyAdmin\SqlParser\Components\Expression;
use PhpMyAdmin\SqlParser\Components\SetOperation;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statement;
use PhpMyAdmin\SqlParser\Statements\DeleteStatement;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PhpMyAdmin\SqlParser\Statements\UpdateStatement;
use PhpMyAdmin\SqlParser\Token;
use PhpMyAdmin\SqlParser\TokensList;
use PhpMyAdmin\SqlParser\TokenType;
use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags bare identifiers in SQL string literals that match a MySQL 8+ or
 * MariaDB reserved word AND a known OpenEMR schema identifier.
 *
 * Rationale: an identifier reserved by either supported engine must be
 * backticked or the query fails to parse on that engine. MariaDB has a
 * smaller reserved-word set than MySQL 8.0+, so bugs of this shape pass
 * CI on the MariaDB legs and fail only on MySQL — the FieldRenderingSnapshot
 * test's discovery of `ORDER BY rank` in contact_telecom was one such bug.
 *
 * Coverage spans every grammatical position phpmyadmin/sql-parser's Parser
 * exposes as a column reference: SELECT expression list, WHERE/HAVING
 * conditions, ORDER BY / GROUP BY expressions, JOIN ON conditions, joined
 * table references, UPDATE SET column targets, DELETE WHERE, and INSERT
 * column lists. (The INSERT case requires a token-stream walk -- the
 * parser exposes IntoKeyword::$columns with backticks already stripped.)
 *
 * The intersection with `SchemaColumnRegistry` eliminates false positives:
 * a word like `over_field` shares no name with a real OpenEMR identifier,
 * so even though `over` is reserved it never fires. Function calls
 * (DATE_ADD, NOW, etc.) are correctly distinguished from identifier
 * references by the parser's grammar awareness.
 *
 * @implements Rule<CallLike>
 */
final readonly class SqlReservedWordRule implements Rule
{
    public function __construct(
        private ReservedWordRegistry $reservedWords,
        private SchemaColumnRegistry $schemaIdentifiers,
        private SqlSinkResolver $sinks,
    ) {
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param CallLike $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->sinks->isSink($node)) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $firstArg = $args[0]->value;
        $type = $scope->getType($firstArg);

        $constantStrings = $type->getConstantStrings();
        if ($constantStrings === []) {
            // Dynamic SQL we can't analyse — silently skip rather than emit a
            // false positive.
            return [];
        }

        $reportedThisCall = [];
        $errors = [];
        foreach ($constantStrings as $sqlType) {
            $sql = $sqlType->getValue();
            foreach ($this->findOffendingIdentifiers($sql) as $offender) {
                if (isset($reportedThisCall[$offender])) {
                    continue;
                }
                $reportedThisCall[$offender] = true;
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'SQL identifier `%s` is reserved in MySQL 8+ or MariaDB. '
                    . 'Backtick it (`%s`) or rename the column — bare use fails to parse on those engines.',
                    $offender,
                    $offender,
                ))
                    ->identifier('openemr.sqlReservedWord')
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * Upper bound on the SQL string length we'll feed to the parser, to
     * cap the rule's worst-case time on a hostile or accidentally-huge
     * literal. Realistic OpenEMR SQL strings are well under 10 KB; the
     * 100 KB cap is comfortable headroom and skips analysis entirely
     * past that, since exceeding it is far more likely to indicate
     * embedded data than a column reference worth flagging.
     */
    private const MAX_SQL_BYTES = 100_000;

    /**
     * Walks the parsed SQL tree, yielding each lowercase identifier that is
     * both a reserved word and a known schema identifier and not already
     * backticked.
     *
     * @return iterable<string>
     */
    private function findOffendingIdentifiers(string $sql): iterable
    {
        if (strlen($sql) > self::MAX_SQL_BYTES) {
            return;
        }

        $parser = new Parser($sql);
        $tokens = $parser->list;
        if ($tokens === null) {
            return;
        }

        foreach ($parser->statements as $stmt) {
            yield from $this->checkAll($this->collectIdentifiers($stmt, $tokens));
        }
    }

    /**
     * @param iterable<string> $names
     * @return iterable<string>
     */
    private function checkAll(iterable $names): iterable
    {
        foreach ($names as $raw) {
            $name = strtolower($raw);
            if (!$this->reservedWords->isReserved($name)) {
                continue;
            }
            if (!$this->schemaIdentifiers->isIdentifier($name)) {
                continue;
            }
            yield $name;
        }
    }

    /**
     * @return iterable<string>
     */
    private function collectIdentifiers(Statement $stmt, TokensList $tokens): iterable
    {
        if ($stmt instanceof SelectStatement) {
            yield from $this->visitExpressions($stmt->expr);
            yield from $this->visitExpressions($stmt->from);
            yield from $this->visitConditions($stmt->where ?? []);
            yield from $this->visitConditions($stmt->having ?? []);
            foreach ($stmt->order ?? [] as $order) {
                yield from $this->visitExpression($order->expr);
            }
            foreach ($stmt->group ?? [] as $group) {
                yield from $this->visitExpression($group->expr);
            }
            foreach ($stmt->join ?? [] as $join) {
                yield from $this->visitExpression($join->expr);
                yield from $this->visitConditions($join->on ?? []);
            }
        }

        if ($stmt instanceof UpdateStatement) {
            yield from $this->visitExpressions($stmt->tables ?? []);
            foreach ($stmt->set ?? [] as $op) {
                yield from $this->visitSetOperation($op);
            }
            yield from $this->visitConditions($stmt->where ?? []);
        }

        if ($stmt instanceof InsertStatement) {
            yield from $this->visitInsertColumnList($stmt, $tokens);
        }

        if ($stmt instanceof DeleteStatement) {
            yield from $this->visitExpressions($stmt->from ?? []);
            yield from $this->visitConditions($stmt->where ?? []);
        }
    }

    /**
     * @param iterable<mixed> $expressions
     * @return iterable<string>
     */
    private function visitExpressions(iterable $expressions): iterable
    {
        foreach ($expressions as $expression) {
            yield from $this->visitExpression($expression);
        }
    }

    /**
     * Accepts any value because parser ->expr arrays sometimes contain
     * CaseExpression alongside Expression. Non-Expression values are
     * ignored (CASE expressions hold their column references inside
     * sub-conditions that would need their own walker).
     *
     * Qualified identifiers (e.g. `table.column` or `database.table`) are
     * skipped: MySQL's grammar accepts reserved words after a `.` without
     * quoting, so `p.interval` parses fine even though bare `interval`
     * does not. Only bare references that sit directly in identifier
     * position are flaggable.
     *
     * @return iterable<string>
     */
    private function visitExpression(mixed $expression): iterable
    {
        if (!$expression instanceof Expression) {
            return;
        }

        if ($expression->column !== null && $expression->column !== '') {
            // Column reference. Flag only when bare (no table qualifier).
            if ($expression->table !== null && $expression->table !== '') {
                return;
            }
            $name = $expression->column;
        } elseif ($expression->table !== null && $expression->table !== '') {
            // Table reference. Flag only when bare (no database qualifier).
            if ($expression->database !== null && $expression->database !== '') {
                return;
            }
            $name = $expression->table;
        } else {
            return;
        }

        if ($this->isBackticked($expression->expr ?? '', $name)) {
            return;
        }
        yield $name;
    }

    /**
     * @param iterable<Condition> $conditions
     * @return iterable<string>
     */
    private function visitConditions(iterable $conditions): iterable
    {
        foreach ($conditions as $condition) {
            yield from $this->visitCondition($condition);
        }
    }

    /**
     * @return iterable<string>
     */
    private function visitCondition(Condition $condition): iterable
    {
        // Conditions parsed into a binary operator form expose the
        // operand text directly. Use that rather than ->identifiers, which
        // mixes table aliases, column names, and even string-literal
        // values (e.g. for `WHERE name = 'rank'` it includes 'rank').
        foreach ([$condition->leftOperand, $condition->rightOperand] as $operand) {
            $identifier = $this->extractBareIdentifier($operand);
            if ($identifier === null) {
                continue;
            }
            yield $identifier;
        }
    }

    /**
     * @return iterable<string>
     */
    private function visitSetOperation(SetOperation $operation): iterable
    {
        $column = $operation->column;
        if ($column === '') {
            return;
        }
        // SetOperation preserves the original token text verbatim, so a
        // qualified column shows up with the dot in it
        // (e.g. `p.rank` for `UPDATE t p SET p.rank = 5`). MySQL accepts
        // reserved words after a `.`, so skip qualified forms.
        if (str_contains($column, '.')) {
            return;
        }
        if (str_starts_with($column, '`')) {
            return;
        }
        yield $column;
    }

    /**
     * Walks the column-list parentheses of an INSERT INTO statement and
     * yields each unbacktick'd bare identifier.
     *
     * phpmyadmin/sql-parser exposes IntoKeyword::$columns as plain strings
     * with backticks already stripped, so we can't recover backtick state
     * from the parse tree alone. Instead, walk the raw token stream
     * within the statement's bounds, find the column-list parens, and
     * inspect each token's type/flags directly.
     *
     * Known gap: INSERT INTO foo PARTITION (...) (cols) — the PARTITION
     * clause introduces parens before the column list. This walker
     * currently treats the first parens after the table as the column
     * list, so PARTITION'd inserts would have their partition names
     * walked instead. The (reserved ∩ schema-identifier) gate filters
     * most spurious flags. No known OpenEMR call sites use this.
     *
     * @return iterable<string>
     */
    private function visitInsertColumnList(InsertStatement $stmt, TokensList $tokens): iterable
    {
        if ($stmt->into === null || $stmt->into->columns === null || $stmt->into->columns === []) {
            return; // INSERT INTO foo VALUES (...) -- no column list to walk
        }

        $sawInto = false;
        $sawTable = false;
        $depth = 0;
        $prev = null;

        for ($i = $stmt->first; $i <= $stmt->last; $i++) {
            $token = $tokens->tokens[$i] ?? null;
            if ($token === null) {
                continue;
            }
            if ($token->type === TokenType::Whitespace || $token->type === TokenType::Comment) {
                continue;
            }

            if (!$sawInto) {
                if ($token->type === TokenType::Keyword && strtoupper($token->token) === 'INTO') {
                    $sawInto = true;
                }
                continue;
            }

            if (!$sawTable && $depth === 0) {
                if ($this->isIdentifierToken($token)) {
                    $sawTable = true;
                }
                $prev = $token;
                continue;
            }

            // Handle database-qualified table: "db.foo".
            if ($depth === 0 && $token->type === TokenType::Operator && $token->token === '.') {
                $sawTable = false;
                $prev = $token;
                continue;
            }

            if ($depth === 0) {
                if ($token->type === TokenType::Operator && $token->token === '(') {
                    $depth = 1;
                    $prev = $token;
                    continue;
                }
                // VALUES, SELECT, PARTITION, SET... no column list to inspect.
                return;
            }

            // depth >= 1: inside the column-list parens.
            if ($token->type === TokenType::Operator) {
                if ($token->token === '(') {
                    $depth++;
                } elseif ($token->token === ')') {
                    $depth--;
                    if ($depth === 0) {
                        return;
                    }
                }
                $prev = $token;
                continue;
            }

            if ($depth === 1 && $this->isIdentifierToken($token)) {
                // Skip if backticked.
                if ($token->type === TokenType::Symbol) {
                    $prev = $token;
                    continue;
                }
                // Skip if qualified (preceded by `.`).
                $isQualified = $prev !== null
                    && $prev->type === TokenType::Operator
                    && $prev->token === '.';
                if (!$isQualified) {
                    yield $token->token;
                }
            }

            $prev = $token;
        }
    }

    private function isIdentifierToken(Token $token): bool
    {
        return match ($token->type) {
            TokenType::None, TokenType::Symbol, TokenType::Keyword => true,
            default => false,
        };
    }

    /**
     * Returns true when $columnName appears as a backticked identifier
     * anywhere in $expressionText.
     */
    private function isBackticked(string $expressionText, string $columnName): bool
    {
        return stripos($expressionText, '`' . $columnName . '`') !== false;
    }

    /**
     * Returns the bare identifier name from a condition operand string, or
     * null if the operand is a literal, placeholder, function call,
     * backticked identifier, or table-qualified reference (MySQL accepts
     * reserved words after a `.`, so qualified forms aren't flaggable).
     */
    private function extractBareIdentifier(string $operand): ?string
    {
        $operand = trim($operand);
        if ($operand === '' || $operand === '?') {
            return null;
        }
        // Literal-shaped operands.
        if (str_starts_with($operand, "'") || str_starts_with($operand, '"')) {
            return null;
        }
        if (preg_match('/^-?\d/', $operand) === 1) {
            return null;
        }
        // Function call or sub-expression.
        if (str_contains($operand, '(')) {
            return null;
        }
        // Qualified reference like a.col — MySQL's grammar accepts reserved
        // words after a `.`, so these are not parse failures.
        if (str_contains($operand, '.')) {
            return null;
        }
        $candidate = $operand;
        if (str_starts_with($candidate, '`')) {
            return null;
        }
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $candidate) !== 1) {
            return null;
        }
        return $candidate;
    }
}
