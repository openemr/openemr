<?php

/**
 * Custom PHPStan Rule to Forbid Shell Execution Functions
 *
 * Prevents use of PHP functions that execute shell commands:
 * system(), exec(), shell_exec(), proc_open(), passthru(), popen(), pcntl_exec()
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
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<FuncCall>
 */
class ForbiddenShellExecutionRule implements Rule
{
    /**
     * Shell execution functions and their descriptions
     */
    private const FORBIDDEN_FUNCTIONS = [
        'system' => 'system() executes a shell command and displays output.',
        'exec' => 'exec() executes a shell command.',
        'shell_exec' => 'shell_exec() executes a command via the shell.',
        'proc_open' => 'proc_open() opens a process file pointer.',
        'passthru' => 'passthru() executes a command and passes raw output.',
        'popen' => 'popen() opens a pipe to a process.',
        'pcntl_exec' => 'pcntl_exec() replaces the current process with another.',
    ];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @param FuncCall $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!($node->name instanceof Name)) {
            return [];
        }

        $functionName = $node->name->toLowerString();

        if (!isset(self::FORBIDDEN_FUNCTIONS[$functionName])) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Shell execution function %s() is forbidden. %s Use Symfony\Component\Process\Process with array arguments as a safer alternative.',
                    $functionName,
                    self::FORBIDDEN_FUNCTIONS[$functionName]
                )
            )
                ->identifier('openemr.forbiddenShellExecution')
                ->tip('Pass arguments as an array to Process for proper escaping; avoid fromShellCommandline() or interpolated shell strings')
                ->build()
        ];
    }
}
