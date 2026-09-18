<?php

/**
 * PHPStan rule: appointment status updates must also advance pc_time.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules\Sql;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags SQL sinks that UPDATE pc_apptstatus without SET pc_time.
 *
 * Status-only calendar writers should call
 * AppointmentService::persistAppointmentStatus() so FHIR lastUpdated cannot
 * stall. Full event editors that already set both columns are allowed.
 *
 * @implements Rule<CallLike>
 */
final readonly class AppointmentStatusTimestampRule implements Rule
{
    public function __construct(
        private SqlSinkResolver $sinks,
    ) {
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param CallLike $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $file = $scope->getFile();
        if (str_contains($file, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
            return [];
        }

        if (!$this->sinks->isSink($node)) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $constantStrings = $scope->getType($args[0]->value)->getConstantStrings();
        if ($constantStrings === []) {
            return [];
        }

        foreach ($constantStrings as $sqlType) {
            if ($this->statusUpdateMissingTimestamp($sqlType->getValue())) {
                return [
                    RuleErrorBuilder::message(
                        'Appointment status updates must also set pc_time. '
                        . 'Use AppointmentService::persistAppointmentStatus().'
                    )
                        ->identifier('openemr.appointmentStatusTimestamp')
                        ->build(),
                ];
            }
        }

        return [];
    }

    private function statusUpdateMissingTimestamp(string $sql): bool
    {
        if (!preg_match('/\bUPDATE\b/i', $sql)) {
            return false;
        }

        if (!preg_match('/\bSET\b(?<set>.*?)(?:\bWHERE\b|$)/is', $sql, $matches)) {
            return false;
        }

        $set = $matches['set'];
        return preg_match('/`?pc_apptstatus`?\s*=/i', $set) === 1
            && preg_match('/`?pc_time`?\s*=/i', $set) !== 1;
    }
}
