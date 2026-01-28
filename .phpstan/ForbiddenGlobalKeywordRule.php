<?php

/**
 * Custom PHPStan Rule to Forbid Use of the `global` Keyword
 *
 * This rule prevents use of the `global` keyword in favor of dependency injection
 * or OEGlobalsBag::getInstance() for accessing global configuration.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2025 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Skip tests
        if (str_contains($scope->getFile(), DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
            return [];
        }

        $varNames = [];
        foreach ($node->vars as $var) {
            if ($var instanceof Node\Expr\Variable && is_string($var->name)) {
                $varNames[] = '$' . $var->name;
            }
        }

        $varList = implode(', ', $varNames);
        $message = sprintf(
            'Use of the "global" keyword is forbidden (%s). Use dependency injection or OEGlobalsBag::getInstance() instead.',
            $varList
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.forbiddenGlobalKeyword')
                ->tip('See src/Core/OEGlobalsBag.php for accessing global configuration values.')
                ->build()
        ];
    }
}
