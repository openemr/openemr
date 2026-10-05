<?php

/**
 * Flags query-string parameters assembled by concatenation or interpolation
 *
 * A parameter is hand-built when a literal ending in `?key=` or `&key=` is
 * joined directly to a runtime value, or when the key itself is a runtime
 * value between a separator and '='. Each finding says how the value is
 * encoded today, which decides how to migrate it to QueryString::build().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Expr>
 */
final class HandBuiltQueryStringRule implements Rule
{
    private const DYNAMIC_KEY = '(dynamic)';

    public function getNodeType(): string
    {
        return Expr::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $sites = match (true) {
            $node instanceof Concat => $this->concatSites($node),
            $node instanceof InterpolatedString => $this->interpolatedSites($node),
            default => [],
        };

        $errors = [];
        foreach ($sites as [$key, $value]) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Query parameter "%s" is concatenated by hand (%s). Build the query with QueryString::build().',
                $key,
                $key === self::DYNAMIC_KEY ? 'dynamic key' : $this->describeEncoding($value, $scope),
            ))
                ->identifier(HandBuiltQueryString::IDENTIFIER)
                ->line($value->getStartLine())
                ->tip(HandBuiltQueryString::TIP)
                ->build();
        }
        return $errors;
    }

    /**
     * Each adjacent pair of leaves in a chain meets at exactly one Concat
     * node, so checking only this node's own seam reports every site once.
     *
     * @return list<array{string, Expr}>
     */
    private function concatSites(Concat $node): array
    {
        $left = $this->leaves($node->left);
        $before = $left[count($left) - 1];
        $after = $node->right;
        while ($after instanceof Concat) {
            $after = $after->left;
        }

        // 'x.php?id=' . $id
        $beforeText = $this->literalText($before);
        $key = $beforeText === null ? null : HandBuiltQueryString::keyBeforeValue($beforeText);
        // $url . 'id=' . $id, where $url already ends in '&'. A SQL-quoted value
        // ($where . 'name=' . $db->Quote($name)) is a SQL condition, not a query.
        if (
            $key === null
            && $beforeText !== null
            && count($left) > 1
            && !$left[count($left) - 2] instanceof String_
            && !$this->isSqlQuoted($after)
        ) {
            $key = HandBuiltQueryString::bareKey($beforeText);
        }
        if ($key !== null && !$after instanceof String_) {
            return [[$key, $after]];
        }

        // '&' . $name . '=' . $value, seen at the seam before '='
        $separator = $this->literalText($left[count($left) - 2] ?? null);
        if (
            $after instanceof String_
            && str_starts_with($after->value, '=')
            && !$before instanceof String_
            && $separator !== null
            && HandBuiltQueryString::endsWithSeparator($separator)
        ) {
            return [[self::DYNAMIC_KEY, $before]];
        }

        return [];
    }

    /**
     * @return list<array{string, Expr}>
     */
    private function interpolatedSites(InterpolatedString $node): array
    {
        $sites = [];
        $parts = array_values($node->parts);
        foreach ($parts as $index => $part) {
            $previous = $parts[$index - 1] ?? null;
            if (!$part instanceof Expr || !$previous instanceof InterpolatedStringPart) {
                continue;
            }
            $key = HandBuiltQueryString::keyBeforeValue($previous->value);
            if ($key !== null) {
                $sites[] = [$key, $part];
                continue;
            }
            $next = $parts[$index + 1] ?? null;
            if (
                HandBuiltQueryString::endsWithSeparator($previous->value)
                && $next instanceof InterpolatedStringPart
                && str_starts_with($next->value, '=')
            ) {
                $sites[] = [self::DYNAMIC_KEY, $part];
            }
        }
        return $sites;
    }

    /**
     * ADODB's Quote() and qstr() wrap a value for a SQL literal.
     */
    private function isSqlQuoted(Expr $expr): bool
    {
        return $expr instanceof MethodCall
            && $expr->name instanceof Identifier
            && in_array($expr->name->toLowerString(), ['quote', 'qstr'], true);
    }

    /**
     * @return non-empty-list<Expr>
     */
    private function leaves(Expr $expr): array
    {
        if (!$expr instanceof Concat) {
            return [$expr];
        }
        return [...$this->leaves($expr->left), ...$this->leaves($expr->right)];
    }

    /**
     * The literal text at the end of $expr, when it ends in literal text.
     */
    private function literalText(?Expr $expr): ?string
    {
        if ($expr instanceof String_) {
            return $expr->value;
        }
        if ($expr instanceof InterpolatedString) {
            $last = end($expr->parts);
            return $last instanceof InterpolatedStringPart ? $last->value : null;
        }
        return null;
    }

    private function describeEncoding(Expr $value, Scope $scope): string
    {
        if ($value instanceof FuncCall && $value->name instanceof Name) {
            $function = $value->name->toLowerString();
            $args = $value->getArgs();
            $argType = count($args) === 1 ? $scope->getType($args[0]->value) : null;
            $typed = $argType !== null && ($argType->isString()->yes() || $argType->isInteger()->yes());
            return match ($function) {
                'urlencode', 'attr_url' => $function . '(), ' . ($typed ? 'typed' : 'untyped'),
                default => $function . '()',
            };
        }

        $type = $scope->getType($value);
        if ($type->isInteger()->yes()) {
            return 'unencoded int';
        }
        if ($type->isString()->yes()) {
            return 'unencoded string';
        }
        return 'unencoded, untyped';
    }
}
