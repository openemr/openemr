<?php

/**
 * Custom PHPStan Rule to Forbid @covers Annotations on Classes
 *
 * This rule prevents use of @covers annotations in test classes as it causes
 * transitively used code to be excluded from coverage reports.
 *
 * @package   OpenEMR
 * @author    GitHub Copilot // AI-generated
 * @copyright Copyright (c) 2025 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Class_>
 */
class NoCoversAnnotationOnClassRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $docComment = $node->getDocComment();

        if ($docComment === null) {
            return [];
        }

        if (preg_match('/@covers\b/', $docComment->getText())) {
            return [
                RuleErrorBuilder::message(
                    'Please do not use the @covers annotation. It excludes transitively used code from coverage reports, ' .
                    'resulting in incomplete coverage information.'
                )
                ->identifier('openemr.noCoversAnnotationOnClass')
                ->build(),
            ];
        }

        return [];
    }
}
