<?php

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class ForbiddenGlobalNamespaceRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Stmt\Function_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $ns = $scope->getNamespace();
        if ($ns !== null) {
            // Not in global namespace, this is permitted.
            return [];
        }

        // todo: allowlist for autoload_files?
        $definingFile = $scope->getFile();

        $functionName = $node->name->toString();

        $message = sprintf(
            'Function %s may not be defined in the global namespace.',
            $functionName,
        );
        $closureTip = sprintf(
            'Try a closure, like $%s = function () { ... }',
            $functionName,
        );
        $ooTip = 'A static method in an auto-loaded class works too';
        $includeTip = 'If this MUST be a global function, use `library/global_functions.inc.php` as a last resort.';
        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.noGlobalNsFunctions')
                ->addTip($closureTip)
                ->addTip($ooTip)
                ->addTip($includeTip)
                ->build(),
        ];
    }
}
