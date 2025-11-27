<?php

/**
 * Custom PHPStan Rule to Forbid Direct $GLOBALS Access
 *
 * This rule prevents direct $GLOBALS access in favor of OEGlobalsBag::getInstance().
 * OEGlobalsBag provides better testability, type safety, and enables future integration
 * with external secrets providers.
 *
 * @package   OpenEMR
 * @author    GitHub Copilot // AI-generated
 * @copyright Copyright (c) 2025 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<ArrayDimFetch>
 */
class ForbiddenGlobalsAccessRule implements Rule
{
    public function getNodeType(): string
    {
        return ArrayDimFetch::class;
    }

    /**
     * @param ArrayDimFetch $node
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Skip tests
        if (str_contains($scope->getFile(), DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
            return [];
        }

        // Allow $GLOBALS access in the OEGlobalsBag class itself
        if ($scope->isInClass() && $scope->getClassReflection()->getName() === 'OpenEMR\\Core\\OEGlobalsBag') {
            return [];
        }

        // Check if this is accessing $GLOBALS
        if (!($node->var instanceof Variable)) {
            return [];
        }

        if (!is_string($node->var->name) || $node->var->name !== 'GLOBALS') {
            return [];
        }

        $message = 'Direct access to $GLOBALS is forbidden. Use OEGlobalsBag::getInstance()->get() instead.';

        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.forbiddenGlobalsAccess')
                ->tip('For encrypted values, OEGlobalsBag handles decryption automatically. See src/Core/OEGlobalsBag.php')
                ->build()
        ];
    }
}
