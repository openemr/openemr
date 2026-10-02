<?php

/**
 * Flags query-string parameters assembled in templates
 *
 * Catches `href="x.php?id=<?php echo attr_url($id); ?>"`: inline HTML ending
 * in `?key=` or `&key=` followed directly by an echo. HandBuiltQueryStringRule
 * covers the same pattern inside PHP expressions.
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
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\InlineHTML;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<FileNode>
 */
final class HandBuiltQueryStringTemplateRule implements Rule
{
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @param FileNode $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $this->walk($node->getNodes(), $errors);
        return $errors;
    }

    /**
     * Checks each run of sibling statements, then recurses into every nested statement list.
     *
     * @param array<Node> $nodes
     * @param list<IdentifierRuleError> $errors
     */
    private function walk(array $nodes, array &$errors): void
    {
        $nodes = array_values($nodes);
        foreach ($nodes as $index => $current) {
            $next = $nodes[$index + 1] ?? null;
            if ($current instanceof InlineHTML && $next instanceof Echo_) {
                $previous = $nodes[$index - 1] ?? null;
                $this->check($current, $next, $previous instanceof Echo_, $nodes[$index + 2] ?? null, $errors);
            }
            foreach ($current->getSubNodeNames() as $name) {
                $child = $current->$name;
                if (is_array($child)) {
                    $this->walk(array_filter($child, static fn (mixed $c): bool => $c instanceof Node), $errors);
                } elseif ($child instanceof Node) {
                    $this->walk([$child], $errors);
                }
            }
        }
    }

    /**
     * @param list<IdentifierRuleError> $errors
     */
    private function check(InlineHTML $html, Echo_ $echo, bool $followsEcho, ?Node $after, array &$errors): void
    {
        $key = HandBuiltQueryString::keyBeforeValue($html->value)
            ?? HandBuiltQueryString::keyBeforeJsConcatenation($html->value);
        // An echo of a URL already ending in '&', then 'id=', then an echo of the value
        if ($key === null && $followsEcho) {
            $key = HandBuiltQueryString::bareKey($html->value);
        }
        if ($key === null) {
            if (!HandBuiltQueryString::endsWithSeparator($html->value)) {
                return;
            }
            if (!$after instanceof InlineHTML || !str_starts_with($after->value, '=')) {
                return;
            }
            $key = '(dynamic)';
        }

        $errors[] = RuleErrorBuilder::message(sprintf(
            'Query parameter "%s" is concatenated by hand in a template (%s). Build the query with QueryString::build().',
            $key,
            $this->describeEncoding($echo),
        ))
            ->identifier(HandBuiltQueryString::IDENTIFIER)
            ->line($echo->getStartLine())
            ->tip(HandBuiltQueryString::TIP)
            ->build();
    }

    private function describeEncoding(Echo_ $echo): string
    {
        $value = $echo->exprs[0] ?? null;
        if ($value instanceof FuncCall && $value->name instanceof Name) {
            return $value->name->toLowerString() . '()';
        }
        return 'unencoded';
    }
}
