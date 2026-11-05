<?php

/**
 * Renders findings as SARIF 2.1.0 for GitHub code scanning
 *
 * Every finding is a warning, so the upload never trips a code scanning merge
 * rule; the pull request check enforces through the command's exit code.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final class SarifReport
{
    public const TOOL_NAME = 'openemr-license-headers';
    private const SCHEMA = 'https://json.schemastore.org/sarif-2.1.0.json';
    private const INFORMATION_URI = 'https://github.com/openemr/openemr/blob/master/.license-headers.yaml';
    private const LEVEL = 'warning';

    public function render(ScanResult $result): string
    {
        $rules = [];
        foreach (Rule::cases() as $rule) {
            $rules[] = [
                'id' => $rule->value,
                'name' => $rule->name,
                'shortDescription' => ['text' => $rule->title()],
                'help' => ['text' => $rule->help()],
                'defaultConfiguration' => ['level' => self::LEVEL],
            ];
        }

        $results = [];
        foreach ($result->findings() as $finding) {
            $results[] = [
                'ruleId' => $finding->rule->value,
                'level' => self::LEVEL,
                'message' => ['text' => $finding->message . ' ' . $finding->rule->help()],
                'locations' => [[
                    'physicalLocation' => [
                        'artifactLocation' => ['uri' => $finding->path],
                        'region' => ['startLine' => $finding->line],
                    ],
                ]],
            ];
        }

        return $this->encode([
            '$schema' => self::SCHEMA,
            'version' => '2.1.0',
            'runs' => [[
                'tool' => ['driver' => [
                    'name' => self::TOOL_NAME,
                    'informationUri' => self::INFORMATION_URI,
                    'rules' => $rules,
                ]],
                'results' => $results,
            ]],
        ]);
    }

    /**
     * @param array<string, mixed> $document
     */
    private function encode(array $document): string
    {
        return json_encode($document, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }
}
