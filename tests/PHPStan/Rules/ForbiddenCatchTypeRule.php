<?php

declare(strict_types=1);

/**
 * Custom PHPStan Rule to Forbid Overbroad catch Types
 *
 * PHP's throwable hierarchy has two branches: `\Exception` (for recoverable
 * application-level failures) and `\Error` (for programmer errors —
 * `\TypeError`, `\ParseError`, `\ArithmeticError`, etc.) — both implementing
 * `\Throwable`. Code should never catch `\Throwable` or anything in the
 * `\Error` branch: doing so masks programmer bugs as "handled" failures and
 * prevents the global exception handler from logging and responding
 * appropriately.
 *
 * Catches of `\Exception` and its subclasses are allowed. To forbid specific
 * further types (e.g. ban a particular domain exception from being caught
 * broadly), add them to {@see self::ADDITIONALLY_FORBIDDEN}.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Catch_>
 */
class ForbiddenCatchTypeRule implements Rule
{
    /**
     * Additional FQCNs (or their subclasses) forbidden from being caught.
     * Extend this list to block further types beyond the \Error branch.
     *
     * @var list<class-string>
     */
    private const ADDITIONALLY_FORBIDDEN = [];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Catch_::class;
    }

    /**
     * @param Catch_ $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        foreach ($node->types as $typeName) {
            $error = $this->checkType($typeName);
            if ($error !== null) {
                $errors[] = $error;
            }
        }
        return $errors;
    }

    private function checkType(Name $typeName): ?IdentifierRuleError
    {
        $fqn = ltrim($typeName->toString(), '\\');
        if (!$this->reflectionProvider->hasClass($fqn)) {
            return null;
        }
        $classRef = $this->reflectionProvider->getClass($fqn);

        if (!$classRef->is(\Exception::class)) {
            return $this->error(
                $typeName,
                sprintf(
                    'Catching %s is forbidden: it captures PHP Errors (TypeError, ParseError, etc.) which should propagate to the global exception handler.',
                    $fqn,
                ),
            );
        }

        foreach (self::ADDITIONALLY_FORBIDDEN as $forbidden) {
            if ($classRef->is(ltrim($forbidden, '\\'))) {
                return $this->error(
                    $typeName,
                    sprintf('Catching %s is forbidden (matches configured forbidden type %s).', $fqn, $forbidden),
                );
            }
        }

        return null;
    }

    private function error(Name $typeName, string $message): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('openemr.forbiddenCatchType')
            ->line($typeName->getStartLine())
            ->tip('Narrow the catch to \Exception or a more specific subclass. Use "throw;" at the end of the handler if you need to observe/log without swallowing the failure.')
            ->build();
    }
}
