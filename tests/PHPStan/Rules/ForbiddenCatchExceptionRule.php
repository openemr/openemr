<?php

/**
 * Custom PHPStan Rule to Forbid catch (Exception)
 *
 * This rule prevents catching the generic Exception class, which misses Error subclasses.
 * Use \Throwable to catch all throwable values, or catch specific exception types.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Catch_>
 */
class ForbiddenCatchExceptionRule implements Rule
{
    public function getNodeType(): string
    {
        return Catch_::class;
    }

    /**
     * @param Catch_ $node
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach ($node->types as $type) {
            $typeName = $type->toString();

            // Check for generic Exception (with or without leading backslash)
            if ($typeName === 'Exception' || $typeName === '\Exception') {
                $errors[] = RuleErrorBuilder::message(
                    'Catching generic Exception misses Error subclasses. Use \Throwable to catch all throwable values, or catch a specific exception type.'
                )
                    ->identifier('openemr.catchException')
                    ->tip('See https://github.com/openemr/openemr/issues/10618')
                    ->build();
            }
        }

        return $errors;
    }
}
