<?php

/**
 * Custom PHPStan Rule to Forbid Direct Authorization-Header Reads Outside the
 * Bearer-Extraction Path.
 *
 * The Authorization header carries bearer tokens. Reading it from an ad-hoc
 * call site means a second code path exists where the token flows through
 * un-validated — bypassing the signature + revocation + scope checks that
 * BearerTokenAuthorizationStrategy performs. Every real inbound-token read
 * should go through that strategy; this rule keeps that invariant.
 *
 * Covers the shape:
 *     $request->headers->get('Authorization')
 *     $request->headers->get('authorization')
 *
 * $_SERVER['HTTP_AUTHORIZATION'] is already blocked by
 * ForbiddenRequestGlobalsRule, so this rule narrowly targets the Symfony
 * HeaderBag read path.
 *
 * Exempt paths: /tests/ plus the auth-extraction classes themselves.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<MethodCall>
 */
class ForbiddenAuthorizationHeaderReadRule implements Rule
{
    /**
     * Paths allowed to read the Authorization header directly: the test
     * suite, and the auth-extraction classes that own bearer handling so
     * nothing else has to. Matched as substrings of the normalized
     * (forward-slash) path.
     *
     * @var list<string>
     */
    public const DEFAULT_EXEMPT_PATH_FRAGMENTS = [
        '/tests/',
        '/src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
        '/src/RestControllers/AuthorizationController.php',
        '/src/RestControllers/TokenIntrospectionRestController.php',
    ];

    /**
     * @param list<string> $exemptPathFragments
     */
    public function __construct(
        private readonly array $exemptPathFragments = self::DEFAULT_EXEMPT_PATH_FRAGMENTS,
    ) {
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!($node->name instanceof Identifier) || $node->name->name !== 'get') {
            return [];
        }
        if (!isset($node->args[0]) || !($node->args[0] instanceof Arg)) {
            return [];
        }
        $argValue = $node->args[0]->value;
        if (!($argValue instanceof String_)) {
            return [];
        }
        if (strcasecmp($argValue->value, 'authorization') !== 0) {
            return [];
        }
        // Narrow to Symfony HeaderBag-shaped calls: `->headers->get(...)`.
        if (!($node->var instanceof PropertyFetch)) {
            return [];
        }
        if (!($node->var->name instanceof Identifier) || $node->var->name->name !== 'headers') {
            return [];
        }

        $file = str_replace('\\', '/', $scope->getFile());
        foreach ($this->exemptPathFragments as $fragment) {
            if (str_contains($file, $fragment)) {
                return [];
            }
        }

        return [
            RuleErrorBuilder::message(
                'Direct read of the Authorization header is forbidden. Bearer tokens must flow through BearerTokenAuthorizationStrategy so the signature, revocation, scope, and trusted-user checks run.',
            )
                ->identifier('openemr.forbiddenAuthorizationHeaderRead')
                ->tip('If you need the authenticated user or client, read it from the request attributes set by BearerTokenAuthorizationStrategy (userId, clientId, oauth_scopes). If you are implementing a new authorization strategy, add it to the exempt list in ForbiddenAuthorizationHeaderReadRule::DEFAULT_EXEMPT_PATH_FRAGMENTS.')
                ->build(),
        ];
    }
}
