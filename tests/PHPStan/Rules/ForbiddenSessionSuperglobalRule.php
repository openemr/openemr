<?php

/**
 * Custom PHPStan Rule to Forbid Direct $_SESSION Access
 *
 * Forbid direct access to the $_SESSION superglobal in favor of the session
 * wrapper: read through the Symfony SessionInterface returned by
 * SessionWrapperFactory, write through SessionUtil.
 *
 * Raw $_SESSION access is a hidden dependency that bypasses the read_and_close
 * lock cycle, the session bags, and any testing seam. Code that reads it cannot
 * run outside a web SAPI without the superglobal being faked.
 *
 * This mirrors ForbiddenRequestGlobalsRule ($_GET, $_POST, ...) and
 * ForbiddenGlobalsAccessRule ($GLOBALS); $_SESSION gets its own rule because
 * its replacement API — and therefore its guidance — is different.
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
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Variable>
 */
class ForbiddenSessionSuperglobalRule implements Rule
{
    /**
     * Paths allowed to touch $_SESSION directly: the test suite, and the
     * session infrastructure that owns the superglobal so nothing else has
     * to. Matched as substrings of the normalized (forward-slash) path, so
     * they work regardless of the absolute prefix.
     *
     * @var list<string>
     */
    public const DEFAULT_EXEMPT_PATH_FRAGMENTS = [
        '/tests/',
        '/src/Common/Session/',
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
        return Variable::class;
    }

    /**
     * @param Variable $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!is_string($node->name) || $node->name !== '_SESSION') {
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
                'Direct access to $_SESSION is forbidden. Use the session wrapper instead.',
            )
                ->identifier('openemr.forbiddenSessionSuperglobal')
                ->tip('Read: SessionWrapperFactory::getInstance()->getActiveSession()->get($key). Write: SessionUtil::setSession() / SessionUtil::unsetSession(). See src/Common/Session/.')
                ->build(),
        ];
    }
}
