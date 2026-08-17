<?php

/**
 * Custom PHPStan Rule to Forbid Direct Request Superglobal Access
 *
 * Forbid direct access to $_GET, $_POST, $_REQUEST, $_FILES, $_COOKIE, and
 * $_SERVER in favor of Symfony's Request object.
 *
 * Raw superglobals bypass validation, type narrowing, and testing seams. The
 * Request object's typed InputBag getters provide all three, with a dedicated
 * bag per superglobal — query, request, cookies, files, server — including
 * arrays and uploaded files.
 *
 * $_REQUEST is the one exception: Symfony keeps query and body data separate
 * and has no merged bag, so converting a $_REQUEST read means choosing the
 * source it actually came from. That choice depends on the request_order ini
 * setting and must be made per call site, not mechanically.
 *
 * filter_input() is not an acceptable target. It reaches only four of the six
 * superglobals (no INPUT_REQUEST, no $_FILES equivalent), addresses only
 * top-level keys, and needs an explicit FILTER_REQUIRE_ARRAY flag to return an
 * array at all. Its return type is the deeper problem: mixed, varying with the
 * filter passed, with failure signalled in-band by sentinels rather than by
 * type — false for "filter failed", null for "not set", and
 * FILTER_NULL_ON_FAILURE reverses the two. `??` lets the false failure value
 * through, while `?:` swallows legitimate falsy input like '0', so every call
 * site needs an explicit === false / === null check against the filter it
 * chose. Converting a superglobal to filter_input() silences this rule while
 * moving the problem to a surface with no rule covering it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Variable>
 */
class ForbiddenRequestGlobalsRule implements Rule
{
    /** @var list<string> */
    private const FORBIDDEN_SUPERGLOBALS = [
        '_GET',
        '_POST',
        '_REQUEST',
        '_FILES',
        '_COOKIE',
        '_SERVER',
    ];

    /**
     * Classes that bridge raw superglobals into an abstraction layer.
     * These exist specifically to absorb globals so nothing else has to.
     *
     * @var list<class-string>
     */
    private const ABSTRACTION_CLASSES = [
        \OpenEMR\Common\Http\HttpRestRequest::class,
        \OpenEMR\Common\Http\RawPostParser::class,
        \OpenEMR\Core\OEEnvBag::class,
    ];

    public function getNodeType(): string
    {
        return Variable::class;
    }

    /**
     * @param Variable $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!is_string($node->name)) {
            return [];
        }

        if (!in_array($node->name, self::FORBIDDEN_SUPERGLOBALS, true)) {
            return [];
        }

        // Skip tests
        if (str_contains($scope->getFile(), DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
            return [];
        }

        // Allow access inside abstraction-layer classes that exist to wrap these globals
        if ($scope->isInClass()) {
            $className = $scope->getClassReflection()->getName();
            if (in_array($className, self::ABSTRACTION_CLASSES, true)) {
                return [];
            }
        }

        $superglobal = '$' . $node->name;

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Direct access to %s is forbidden. Use Symfony\'s Request object instead.',
                    $superglobal,
                ),
            )
                ->identifier('openemr.forbiddenRequestGlobals')
                ->tip('Get the request with OpenEMR\Common\Http\CurrentRequest::get(), or take one as a constructor parameter. Then read the bag matching the superglobal: $_GET -> $request->query->getString(), $_POST -> $request->request->getInt(), arrays -> $request->request->all(), $_FILES -> $request->files->get(), $_COOKIE -> $request->cookies->getString(), $_SERVER -> $request->server->getString() or a named accessor such as $request->getMethod(), HTTP headers -> $request->headers->get(). $_REQUEST has no equivalent bag: decide whether the value comes from the query string or the body and read that bag directly. Do not convert to filter_input() — it is not a valid target for this rule.')
                ->build(),
        ];
    }
}
