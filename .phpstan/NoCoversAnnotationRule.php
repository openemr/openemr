<?php

/**
 * Custom PHPStan Rule to Forbid @covers Annotations on Methods
 *
 * This rule prevents use of @covers annotations in test methods as it causes
 * transitively used code to be excluded from coverage reports.
 *
 * @package   OpenEMR
 * @author    GitHub Copilot // AI-generated
 * @copyright Copyright (c) 2025 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassMethodNode>
 */
class NoCoversAnnotationRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    /**
     * @param InClassMethodNode $node
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $methodReflection = $node->getMethodReflection();
        $docComment = $methodReflection->getDocComment();

        if ($docComment === null) {
            return [];
        }

        if (preg_match('/@covers\b/', $docComment)) {
            return [
                RuleErrorBuilder::message(
                    'The @covers annotation should not be used as it excludes transitively used code from coverage reports, ' .
                    'resulting in incomplete coverage information.'
                )
                ->identifier('openemr.noCoversAnnotation')
                ->build(),
            ];
        }

        return [];
    }
}
