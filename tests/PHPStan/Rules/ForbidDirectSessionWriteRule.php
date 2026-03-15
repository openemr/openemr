<?php

/**
 * Custom PHPStan Rule to Forbid Direct Session Writes
 *
 * Prevents use of $session->set(), $session->remove(), and $session->clear()
 * outside of SessionUtil and other whitelisted contexts. These methods bypass
 * the read_and_close reopen-write-close cycle and silently lose data when
 * the session was opened with read_and_close.
 *
 * Use SessionUtil::setSession() / SessionUtil::unsetSession() instead.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code AI
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
class ForbidDirectSessionWriteRule implements Rule
{
    private const FORBIDDEN_METHODS = ['set', 'remove', 'clear'];

    private const SESSION_INTERFACE = \Symfony\Component\HttpFoundation\Session\SessionInterface::class;

    /**
     * Files that are allowed to call $session->set/remove/clear directly.
     * Uses suffix matching so it works regardless of absolute path prefix.
     */
    private const WHITELISTED_FILE_SUFFIXES = [
        'src/Common/Session/',
        // Auth flows — always called from writable session contexts
        'src/Common/Auth/',
        'src/Common/Csrf/CsrfUtils.php',
        'library/auth.inc.php',
        // Login/setup flows with $sessionAllowWrite = true
        'interface/login/login.php',
        'interface/main/main_screen.php',
        'interface/main/tabs/main.php',
        'interface/usergroup/mfa_totp.php',
        'interface/globals.php',
        'setup.php',
        'sql_upgrade.php',
        'ccdaservice/',
        // Files with $sessionAllowWrite = true
        'interface/reports/appointments_report.php',
        // CLI command uses standalone mock session (not factory session)
        'src/Common/Command/GenerateAccessTokenCommand.php',
        // REST/API controllers manage their own sessions
        'src/RestControllers/',
        // Services called from CLI context
        'src/Services/Cda/',
        // Test files — session setup in tests uses writable mock sessions
        'tests/',
        // Test/dev utilities
        'src/Cqm/test.php',
        // Modules with $sessionAllowWrite = true
        'oe-module-faxsms/',
    ];

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
        if (!($node->name instanceof Identifier)) {
            return [];
        }

        $methodName = $node->name->name;

        if (!in_array($methodName, self::FORBIDDEN_METHODS, true)) {
            return [];
        }

        // Check if the method is being called on a SessionInterface object
        $callerType = $scope->getType($node->var);
        $sessionType = new ObjectType(self::SESSION_INTERFACE);
        if (!$sessionType->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        // Check if the file is whitelisted
        $file = $scope->getFile();
        foreach (self::WHITELISTED_FILE_SUFFIXES as $suffix) {
            if (str_contains($file, $suffix)) {
                return [];
            }
        }

        $replacement = match ($methodName) {
            'set' => 'SessionUtil::setSession()',
            'remove' => 'SessionUtil::unsetSession()',
            'clear' => '$session->clear() requires a writable session (setSessionReadOnly(false))',
        };

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Direct $session->%s() is forbidden — it silently fails on read_and_close sessions. Use %s instead.',
                    $methodName,
                    $replacement,
                )
            )
                ->identifier('openemr.forbidDirectSessionWrite')
                ->tip('SessionUtil methods use withWritableSession() to auto-reopen the session lock before writing.')
                ->build(),
        ];
    }
}
