#!/usr/bin/env php
<?php

/**
 * Extract v_database value from version.php using AST parsing
 *
 * Outputs the integer value to stdout. Exits non-zero if not found.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\ParserFactory;

$file = $argv[1] ?? __DIR__ . '/../version.php';

if (!file_exists($file)) {
    fwrite(STDERR, "File not found: $file\n");
    exit(1);
}

$code = file_get_contents($file);
if ($code === false) {
    fwrite(STDERR, "Could not read file: $file\n");
    exit(1);
}

$parser = (new ParserFactory())->createForNewestSupportedVersion();

try {
    $ast = $parser->parse($code);
} catch (PhpParser\Error $e) {
    fwrite(STDERR, "Parse error: {$e->getMessage()}\n");
    exit(1);
}

if ($ast === null) {
    fwrite(STDERR, "Parse returned null for: $file\n");
    exit(1);
}

$visitor = new class extends NodeVisitorAbstract {
    public ?int $vDatabase = null;

    public function enterNode(Node $node): ?int
    {
        // Look for: $v_database = <number>;
        if (
            $node instanceof Node\Expr\Assign
            && $node->var instanceof Node\Expr\Variable
            && $node->var->name === 'v_database'
            && $node->expr instanceof Node\Scalar\Int_
        ) {
            $this->vDatabase = $node->expr->value;
            return NodeVisitor::STOP_TRAVERSAL;
        }
        return null;
    }
};

$traverser = new NodeTraverser();
$traverser->addVisitor($visitor);
$traverser->traverse($ast);

if ($visitor->vDatabase === null) {
    fwrite(STDERR, "Could not find \$v_database assignment in $file\n");
    exit(1);
}

echo $visitor->vDatabase . "\n";
