<?php

/**
 * Handles the building and aggregation of SMART on FHIR session context information that are returned in the Access
 * Token request response body.  These include context information such as selected patient, launch, encounter, etc.
 *
 * @see http://hl7.org/fhir/smart-app-launch/scopes-and-launch-context/index.html
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2022 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use OpenEMR\RestControllers\SMART\SMARTAuthorizationController;
use OpenEMR\Services\FHIR\UtilsService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use Exception;

class SMARTSessionTokenContextBuilder
{
    use SystemLoggerAwareTrait;

    public function __construct(private OEGlobalsBag $globalsBag, private SessionInterface $session)
    {
    }

    /**
     * @return array
     * @throws OAuthServerException
     */
    public function getEHRLaunchContext(): array
    {
        $launch = $this->session->get('launch');
        $this->getSystemLogger()->debug("SMARTSessionTokenContextBuilder->getEHRLaunchContext() launch context is", ['launch' => $launch]);
        if (empty($launch)) {
            return [];
        }
        $context = [];
        try {
            $launchToken = SMARTLaunchToken::deserializeToken($launch);
            $this->getSystemLogger()->debug(
                "SMARTSessionTokenContextBuilder->getEHRLaunchContext() decoded launch context is",
                ['patient' => $launchToken->getPatient(), 'encounter' => $launchToken->getEncounter(), 'intent' => $launchToken->getIntent()]
            );

            // we assume that if a patient is provided we are already displaying the patient
            // we may in the future need to adjust the need_patient_banner depending on the 'intent' chosen.
            if (!empty($launchToken->getPatient())) {
                $context['patient'] = $launchToken->getPatient();
                $context['need_patient_banner'] = false;
            }
            if (!empty($launchToken->getEncounter())) {
                $context['encounter'] = $launchToken->getEncounter();
            }
            if (!empty($launchToken->getIntent())) {
                $context['intent'] = $launchToken->getIntent();
            }
            if (!empty($launchToken->getAppointmentUuid())) {
                $context['fhirContext'] = [UtilsService::createRelativeReference('Appointment', $launchToken->getAppointmentUuid())];
            }
            $context['smart_style_url'] = $this->getSmartStyleURL();
        } catch (Exception $ex) {
            $this->getSystemLogger()->error("SMARTSessionTokenContextBuilder->getAccessTokenContextParameters() Failed to decode launch context parameter", ['error' => $ex->getMessage()]);
            throw new OAuthServerException("Invalid launch parameter", 0, 'invalid_launch_context');
        }
        $this->getSystemLogger()->debug("SMARTSessionTokenContextBuilder->getEHRLaunchContext() ehr launch context is ", $context);
        return $context;
    }

    public function getContextForScopesWithExistingContext(array $context, array $scopes): array
    {
        $returnContext = [];
        $populateKeys = ['patient'];
        if ($this->isStandaloneLaunchPatientRequest($scopes)) {
            $returnContext = [
                'need_patient_banner' => true
                ,'smart_style_url' => $this->getSmartStyleURL()
            ];
        } else if ($this->isEHRLaunchRequest($scopes)) {
            $returnContext = [
                'need_patient_banner' => false
                ,'smart_style_url' => $this->getSmartStyleURL()
            ];
            $populateKeys[] = 'encounter';
            $populateKeys[] = 'intent';
        }

        // populate any values we have from our orig context into our return array
        foreach ($populateKeys as $item) {
            if (isset($context[$item])) {
                $returnContext[$item] = $context[$item];
            }
        }
        return $returnContext;
    }

    /**
     * @param $scopes
     * @return array
     * @throws OAuthServerException
     */
    public function getContextForScopes($scopes): array
    {
        $context = [];
        $this->getSystemLogger()->debug("SMARTSessionTokenContextBuilder->getContextForScopes()");
        if ($this->isStandaloneLaunchPatientRequest($scopes)) {
            $context = $this->getStandaloneLaunchContext();
        } else if ($this->isEHRLaunchRequest($scopes)) {
            $context = $this->getEHRLaunchContext();
        }
        return $context;
    }

    public function getStandaloneLaunchContext(): array
    {
        $context = [];
        if (empty($this->session->get('puuid'))) {
            return $context;
        }
        $context['patient'] = $this->session->get('puuid');
        $context['need_patient_banner'] = true;
        $context['smart_style_url'] = $this->getSmartStyleURL();
        return $context;
    }

    /**
     * Needed for OpenEMR\FHIR\SMART\Capability::CONTEXT_STYLE support
     * Reading the SMART FHIR spec author forums so few app writers are actually using this at all, it seems like we
     * can just use defaults without getting trying to load up based upon which skin we have, or using node &
     * gulp to auto generate a skin.
     */
    private function getSmartStyleURL(): string
    {
        // "/public/smart-styles/smart-light.json";
        // need to make sure we grab the site id for this.
        return $this->globalsBag->get('site_addr_oath') . $this->globalsBag->get('web_root') . "/oauth2/" . $this->session->get('site_id') . SMARTAuthorizationController::SMART_STYLE_URL;
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    public function isStandaloneLaunchPatientRequest(array $scopes): bool
    {
        return $this->hasScope($scopes, SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE);
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    public function isEHRLaunchRequest(array $scopes): bool
    {
        return $this->hasScope($scopes, SmartLaunchController::CLIENT_APP_REQUIRED_LAUNCH_SCOPE);
    }

    /**
     * @param ScopeEntity[] $scopes
     * @param string $searchScope
     * @return bool
     */
    private function hasScope(array $scopes, string $searchScope): bool
    {
        // Verify scope and make sure openid exists.
        $valid  = false;

        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() == $searchScope) {
                $valid = true;
                break;
            }
        }

        return $valid;
    }
}
