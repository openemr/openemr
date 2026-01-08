#!/usr/bin/env php
<?php
/**
 * Extract SQL strings from PHP files for linting using AST parsing
 *
 * Usage: php extract-sql-from-php.php <file.php> [--json]
 *        php extract-sql-from-php.php <file.php> | sqruff lint - --dialect mysql -f json
 *
 * @package   OpenEMR
 * @link      https://opencoreemr.com
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;

if ($argc < 2) {
    fwrite(STDERR, "Usage: {$argv[0]} <file.php> [--json]\n");
    exit(1);
}

$file = $argv[1];
$jsonOutput = in_array('--json', $argv);

if (!file_exists($file)) {
    fwrite(STDERR, "File not found: $file\n");
    exit(1);
}

// SQL-executing functions to look for
$sqlFunctions = [
    'sqlStatement',
    'sqlStatementNoLog',
    'sqlStatementThrowException',
    'sqlQuery',
    'sqlQueryNoLog',
    'sqlInsert',
    'sqlFetchArray',
];

// Static methods on QueryUtils
$sqlStaticMethods = [
    'QueryUtils' => ['fetchRecords', 'fetchRecordsNoLog', 'sqlStatementThrowException', 'fetchTableColumn'],
];

// ADODB method calls: $db->Execute($sql), $db->GetOne($sql), etc.
$adodbMethods = [
    'Execute',
    'GetOne',
    'GetAll',
    'GetRow',
];

class SqlExtractorVisitor extends NodeVisitorAbstract
{
    public array $results = [];
    private string $file;
    private array $sqlFunctions;
    private array $sqlStaticMethods;
    private array $adodbMethods;

    public function __construct(string $file, array $sqlFunctions, array $sqlStaticMethods, array $adodbMethods)
    {
        $this->file = $file;
        $this->sqlFunctions = $sqlFunctions;
        $this->sqlStaticMethods = $sqlStaticMethods;
        $this->adodbMethods = $adodbMethods;
    }

    public function enterNode(Node $node)
    {
        // Check for function calls: sqlStatement($sql, ...)
        if ($node instanceof Node\Expr\FuncCall) {
            if ($node->name instanceof Node\Name) {
                $funcName = $node->name->toString();
                if (in_array($funcName, $this->sqlFunctions) && !empty($node->args)) {
                    $this->extractSqlFromArg($node->args[0], $node->getStartLine());
                }
            }
        }

        // Check for static method calls: QueryUtils::fetchRecords($sql, ...)
        if ($node instanceof Node\Expr\StaticCall) {
            if ($node->class instanceof Node\Name && $node->name instanceof Node\Identifier) {
                $className = $node->class->toString();
                $methodName = $node->name->toString();

                // Check short name (QueryUtils) or full name
                $shortClass = basename(str_replace('\\', '/', $className));
                if (isset($this->sqlStaticMethods[$shortClass])) {
                    if (in_array($methodName, $this->sqlStaticMethods[$shortClass]) && !empty($node->args)) {
                        $this->extractSqlFromArg($node->args[0], $node->getStartLine());
                    }
                }
            }
        }

        // Check for ADODB method calls: $db->Execute($sql), $db->GetOne($sql), etc.
        if ($node instanceof Node\Expr\MethodCall) {
            if ($node->name instanceof Node\Identifier) {
                $methodName = $node->name->toString();
                if (in_array($methodName, $this->adodbMethods) && !empty($node->args)) {
                    $this->extractSqlFromArg($node->args[0], $node->getStartLine());
                }
            }
        }

        // Check for variable assignments: $sql = "SELECT ..."
        if ($node instanceof Node\Expr\Assign) {
            if ($node->var instanceof Node\Expr\Variable && $node->var->name === 'sql') {
                $this->extractSqlFromExpr($node->expr, $node->getStartLine());
            }
        }

        return null;
    }

    private function extractSqlFromArg(Node\Arg $arg, int $line): void
    {
        $this->extractSqlFromExpr($arg->value, $line);
    }

    private function extractSqlFromExpr(Node\Expr $expr, int $line): void
    {
        $sql = $this->resolveStringExpr($expr);
        if ($sql !== null && $this->looksLikeSql($sql)) {
            $this->results[] = [
                'file' => $this->file,
                'line' => $line,
                'sql' => $sql,
            ];
        }
    }

    private function resolveStringExpr(Node\Expr $expr): ?string
    {
        // Simple string literal
        if ($expr instanceof Node\Scalar\String_) {
            return $expr->value;
        }

        // String concatenation
        if ($expr instanceof Node\Expr\BinaryOp\Concat) {
            $left = $this->resolveStringExpr($expr->left);
            $right = $this->resolveStringExpr($expr->right);
            if ($left !== null && $right !== null) {
                return $left . $right;
            }
            // If one side has a variable, use placeholder
            if ($left !== null) {
                return $left . '?';
            }
            if ($right !== null) {
                return '?' . $right;
            }
        }

        // Interpolated string - try to extract the literal parts
        if ($expr instanceof Node\Scalar\InterpolatedString) {
            $result = '';
            foreach ($expr->parts as $part) {
                if ($part instanceof Node\InterpolatedStringPart) {
                    $result .= $part->value;
                } else {
                    $result .= '?'; // placeholder for variables
                }
            }
            return $result;
        }

        // Variable - can't resolve
        return null;
    }

    private function looksLikeSql(string $str): bool
    {
        $keywords = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'ALTER', 'DROP', 'SHOW', 'DESCRIBE', 'REPLACE'];
        $upper = strtoupper(trim($str));
        foreach ($keywords as $keyword) {
            if (strpos($upper, $keyword) === 0) {
                return true;
            }
        }
        return false;
    }
}

$code = file_get_contents($file);
$parser = (new ParserFactory())->createForVersion(PhpVersion::fromComponents(8, 2));

try {
    $ast = $parser->parse($code);
} catch (PhpParser\Error $e) {
    fwrite(STDERR, "Parse error in $file: " . $e->getMessage() . "\n");
    exit(1);
}

$traverser = new NodeTraverser();
$visitor = new SqlExtractorVisitor($file, $sqlFunctions, $sqlStaticMethods, $adodbMethods);
$traverser->addVisitor($visitor);
$traverser->traverse($ast);

if ($jsonOutput) {
    echo json_encode($visitor->results, JSON_PRETTY_PRINT) . "\n";
} else {
    foreach ($visitor->results as $i => $result) {
        $sql = $result['sql'];
        if (!preg_match('/;\s*$/', $sql)) {
            $sql .= ';';
        }
        // Output with comment showing source location
        // sqruff will report line numbers relative to this output
        echo "-- sqruff-source: {$result['file']}:{$result['line']}\n";
        echo $sql . "\n\n";
    }
}
