<?php

/**
 * Custom PHPStan Rule to Forbid Direct Output
 *
 * Flags PHP that writes to the output stream itself: inline HTML outside a
 * PHP block, echo (including <?= ?>), print, and the printf family. Output
 * belongs in a Symfony Response or a Twig template. Existing occurrences live
 * in the baseline, whose per-file counts can only go down.
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
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Walks the whole file rather than hooking each node type, so output after
 * an exit or return, which PHPStan skips as unreachable, still counts.
 *
 * @implements Rule<FileNode>
 */
class ForbiddenDirectOutputRule implements Rule
{
    /** Path prefixes relative to the project root; only the top-level tests/ is exempt. */
    public const DEFAULT_EXEMPT_PATH_PREFIXES = [
        'tests/',
    ];

    private const IDENTIFIER = 'openemr.forbiddenDirectOutput';

    private const TIP = 'Return a Symfony\Component\HttpFoundation\Response from a controller, or render markup through a Twig template (OpenEMR\Common\Twig\TwigContainer). Console commands write to the Symfony Console OutputInterface.';

    private const OUTPUT_FUNCTIONS = [
        'fpassthru' => 'Stream file contents with a Symfony StreamedResponse or BinaryFileResponse.',
        'printf' => 'Build the string with sprintf() and return it in a Response or pass it to a template.',
        'readfile' => 'Send files with a Symfony BinaryFileResponse.',
        'vprintf' => 'Build the string with vsprintf() and return it in a Response or pass it to a template.',
    ];

    private readonly string $projectRoot;

    /**
     * @param list<string> $exemptPathPrefixes
     * @param ?string $projectRoot defaults to the checkout this rule lives in
     */
    public function __construct(
        private readonly array $exemptPathPrefixes = self::DEFAULT_EXEMPT_PATH_PREFIXES,
        ?string $projectRoot = null,
    ) {
        $this->projectRoot = rtrim(str_replace('\\', '/', $projectRoot ?? dirname(__DIR__, 3)), '/') . '/';
    }

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
        $file = str_replace('\\', '/', $scope->getFile());
        if (str_starts_with($file, $this->projectRoot)) {
            $relativePath = substr($file, strlen($this->projectRoot));
            foreach ($this->exemptPathPrefixes as $prefix) {
                if (str_starts_with($relativePath, $prefix)) {
                    return [];
                }
            }
        }

        $errors = [];
        $outputNodes = (new NodeFinder())->find(
            $node->getNodes(),
            static fn (Node $n): bool => $n instanceof InlineHTML
                || $n instanceof Echo_
                || $n instanceof Print_
                || $n instanceof FuncCall,
        );
        foreach ($outputNodes as $outputNode) {
            array_push($errors, ...$this->errorsFor($outputNode));
        }
        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function errorsFor(Node $node): array
    {
        if ($node instanceof InlineHTML) {
            return $this->inlineHtmlErrors($node);
        }
        if ($node instanceof Echo_) {
            return [$this->error('echo is forbidden. Return output in a Response or render it with a Twig template.', $node->getStartLine())];
        }
        if ($node instanceof Print_) {
            return [$this->error('print is forbidden. Return output in a Response or render it with a Twig template.', $node->getStartLine())];
        }
        if (!($node instanceof FuncCall) || !($node->name instanceof Name)) {
            return [];
        }
        $functionName = $node->name->toLowerString();
        if (!isset(self::OUTPUT_FUNCTIONS[$functionName])) {
            return [];
        }
        return [$this->error(
            sprintf('%s() writes output directly and is forbidden. %s', $functionName, self::OUTPUT_FUNCTIONS[$functionName]),
            $node->getStartLine(),
        )];
    }

    /**
     * Reports one error per non-blank line, so adding markup to an existing
     * block raises the count and moving it to Twig lowers it. A shebang on
     * the first line is skipped: PHP strips it instead of printing it.
     *
     * @return list<IdentifierRuleError>
     */
    private function inlineHtmlErrors(InlineHTML $node): array
    {
        $errors = [];
        foreach (explode("\n", $node->value) as $offset => $line) {
            if (trim($line) === '') {
                continue;
            }
            $lineNumber = $node->getStartLine() + $offset;
            if ($lineNumber === 1 && str_starts_with($line, '#!')) {
                continue;
            }
            $errors[] = $this->error(
                'Inline HTML outside a PHP block is forbidden. Render markup with a Twig template.',
                $lineNumber,
            );
        }
        return $errors;
    }

    private function error(string $message, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier(self::IDENTIFIER)
            ->line($line)
            ->tip(self::TIP)
            ->build();
    }
}
