<?php

declare(strict_types=1);

namespace Brick\VarExporter\Internal\ObjectExporter;

use Brick\VarExporter\ExportException;
use Brick\VarExporter\Internal\ObjectExporter;
use Closure;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use ReflectionFunction;

/**
 * Handles closures.
 *
 * @internal This class is for internal use, and not part of the public API. It may change at any time without warning.
 */
class ClosureExporter extends ObjectExporter
{
    /**
     * @var Parser|null
     */
    private $parser;

    /**
     * {@inheritDoc}
     */
    public function supports(\ReflectionObject $reflectionObject) : bool
    {
        return $reflectionObject->getName() === \Closure::class;
    }

    /**
     * {@inheritDoc}
     */
    public function export($object, \ReflectionObject $reflectionObject, array $path, array $parentIds) : array
    {
        assert($object instanceof Closure);

        $reflectionFunction = new \ReflectionFunction($object);

        $file = $reflectionFunction->getFileName();
        $line = $reflectionFunction->getStartLine();

        $ast = $this->parseFile($file, $path);
        $ast = $this->resolveNames($ast);

        $closure = $this->getClosure($reflectionFunction, $ast, $file, $line, $path);

        $prettyPrinter = new ClosureExporter\PrettyPrinter();
        $prettyPrinter->setVarExporterNestingLevel(count($path) + $this->exporter->indentLevel);

        $code = $prettyPrinter->prettyPrintExpr($closure);

        // Consider the pretty-printer output as a single line, to avoid breaking multiline quoted strings and
        // heredocs / nowdocs. We must leave the indenting responsibility to the pretty-printer.

        return [$code];
    }

    /**
     * @return Parser
     */
    private function getParser()
    {
        if ($this->parser === null) {
            $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        }

        return $this->parser;
    }

    /**
     * Parses the given source file.
     *
     * @param string   $filename The source file name.
     * @param string[] $path     The path to the closure in the array/object graph.
     *
     * @return Node\Stmt[] The AST.
     *
     * @throws ExportException
     */
    private function parseFile(string $filename, array $path) : array
    {
        if (substr($filename, -16) === " : eval()'d code") {
            throw new ExportException("Closure defined in eval()'d code cannot be exported.", $path);
        }

        $source = @ file_get_contents($filename);

        if ($source === false) {
            // @codeCoverageIgnoreStart
            throw new ExportException("Cannot open source file \"$filename\" for reading closure code.", $path);
            // @codeCoverageIgnoreEnd
        }

        try {
            $nodes = $this->getParser()->parse($source);

            // throwing error handler
            assert($nodes !== null);

            return $nodes;
            // @codeCoverageIgnoreStart
        } catch (Error $e) {
            throw new ExportException("Cannot parse file \"$filename\" for reading closure code.", $path, $e);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Resolves namespaced names in the AST.
     *
     * @param Node[] $ast
     *
     * @return Node[]
     */
    private function resolveNames(array $ast) : array
    {
        $nameResolver = new NameResolver();
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($nameResolver);

        return $nodeTraverser->traverse($ast);
    }

    /**
     * Finds a closure in the source file and returns its node.
     *
     * @param ReflectionFunction $reflectionFunction Reflection of the closure.
     * @param Node[]             $ast                The AST.
     * @param string             $file               The file name.
     * @param int                $line               The line number where the closure is located in the source file.
     * @param string[]           $path               The path to the closure in the array/object graph.
     *
     * @return Node\Expr\Closure
     *
     * @throws ExportException
     */
    private function getClosure(
        ReflectionFunction $reflectionFunction,
        array $ast,
        string $file,
        int $line,
        array $path
    ) : Node\Expr\Closure {
        $finder = new FindingVisitor(function(Node $node) use ($line) : bool {
            return ($node instanceof Node\Expr\Closure || $node instanceof Node\Expr\ArrowFunction)
                && $node->getStartLine() === $line;
        });

        $traverser = new NodeTraverser();
        $traverser->addVisitor($finder);
        $traverser->traverse($ast);

        $closures = $finder->getFoundNodes();
        $count = count($closures);

        if ($count !== 1) {
            throw new ExportException(sprintf(
                'Expected exactly 1 closure in %s on line %d, found %d.',
                $file,
                $line,
                $count
            ), $path);
        }

        /** @var Node\Expr\Closure|Node\Expr\ArrowFunction $closure */
        $closure = $closures[0];

        if ($closure instanceof Node\Expr\ArrowFunction) {
            $closure = $this->convertArrowFunction($reflectionFunction, $closure);
        }

        if ($closure->uses) {
            $this->closureHandleUses($reflectionFunction, $closure, $path);
        }

        return $closure;
    }

    /**
     * Convert a parsed arrow function to a closure.
     *
     * @param ReflectionFunction       $reflectionFunction  Reflection of the closure.
     * @param Node\Expr\ArrowFunction  $arrowFunction       Parsed arrow function.
     *
     * @return Node\Expr\Closure
     */
    private function convertArrowFunction(
        ReflectionFunction $reflectionFunction,
        Node\Expr\ArrowFunction $arrowFunction
    ) : Node\Expr\Closure {
        $closure = new Node\Expr\Closure([], ['arrow_function' => true]);

        $closure->static = false;
        $closure->params = $arrowFunction->params;
        $closure->returnType = $arrowFunction->returnType;

        $closure->stmts[] = new Node\Stmt\Return_($arrowFunction->expr);

        $static = $reflectionFunction->getStaticVariables();

        foreach (array_keys($static) as $var) {
            assert(is_string($var));

            $closure->uses[] = new Node\Expr\ClosureUse(
                new Node\Expr\Variable($var)
            );
        }

        return $closure;
    }

    /**
     * Handle `use` part of closure.
     *
     * @param ReflectionFunction $reflectionFunction Reflection of the closure.
     * @param Node\Expr\Closure  $closure            Parsed closure.
     * @param string[]           $path               The path to the closure in the array/object graph.
     *
     * @throws ExportException
     */
    private function closureHandleUses(
        ReflectionFunction $reflectionFunction,
        Node\Expr\Closure $closure,
        array $path
    ) : void {
        if (! $this->exporter->closureSnapshotUses) {
            $message = $closure->hasAttribute('arrow_function')
                ? "The arrow function uses variables in the parent scope, this is not supported by default"
                : "The closure has bound variables through 'use', this is not supported by default";

            throw new ExportException("$message. Use the CLOSURE_SNAPSHOT_USE option to export them.", $path);
        }

        $static = $reflectionFunction->getStaticVariables();
        $stmts = [];

        $parser = $this->getParser();

        foreach ($closure->uses as $use) {
            $var = $use->var->name;

            assert(is_string($var));

            $export = array_merge(['<?php'], $this->exporter->export($static[$var], $path, []), [';']);
            $nodes = $parser->parse(implode(PHP_EOL, $export));

            // throwing error handler
            assert($nodes !== null);

            /** @var Node\Stmt\Expression $expr */
            $expr = $nodes[0];

            $assign = new Node\Expr\Assign(
                new Node\Expr\Variable($var),
                $expr->expr
            );
            $stmts[] = new Node\Stmt\Expression($assign);
        }

        $closure->uses = [];
        $closure->stmts = array_merge($stmts, $closure->stmts);
    }
}
