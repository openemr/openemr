<?php

/**
 * Custom PHPStan Rule to Forbid Opting Into a Writable Session
 *
 * Setting $sessionAllowWrite before interface/globals.php keeps the session
 * open, with its lock held, for the whole request. Every other request in the
 * same session waits behind it, and under a lock-wait limit those requests
 * fail. A read-only session already handles writes: SessionUtil::setSession()
 * reopens the session, writes, and releases the lock straight away.
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
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Coalesce;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Expr>
 */
final readonly class ForbiddenSessionAllowWriteRule implements Rule
{
    /**
     * Paths allowed to set the flag, matched as substrings of the normalized
     * (forward-slash) path.
     *
     * @var list<string>
     */
    public const DEFAULT_EXEMPT_PATH_FRAGMENTS = [
        '/tests/',
    ];

    /**
     * @param list<string> $exemptPathFragments
     */
    public function __construct(
        private array $exemptPathFragments = self::DEFAULT_EXEMPT_PATH_FRAGMENTS,
    ) {
    }

    public function getNodeType(): string
    {
        // Assign and AssignOp\Coalesce (??=) share no narrower parent.
        return Expr::class;
    }

    /**
     * @param Expr $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Assign && !$node instanceof Coalesce) {
            return [];
        }

        if (!$node->var instanceof Variable || $node->var->name !== 'sessionAllowWrite') {
            return [];
        }

        // globals.php reads the flag with empty(), so a value that is always
        // falsy keeps the session read-only. The type of the whole expression
        // is the value the variable ends up holding, for = and ??= alike.
        if ($scope->getType($node)->toBoolean()->isFalse()->yes()) {
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
                'Setting $sessionAllowWrite holds the session lock for the whole request.',
            )
                ->identifier('openemr.forbiddenSessionAllowWrite')
                ->tip('Leave the session read-only and write through SessionUtil::setSession(), which reopens it only for the write. A script that must write after output has started keeps the flag with // @phpstan-ignore openemr.forbiddenSessionAllowWrite (reason).')
                ->build(),
        ];
    }
}
