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

use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Token;
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
 * The intersection with `SchemaColumnRegistry` eliminates false positives:
 * a word like `over_field` shares no name with a real OpenEMR identifier,
 * so even though `over` is reserved it never fires.
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

        // Collect every constant-string value the first arg could resolve to.
        // For most call sites this is a single literal; PHPStan can also
        // resolve simple concatenation chains to a constant string.
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
     * Yields each lowercase identifier in $sql that is both a reserved word
     * and a known schema identifier and not already backticked.
     *
     * Scope is intentionally narrow: only tokens immediately following an
     * ORDER BY / GROUP BY / PARTITION BY composed keyword are inspected.
     * These positions unambiguously require an identifier in SQL grammar,
     * which avoids false positives like flagging INTERVAL in
     * DATE_ADD(x, INTERVAL 7 DAY) or TABLE in CREATE TABLE. Broader
     * coverage (UPDATE SET, WHERE col=, INSERT INTO foo (col,...)) would
     * require a real parser, not a lexer.
     *
     * @return iterable<string>
     */
    private function findOffendingIdentifiers(string $sql): iterable
    {
        $lexer = new Lexer($sql);

        $sawIdentifierIntroducer = false;
        foreach ($lexer->list->tokens as $token) {
            if ($token->type === TokenType::Whitespace || $token->type === TokenType::Comment) {
                continue;
            }

            if (!$sawIdentifierIntroducer) {
                $sawIdentifierIntroducer = $this->isIdentifierIntroducer($token);
                continue;
            }

            // We're at the first non-whitespace token after an introducer.
            // The introducer's effect spans only the immediately following
            // expression in this v1 — multi-column lists like
            // "ORDER BY a, b" only catch `a`. That trades coverage for
            // false-positive freedom; the motivating rank bug is in the
            // first-column position.
            $sawIdentifierIntroducer = false;

            // Backticked identifier → no bug.
            if ($token->type === TokenType::Symbol) {
                continue;
            }

            $name = strtolower($token->token);
            if (!$this->reservedWords->isReserved($name)) {
                continue;
            }
            if (!$this->schemaIdentifiers->isIdentifier($name)) {
                continue;
            }

            yield $name;
        }
    }

    private function isIdentifierIntroducer(Token $token): bool
    {
        if ($token->type !== TokenType::Keyword) {
            return false;
        }
        return match (strtoupper($token->token)) {
            'ORDER BY', 'GROUP BY', 'PARTITION BY' => true,
            default => false,
        };
    }
}
