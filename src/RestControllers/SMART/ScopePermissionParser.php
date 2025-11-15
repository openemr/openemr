<?php

/**
 * Scope Permission Parser
 * TODO: @adunsulag this needs to be consolidated later with ServerScopeListEntity
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    AI Generated - Claude (Anthropic)
 * @copyright Copyright (c) 2025 - Public Domain for AI generated content
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// ============================================================================
// AI-GENERATED CODE START
// Generated using Claude (Anthropic) on 2025-01-15
// This helper class parses OAuth scopes and structures them for improved UI display
// ============================================================================

namespace OpenEMR\RestControllers\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;

class ScopePermissionParser
{
    private ScopeRepository $scopeRepository;

    // Human-readable labels for restricted scope categories
    private const RESTRICTION_LABELS = [
        // Condition categories (ONC Required)
        'http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern' => 'Health Concerns',
        'http://terminology.hl7.org/CodeSystem/condition-category|encounter-diagnosis' => 'Encounter Diagnoses',
        'http://terminology.hl7.org/CodeSystem/condition-category|problem-list-item' => 'Problem List Items',

        // Observation categories (ONC Required)
        'http://terminology.hl7.org/CodeSystem/observation-category|clinical-test' => 'Clinical Test',
        'http://terminology.hl7.org/CodeSystem/observation-category|laboratory' => 'Laboratory',
        'http://terminology.hl7.org//CodeSystem-observation-category|social-history' => 'Social History',
        'http://hl7.org/fhir/us/core/CodeSystem/us-core-category|sdoh' => 'Social Determinants of Health (SDOH)',
        'http://terminology.hl7.org/CodeSystem/observation-category|survey' => 'Survey',
        'http://terminology.hl7.org/CodeSystem/observation-category|vital-signs' => 'Vital Signs',

        // DocumentReference categories
        'http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category|clinical-note' => 'Clinical Notes',
    ];

    // ONC Required sub-resource scopes by resource type
    private const ONC_REQUIRED_RESTRICTIONS = [
        'Condition' => [
            'http://terminology.hl7.org/CodeSystem/condition-category|encounter-diagnosis',
            'http://terminology.hl7.org/CodeSystem/condition-category|problem-list-item',
            'http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern',
        ],
        'Observation' => [
            'http://terminology.hl7.org/CodeSystem/observation-category|clinical-test',
            'http://terminology.hl7.org/CodeSystem/observation-category|laboratory',
            'http://terminology.hl7.org//CodeSystem-observation-category|social-history',
            'http://hl7.org/fhir/us/core/CodeSystem/us-core-category|sdoh',
            'http://terminology.hl7.org/CodeSystem/observation-category|survey',
            'http://terminology.hl7.org/CodeSystem/observation-category|vital-signs',
        ],
    ];

    // CRUDS action labels
    private const ACTION_LABELS = [
        'c' => 'Create',
        'r' => 'Read/View',
        'u' => 'Update/Modify',
        'd' => 'Delete',
        's' => 'Search',
    ];

    public function __construct(ScopeRepository $scopeRepository)
    {
        $this->scopeRepository = $scopeRepository;
    }

    /**
     * Parse scopes into a structured format for improved UI display
     * Per ONC requirements, unrestricted resource scopes for Condition/Observation
     * must show ALL sub-resource categories for patient authorization.
     *
     * @param array $scopes Array of scope strings
     * @return array Structured scope data organized by resource
     */
    public function parseScopes(array $scopes): array
    {
        $structuredScopes = [];
        $serverScopeList = new ServerScopeListEntity();

        // Track which resources have unrestricted scopes
        $unrestrictedResources = [];

        foreach ($scopes as $scopeString) {
            $parsed = $this->parseScopeString($scopeString);

            if (!$parsed) {
                continue;
            }

            $resource = $parsed['resource'];
            $context = $parsed['context'];

            // Skip non-resource scopes (handled elsewhere)
            if (empty($resource) || in_array($scopeString, ['openid', 'fhirUser', 'online_access', 'offline_access', 'launch', 'launch/patient', 'api:oemr', 'api:fhir', 'api:port'])) {
                continue;
            }

            // Initialize resource structure if not exists
            if (!isset($structuredScopes[$resource])) {
                $structuredScopes[$resource] = [
                    'name' => $resource,
                    'description' => $serverScopeList->lookupDescriptionForResourceScope($resource, $context),
                    'context' => $context,
                    'actions' => [
                        'c' => ['enabled' => false],
                        'r' => ['enabled' => false],
                        'u' => ['enabled' => false],
                        'd' => ['enabled' => false],
                        's' => ['enabled' => false],
                    ],
                    'restrictions' => [],
                    'hasRestrictions' => false,
                    'isUnrestricted' => false, // Will be set to true if no restrictions in scope
                ];
            }

            // Parse actions - mark them as enabled
            foreach ($parsed['actions'] as $action) {
                if (isset($structuredScopes[$resource]['actions'][$action])) {
                    $structuredScopes[$resource]['actions'][$action]['enabled'] = true;
                }
            }

            // Handle restrictions
            if (!empty($parsed['restriction'])) {
                // Specific restriction requested
                $structuredScopes[$resource]['hasRestrictions'] = true;
                $restrictionKey = $parsed['restriction'];
                $restrictionLabel = self::RESTRICTION_LABELS[$restrictionKey] ?? $restrictionKey;

                if (!isset($structuredScopes[$resource]['restrictions'][$restrictionKey])) {
                    $structuredScopes[$resource]['restrictions'][$restrictionKey] = [
                        'label' => $restrictionLabel,
                        'value' => $restrictionKey,
                        'selected' => true,
                        'actions' => $parsed['actions'],
                    ];
                }
            } else {
                // No restriction in this scope - mark as unrestricted
                if (!isset($unrestrictedResources[$resource])) {
                    $unrestrictedResources[$resource] = true;
                }
            }
        }

        // ONC Compliance: For unrestricted Condition/Observation scopes,
        // populate ALL required sub-resource categories
        foreach ($unrestrictedResources as $resource => $true) {
            if (isset(self::ONC_REQUIRED_RESTRICTIONS[$resource])) {
                $structuredScopes[$resource]['hasRestrictions'] = true;
                $structuredScopes[$resource]['isUnrestricted'] = true;

                // Add all ONC required restrictions for this resource
                foreach (self::ONC_REQUIRED_RESTRICTIONS[$resource] as $restrictionUri) {
                    if (!isset($structuredScopes[$resource]['restrictions'][$restrictionUri])) {
                        $restrictionLabel = self::RESTRICTION_LABELS[$restrictionUri] ?? $restrictionUri;

                        $structuredScopes[$resource]['restrictions'][$restrictionUri] = [
                            'label' => $restrictionLabel,
                            'value' => $restrictionUri,
                            'selected' => true, // All selected by default for ONC compliance
                            'actions' => array_keys(array_filter(
                                $structuredScopes[$resource]['actions'],
                                fn($action) => $action['enabled']
                            )),
                        ];
                    }
                }
            }
        }

        // Sort resources alphabetically
        ksort($structuredScopes);

        return $structuredScopes;
    }

    /**
     * Parse a single scope string into components
     *
     * @param string $scopeString The scope string to parse
     * @return array|null Parsed scope components or null if invalid
     */
    private function parseScopeString(string $scopeString): ?array
    {
        // Handle V1 format: patient/Resource.read, user/Resource.write
        if (preg_match('/^(patient|user|system)\/([^.]+)\.(read|write)$/', $scopeString, $matches)) {
            return [
                'context' => $matches[1],
                'resource' => $matches[2],
                'actions' => $matches[3] === 'read' ? ['r', 's'] : ['c', 'u'],
                'restriction' => null,
            ];
        }

        // Handle V2 format with restrictions: patient/Resource.cruds?restriction
        if (preg_match('/^(patient|user|system)\/([^.]+)\.([cruds]+)(?:\?(.+))?$/', $scopeString, $matches)) {
            $actions = str_split($matches[3]);
            $restriction = null;

            if (!empty($matches[4])) {
                // Parse restriction (e.g., category=http://...)
                if (preg_match('/category=(.+)/', $matches[4], $restrictionMatches)) {
                    $restriction = $restrictionMatches[1];
                }
            }

            return [
                'context' => $matches[1],
                'resource' => $matches[2],
                'actions' => $actions,
                'restriction' => $restriction,
            ];
        }

        // Handle operations: patient/Resource.$operation
        if (preg_match('/^(patient|user|system)\/([^.]+)\.(\$[^?]+)(?:\?(.+))?$/', $scopeString, $matches)) {
            return [
                'context' => $matches[1],
                'resource' => $matches[2],
                'actions' => ['r'], // Operations typically require read
                'restriction' => null,
                'operation' => $matches[3],
            ];
        }

        return null;
    }

    /**
     * Get human-readable label for an action
     *
     * @param string $action Single character action code
     * @return string Human-readable label
     */
    public static function getActionLabel(string $action): string
    {
        return self::ACTION_LABELS[$action] ?? $action;
    }

    /**
     * Get human-readable label for a restriction
     *
     * @param string $restriction Restriction URI
     * @return string Human-readable label
     */
    public static function getRestrictionLabel(string $restriction): string
    {
        return self::RESTRICTION_LABELS[$restriction] ?? $restriction;
    }

    /**
     * Get all available restrictions for a resource
     *
     * @param string $resource Resource name
     * @return array Available restrictions
     */
    public static function getAvailableRestrictions(string $resource): array
    {
        $restrictedScopes = [
            'Condition' => [
                'http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern',
                'http://terminology.hl7.org/CodeSystem/condition-category|encounter-diagnosis',
                'http://terminology.hl7.org/CodeSystem/condition-category|problem-list-item',
            ],
            'Observation' => [
                'http://hl7.org/fhir/us/core/CodeSystem/us-core-category|sdoh',
                'http://terminology.hl7.org//CodeSystem-observation-category|social-history',
                'http://terminology.hl7.org/CodeSystem/observation-category|laboratory',
                'http://terminology.hl7.org/CodeSystem/observation-category|survey',
                'http://terminology.hl7.org/CodeSystem/observation-category|vital-signs',
            ],
            'DocumentReference' => [
                'http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category|clinical-note',
            ],
        ];

        return $restrictedScopes[$resource] ?? [];
    }
}

// ============================================================================
// AI-GENERATED CODE END
// ============================================================================
