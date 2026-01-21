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

        // This is a bit fragile - trim the leading app root plus trailing
        // slash from the file being examined, then check if it's in the
        // autoload.files path in composer.json.
        $composer = file_get_contents('composer.json');
        $parsed = json_decode($composer, true);
        $allowed = $parsed['autoload']['files'];

        $appRoot = getcwd();
        $definingFileAbs = $scope->getFile();
        $definingFile = substr($definingFileAbs, strlen($appRoot) + 1);

        if (in_array($definingFile, $allowed, true)) {
            return [];
        }

        // Everything else past this point is forbidden: globally-namespaced
        // function outside of the autoload path.

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
