<?php

/**
 * Custom PHPStan Rule to Forbid Use of the `global` Keyword
 *
 * This rule prevents use of the `global` keyword in favor of dependency injection
 * or OEGlobalsBag::getInstance() for accessing global configuration.
 *
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      https://www.open-emr.org
 * @package   OpenEMR
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Global_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Global_>
 */
class ForbiddenGlobalKeywordRule implements Rule
{
    public function getNodeType(): string
    {
        return Global_::class;
    }

    /**
     * @param Global_ $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $varNames = [];
        foreach ($node->vars as $var) {
            if ($var instanceof Node\Expr\Variable && is_string($var->name)) {
                $varNames[] = '$' . $var->name;
            }
        }

        $varList = implode(', ', $varNames);
        $message = sprintf(
            'Use of the "global" keyword is forbidden (%s). Use dependency injection instead.',
            $varList
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.forbiddenGlobalKeyword')
                ->tip('If you need to modify a variable out of function scope, it can be passed by reference.')
                ->addTip('See https://phpstan.org/blog/enhancements-in-handling-parameters-passed-by-reference for tips on further refining type safety when doing so.')
                ->build()
        ];
    }
}
