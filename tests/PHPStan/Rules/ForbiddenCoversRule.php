<?php

/**
 * Custom PHPStan Rule to Forbid Coverage-Restricting Annotations and Attributes
 *
 * Forbids @covers docblock annotations and #[CoversClass]/#[CoversFunction]
 * PHP attributes on test classes and methods. These restrict PHPUnit's coverage
 * attribution to only the listed symbols, which causes test file lines to show
 * 0% in codecov patch coverage reports on test-only PRs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Class_>
 */
class ForbiddenCoversRule implements Rule
{
    private const FORBIDDEN_ATTRIBUTES = [
        'CoversClass',
        'CoversFunction',
    ];

    private const ERROR_MESSAGE = 'Do not use %s. It restricts coverage attribution to listed symbols, '
        . 'causing test lines to show 0%% in codecov patch coverage reports.';

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        // Check class-level @covers docblock
        $docComment = $node->getDocComment();
        if ($docComment !== null && preg_match('/@covers\b/', $docComment->getText()) === 1) {
            $errors[] = RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, '@covers'))
                ->identifier('openemr.forbiddenCovers')
                ->line($docComment->getStartLine())
                ->build();
        }

        // Check class-level attributes
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $attrName = $attr->name->getLast();
                if (in_array($attrName, self::FORBIDDEN_ATTRIBUTES, true)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, '#[' . $attrName . ']'))
                        ->identifier('openemr.forbiddenCovers')
                        ->line($attr->getStartLine())
                        ->build();
                }
            }
        }

        // Check methods
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof ClassMethod) {
                continue;
            }

            // Check method-level @covers docblock
            $methodDoc = $stmt->getDocComment();
            if ($methodDoc !== null && preg_match('/@covers\b/', $methodDoc->getText()) === 1) {
                $errors[] = RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, '@covers'))
                    ->identifier('openemr.forbiddenCovers')
                    ->line($methodDoc->getStartLine())
                    ->build();
            }

            // Check method-level attributes
            foreach ($stmt->attrGroups as $attrGroup) {
                foreach ($attrGroup->attrs as $attr) {
                    $attrName = $attr->name->getLast();
                    if (in_array($attrName, self::FORBIDDEN_ATTRIBUTES, true)) {
                        $errors[] = RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, '#[' . $attrName . ']'))
                            ->identifier('openemr.forbiddenCovers')
                            ->line($attr->getStartLine())
                            ->build();
                    }
                }
            }
        }

        return $errors;
    }
}
