<?php

/**
 * Custom PHPStan Rule to forbid direct instantiation of ServiceContainer-managed classes.
 *
 * @package   OpenEMR
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<New_>
 */
class ForbiddenInstantiationsRule implements Rule
{
    /**
     * Map of forbidden classes to their suggested replacement
     * @var array<class-string, string>
     */
    private const FORBIDDEN_CLASSES = [
        Common\Logging\SystemLogger::class => ServiceContainer::class . '::getLogger()',
        Common\Crypto\CryptoGen::class => ServiceContainer::class . '::getCrypto()',
        Common\Http\Psr17Factory::class => ServiceContainer::class . '::get{PsrType}Factory()',
    ];

    /**
     * Classes that are exempt from this rule (they must instantiate these classes)
     * @var list<class-string>
     */
    private const EXEMPT_CLASSES = [
        ServiceContainer::class,
    ];

    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Only handle named class instantiation (not dynamic $class or anonymous)
        if (!($node->class instanceof Name)) {
            return [];
        }

        $className = $node->class->toString();

        // Check if this is a forbidden class
        if (!array_key_exists($className, self::FORBIDDEN_CLASSES)) {
            return [];
        }

        // Check if we're in an exempt class
        $classReflection = $scope->getClassReflection();
        if ($classReflection !== null) {
            $currentClass = $classReflection->getName();
            if (in_array($currentClass, self::EXEMPT_CLASSES, true)) {
                return [];
            }
        }

        // Check if we're in an exempt trait
        $traitReflection = $scope->getTraitReflection();
        if ($traitReflection !== null) {
            $currentTrait = $traitReflection->getName();
            if (in_array($currentTrait, self::EXEMPT_CLASSES, true)) {
                return [];
            }
        }

        $suggestion = self::FORBIDDEN_CLASSES[$className];
        $message = sprintf(
            'Direct instantiation of %s is discouraged. Use %s instead.',
            $className,
            $suggestion,
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier('openemr.forbiddenInstantiation')
                ->tip('See src/BC/ServiceContainer.php for service access patterns')
                ->build()
        ];
    }
}
