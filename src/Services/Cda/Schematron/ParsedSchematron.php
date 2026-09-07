<?php

/**
 * ParsedSchematron - fully parsed schematron document, ready for evaluation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

final readonly class ParsedSchematron
{
    /**
     * @param array<string, string> $namespaceMap prefix => uri
     * @param array<string, list<string>> $patternRuleMap patternId => [ruleId, ...]
     * @param array<string, ParsedRule> $ruleMap ruleId => rule
     */
    public function __construct(
        public array $namespaceMap,
        public array $patternRuleMap,
        public array $ruleMap,
    ) {
    }
}
