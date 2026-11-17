<?php

/**
 * PortalSmartAppLauncher lists the SMART apps a patient can launch from the patient portal and builds their launch
 * URLs.
 *
 * A portal app is an enabled client registered with the `launch` scope, at least one `patient/` scope, no `user/` or
 * `system/` scope, and an https launch URI. Its launch carries the portal patient and the patient.portal intent, so the
 * authorization server hands it only to that patient's own login.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;

final readonly class PortalSmartAppLauncher
{
    public function __construct(
        private ClientRepository $clientRepository,
        private string $fhirUrl,
    ) {
    }

    /**
     * @return list<ClientEntity>
     */
    public function listPortalApps(): array
    {
        $apps = [];
        foreach ($this->clientRepository->listClientEntities() as $client) {
            if (self::isPortalApp($client)) {
                $apps[] = $client;
            }
        }
        return $apps;
    }

    /**
     * Whether the patient portal offers this client: enabled, with the `launch` scope, at least one `patient/` scope,
     * no `user/` or `system/` scope, and an https launch URI. The portal renders the launch URI as a link, so any other
     * scheme (`javascript:`, plain `http:`) is never listed.
     */
    public static function isPortalApp(ClientEntity $client): bool
    {
        if (!$client->isEnabled() || !$client->hasScope(SmartLaunchController::CLIENT_APP_REQUIRED_LAUNCH_SCOPE)) {
            return false;
        }
        if (!self::isHttpsUri($client->getLaunchUri())) {
            return false;
        }
        $scopes = $client->getScopes();
        if (!is_array($scopes)) {
            return false;
        }
        $hasPatientScope = false;
        foreach ($scopes as $scope) {
            if (!is_string($scope)) {
                continue;
            }
            // A client that can also act for staff or a backend belongs on the staff launch card, not the portal.
            if (str_starts_with($scope, 'user/') || str_starts_with($scope, 'system/')) {
                return false;
            }
            if (str_starts_with($scope, 'patient/')) {
                $hasPatientScope = true;
            }
        }
        return $hasPatientScope;
    }

    /**
     * The client's launch URI with `iss`, `aud` and a launch token for this patient, as SMART App Launch 2.x sends
     * them. A launch URI that already has a query string keeps it, and a fragment stays after the added query.
     */
    public function launchUrl(ClientEntity $client, string $patientUuid): string
    {
        $token = new SMARTLaunchToken($patientUuid);
        $token->setIntent(SMARTLaunchToken::INTENT_PATIENT_PORTAL);
        $launchUri = trim($client->getLaunchUri());
        $fragment = '';
        $hashAt = strpos($launchUri, '#');
        if ($hashAt !== false) {
            $fragment = substr($launchUri, $hashAt);
            $launchUri = substr($launchUri, 0, $hashAt);
        }
        $separator = str_contains($launchUri, '?') ? '&' : '?';

        return $launchUri . $separator . http_build_query([
            'launch' => $token->serialize(),
            'iss' => $this->fhirUrl,
            'aud' => $this->fhirUrl,
        ], '', '&', PHP_QUERY_RFC3986) . $fragment;
    }

    /**
     * Whether the URI is an absolute https URL with a host.
     */
    private static function isHttpsUri(string $uri): bool
    {
        $parts = parse_url(trim($uri));
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return false;
        }
        return strtolower($parts['scheme']) === 'https' && $parts['host'] !== '';
    }
}
