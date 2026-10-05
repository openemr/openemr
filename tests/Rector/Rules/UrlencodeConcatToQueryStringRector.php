<?php

/**
 * Rector rule to replace hand-concatenated query strings with QueryString::build()
 *
 * Rewrites a concatenation chain such as
 *   'x.php?mode=edit&id=' . urlencode($id) . '&pid=' . $pid
 * into
 *   'x.php?' . QueryString::build(['mode' => 'edit', 'id' => $id, 'pid' => $pid])
 *
 * Everything up to the '?' (and any valueless flags after it, as in
 * '?document&retrieve&id=') and everything after the last parameter stays
 * verbatim, so the output is byte-identical to the original. The rule only
 * fires when it can prove that: every key and literal value is unchanged by
 * urlencode(), every value is wrapped in urlencode() or attr_url(), and every
 * bare value is an int or a CSRF token. A value not typed string|int makes it
 * emit buildUntyped(), which coerces as urlencode() does. It leaves a query
 * alone rather than convert part of it, including one appended to with `.=`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rector\Rules;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\QueryString;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UrlencodeConcatToQueryStringRector extends AbstractRector
{
    /**
     * Marks Concat nodes already considered as part of an enclosing chain.
     */
    private const INNER_CONCAT = 'openemrQueryStringInnerConcat';

    /**
     * Characters urlencode() leaves unchanged.
     */
    private const URL_SAFE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_.-';

    /**
     * Characters that may begin the text after the last parameter: a closing quote, fragment, tag, or whitespace.
     */
    private const SUFFIX_START = "'\"#<> \t\r\n";

    private const NEWLINE_THRESHOLD = 3;

    /**
     * parseQuery() result for a parameter run that cannot be rewritten.
     */
    private const BLOCKED = 'blocked';

    private readonly UnionType $stringOrInt;

    public function __construct()
    {
        $this->stringOrInt = new UnionType([new StringType(), new IntegerType()]);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace hand-concatenated urlencode() query strings with QueryString::build()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
function link(string $id, int $pid): string
{
    return 'x.php?mode=edit&id=' . urlencode($id) . '&pid=' . $pid;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
function link(string $id, int $pid): string
{
    return 'x.php?' . \OpenEMR\Common\Http\QueryString::build([
        'mode' => 'edit',
        'id' => $id,
        'pid' => $pid,
    ]);
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Concat::class];
    }

    /**
     * @param Concat $node
     */
    public function refactor(Node $node): ?Node
    {
        // Only the outermost Concat of a chain is a candidate; a sub-chain
        // rewrite would leave the rest of the query hand-concatenated.
        if ($node->getAttribute(self::INNER_CONCAT) === true) {
            return null;
        }

        $parts = $this->flatten($node);

        // The parameters start right after some '?' or '&'. Try each in order, skipping
        // ones that don't begin a parameter run, e.g. the flag in '?document&id='.
        foreach ($parts as $index => $part) {
            if (!$part instanceof String_) {
                continue;
            }
            foreach ($this->separatorOffsets($part->value) as $offset) {
                $tail = substr($part->value, $offset + 1);
                $parsed = $this->parseQuery($this->mergeAdjacentLiterals([$tail, ...array_slice($parts, $index + 1)]));
                if ($parsed === self::BLOCKED) {
                    // Converting a later run would leave this query half hand-built.
                    return null;
                }
                if ($parsed !== null) {
                    return $this->rebuild($parts, $index, substr($part->value, 0, $offset + 1), ...$parsed);
                }
            }
        }

        return null;
    }

    /**
     * Offsets of every '?', and of each '&' that follows a '?' in the same
     * literal. An '&' with no '?' before it continues a query begun in code
     * this chain cannot see, e.g. `$url .= '&a='`, and converting it would
     * leave that query half hand-built.
     *
     * @return list<int>
     */
    private function separatorOffsets(string $text): array
    {
        $offsets = [];
        $inQuery = false;
        $length = strlen($text);
        for ($offset = 0; $offset < $length; $offset++) {
            if ($text[$offset] === '?') {
                $inQuery = true;
                $offsets[] = $offset;
            } elseif ($text[$offset] === '&' && $inQuery) {
                $offsets[] = $offset;
            }
        }
        return $offsets;
    }

    /**
     * @param non-empty-list<Expr> $parts
     * @param string $head The query literal's text through its separator
     * @param non-empty-list<array{string, Expr, bool}> $pairs
     * @param list<string|Expr> $suffix
     */
    private function rebuild(array $parts, int $queryIndex, string $head, array $pairs, array $suffix): Expr
    {
        $queryLiteral = $parts[$queryIndex];
        assert($queryLiteral instanceof String_);

        // Fold literals just before the separator into it, so 'x.php' . '?a=' becomes 'x.php?' not 'x.php' . '?'.
        $rebuilt = array_slice($parts, 0, $queryIndex);
        while (($last = end($rebuilt)) instanceof String_) {
            $head = $last->value . $head;
            array_pop($rebuilt);
        }
        $rebuilt[] = $this->literalLike($queryLiteral, $head);
        $rebuilt[] = $this->buildCall($pairs);
        foreach ($suffix as $part) {
            $rebuilt[] = is_string($part) ? $this->literalLike($queryLiteral, $part) : $part;
        }

        return $this->concatAll($rebuilt);
    }

    /**
     * @return non-empty-list<Expr>
     */
    private function flatten(Expr $expr): array
    {
        if (!$expr instanceof Concat) {
            return [$expr];
        }
        $expr->left->setAttribute(self::INNER_CONCAT, true);
        $expr->right->setAttribute(self::INNER_CONCAT, true);
        return [...$this->flatten($expr->left), ...$this->flatten($expr->right)];
    }

    /**
     * Parses `key=value&key=value...` spread across literal text and value
     * expressions. Returns the pairs plus whatever follows the last one; null
     * when no parameter run starts here; or BLOCKED when one does but this
     * rule cannot prove its rewrite equivalent.
     *
     * @param non-empty-list<string|Expr> $segments Adjacent literals already merged
     * @return array{non-empty-list<array{string, Expr, bool}>, list<string|Expr>}|self::BLOCKED|null
     */
    private function parseQuery(array $segments): array|string|null
    {
        $pairs = [];
        $keys = [];
        $hasDynamicValue = false;
        $text = array_shift($segments);

        while (true) {
            if (!is_string($text)) {
                return $pairs === [] ? null : self::BLOCKED;
            }
            $keyAndRest = explode('=', $text, 2);
            if (count($keyAndRest) !== 2) {
                return $pairs === [] ? null : self::BLOCKED;
            }
            [$key, $rest] = $keyAndRest;
            if (!self::isUrlSafe($key) || isset($keys[$key])) {
                return $pairs === [] ? null : self::BLOCKED;
            }
            $keys[$key] = true;

            if ($rest === '' && ($segments[0] ?? null) instanceof Expr) {
                $value = $this->valueFrom(array_shift($segments));
                if ($value === null) {
                    return self::BLOCKED;
                }
                $pairs[] = [$key, ...$value];
                $hasDynamicValue = true;
                $remainder = '';
            } else {
                $length = strspn($rest, self::URL_SAFE);
                $pairs[] = [$key, new String_(substr($rest, 0, $length)), false];
                $remainder = substr($rest, $length);
            }

            if ($remainder === '') {
                if ($segments === []) {
                    break;
                }
                // Literals are merged, so an expression here directly follows a value.
                $next = array_shift($segments);
                if ($next instanceof Expr) {
                    return self::BLOCKED;
                }
                $remainder = $next;
            }

            if ($remainder[0] === '&') {
                $text = substr($remainder, 1);
                continue;
            }
            if (!str_contains(self::SUFFIX_START, $remainder[0])) {
                return self::BLOCKED;
            }
            array_unshift($segments, $remainder);
            break;
        }

        if (!$hasDynamicValue) {
            return null;
        }
        return [$pairs, $segments];
    }

    private static function isUrlSafe(string $text): bool
    {
        return $text !== '' && strspn($text, self::URL_SAFE) === strlen($text);
    }

    /**
     * @param non-empty-list<string|Expr> $segments
     * @return non-empty-list<string|Expr>
     */
    private function mergeAdjacentLiterals(array $segments): array
    {
        $merged = [];
        foreach ($segments as $segment) {
            if ($segment instanceof String_) {
                $segment = $segment->value;
            }
            $last = array_key_last($merged);
            if (is_string($segment) && $last !== null && is_string($merged[$last])) {
                $merged[$last] .= $segment;
                continue;
            }
            $merged[] = $segment;
        }
        assert($merged !== []);
        return $merged;
    }

    /**
     * Returns the expression to place in the params array and whether it is
     * untyped, or null when neither QueryString::build() nor buildUntyped()
     * would encode it as the original did.
     *
     * @return array{Expr, bool}|null
     */
    private function valueFrom(Expr $expr): ?array
    {
        // attr_url() is attr(urlencode()), and attr() never changes urlencode() output.
        if (!$expr instanceof FuncCall || !$this->isNames($expr, ['urlencode', 'attr_url'])) {
            // A bare value is only safe when urlencode() would leave it unchanged.
            return $this->getType($expr)->isInteger()->yes() || $this->isCsrfToken($expr) ? [$expr, false] : null;
        }

        if ($expr->isFirstClassCallable() || count($expr->args) !== 1) {
            return null;
        }
        $arg = $expr->getArgs()[0];
        if ($arg->unpack || $arg->name !== null) {
            return null;
        }
        // build() takes string|int only; anything else (null, bool, mixed) goes
        // through buildUntyped(), which coerces exactly as urlencode() does.
        return [$arg->value, !$this->stringOrInt->isSuperTypeOf($this->getType($arg->value))->yes()];
    }

    /**
     * The token is a hex HMAC digest, so it needs no encoding.
     */
    private function isCsrfToken(Expr $expr): bool
    {
        return $expr instanceof StaticCall
            && $this->isName($expr->class, CsrfUtils::class)
            && $this->isName($expr->name, 'collectCsrfToken');
    }

    /**
     * Copies the quote style of $template so the rewrite reads like the original.
     */
    private function literalLike(String_ $template, string $value): String_
    {
        return new String_($value, ['kind' => $template->getAttribute('kind', String_::KIND_SINGLE_QUOTED)]);
    }

    /**
     * @param non-empty-list<array{string, Expr, bool}> $pairs Key, value, and whether the value is untyped
     */
    private function buildCall(array $pairs): StaticCall
    {
        $items = [];
        $untyped = false;
        foreach ($pairs as [$key, $value, $valueUntyped]) {
            $items[] = new ArrayItem($value, new String_($key));
            $untyped = $untyped || $valueUntyped;
        }
        $array = new Array_($items, ['kind' => Array_::KIND_SHORT]);
        // One parameter per line once a single line stops being easy to scan,
        // except in templates, where the lines would break up an inline echo tag.
        $array->setAttribute(
            AttributeKey::NEWLINED_ARRAY_PRINT,
            count($items) >= self::NEWLINE_THRESHOLD && !$this->getFile()->containsHTML(),
        );
        return new StaticCall(new FullyQualified(QueryString::class), $untyped ? 'buildUntyped' : 'build', [new Arg($array)]);
    }

    /**
     * @param non-empty-list<Expr> $parts
     */
    private function concatAll(array $parts): Expr
    {
        $expr = array_shift($parts);
        foreach ($parts as $part) {
            $expr = new Concat($expr, $part);
        }
        return $expr;
    }
}
