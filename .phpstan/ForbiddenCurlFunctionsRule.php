<?php

/**
 * Custom PHPStan Rule to Forbid Raw curl_* Functions
 *
 * This rule prevents use of raw curl_* functions as the project is migrating
 * to use Guzzle HTTP client for better testability, error handling, and PSR-7 compliance.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright Copyright (c) 2025 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<FuncCall>
 */
class ForbiddenCurlFunctionsRule implements Rule
{
    /**
     * Pattern to match all curl_* functions
     */
    private const CURL_FUNCTION_PATTERN = '/^curl_/i';

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @param FuncCall $node
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!($node->name instanceof Name)) {
            return [];
        }

        $functionName = $node->name->toString();

        // Check if it's a curl_* function
        if (!preg_match(self::CURL_FUNCTION_PATTERN, $functionName)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Raw curl_* function %s() is forbidden. Use GuzzleHttp\Client or OpenEMR\Common\Http\oeHttp instead.',
                    $functionName
                )
            )
                ->identifier('openemr.forbiddenCurlFunction')
                ->tip('Migrate to GuzzleHttp for better testability and PSR-7 compliance')
                ->build()
        ];
    }
}
