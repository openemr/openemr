<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ResourceScopeEntityList;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Events\RestApiExtend\RestApiScopeEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenIDConnectServer\Repositories\ClaimSetRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use InvalidArgumentException;

use function in_array;

class ScopeRepository implements ScopeRepositoryInterface
{
    use SystemLoggerAwareTrait;

    /**
     * @var ResourceScopeEntityList[] mapped by strings of the ScopeEntity.getScopeLookupKey()
     */
    private array $validationScopes;

    /**
     * @var string
     */
    private string $requestScopes;

    /**
     * Session string containing the scopes populated in the current session.
     * @var string
     */
    private string $sessionScopes;

    /**
     * @var ServerConfig
     */
    private ServerConfig $config;

    private ?SessionInterface $session = null;

    private ClaimSetRepositoryInterface $claimRepository;

    private ServerScopeListEntity $serverScopeList;

    /**
     * ScopeRepository constructor.
     * @param HttpRestRequest|null $request
     * @param SessionInterface|null $session
     */
    public function __construct(?HttpRestRequest $request = null, ?SessionInterface $session = null)
    {
        if (!empty($request)) {
            $this->requestScopes = $request->get('scope', '');
        } else {
            $this->requestScopes = '';
        }
        if (!empty($session)) {
            $this->sessionScopes = $session->get('scopes', '');
        } else {
            $this->sessionScopes = '';
        }
        $this->session = $session;
    }

    public function setServerScopeList(ServerScopeListEntity $serverScopeList): void
    {
        $this->serverScopeList = $serverScopeList;
    }

    public function getServerScopeList(): ServerScopeListEntity
    {
        if (empty($this->serverScopeList)) {
            $this->serverScopeList = new ServerScopeListEntity();
        }
        return $this->serverScopeList;
    }


    public function setServerConfig(ServerConfig $config): void
    {
        $this->config = $config;
        $this->getServerScopeList()->setSystemScopesEnabled($config->areSystemScopesEnabled());
    }

    public function getServerConfig(): ServerConfig
    {
        if (!isset($this->config)) {
            $this->config = new ServerConfig();
        }
        return $this->config;
    }

    /**
     * We check the scopes against the server supported scopes.
     * Checking the request against the client scopes comes later in the authorization process.
     * @param string $identifier
     * @return ScopeEntity|null
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntity
    {
        if (empty($this->validationScopes)) {
            $this->getSystemLogger()->debug("ScopeRepository->getScopeEntityByIdentifier() attempting to build validation scopes");
            $currentSmartScopes = $this->getCurrentSmartScopes();
            $this->getSystemLogger()->debug("ScopeRepository->getScopeEntityByIdentifier() ", ['supportedServerScopes' => $currentSmartScopes]);
            $this->validationScopes = $this->buildScopeValidatorArray($currentSmartScopes);
        }
        $scopeIdentifier = null;
        try {
            $scopeIdentifier = ScopeEntity::createFromString($identifier);
            $scopeLookupKey = $scopeIdentifier->getScopeLookupKey();
            if (!(
                    isset($this->validationScopes[$scopeLookupKey])
                    && $this->validationScopes[$scopeLookupKey]->containsScope($scopeIdentifier)
                )
            ) {
                $this->getSystemLogger()->debug("ScopeRepository->getScopeEntityByIdentifier() scope requested does not exist in system", [
                    "identifier" => $identifier
                ]);
                $scopeIdentifier = null;
            }
        }
        catch (InvalidArgumentException $exception) {
            $this->getSystemLogger()->error("ScopeRepository->getScopeEntityByIdentifier() invalid scope format for identifier", [
                "scope" => $identifier,
                "exception" => $exception->getMessage()
            ]);
        }
        if ($scopeIdentifier == null) {
            $this->getSystemLogger()->error("ScopeRepository->getScopeEntityByIdentifier() request access to invalid scope", [
                "scope" => $identifier
            ]);
        }
        return $scopeIdentifier;
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ): array {
        $finalizedScopes = [];
        $scopeListNames = [];
        $finalizedScopeNames = [];
        $clientScopes = [];
        // we only let scopes that the client initially registered with through instead of whatever they request in
        // their grant.
        if ($clientEntity instanceof ClientEntity) {
            $clientScopes = $clientEntity->getScopes();
            $clientValidatorArray = $this->buildScopeValidatorArray($clientScopes);
            foreach ($scopes as $scope) {
                $scopeListNames[] = $scope->getIdentifier();
                if ($scope instanceof ScopeEntity) {
                    $lookupKey = $scope->getScopeLookupKey();
                    if (isset($clientValidatorArray[$lookupKey])
                        && $clientValidatorArray[$lookupKey]->containsScope($scope)) {
                        $finalizedScopes[] = $scope;
                        $finalizedScopeNames[] = $scope->getIdentifier();
                    }
                }
            }
        } else {
            $this->getSystemLogger()->error("client entity was not an instance of ClientEntity and scopes could not be retrieved");
        }

        // If a nonce is passed in, add a nonce scope for id token nonce claim
        if (!empty($this->session) && !empty($this->session->get('nonce'))) {
            $scope = new ScopeEntity();
            $scope->setIdentifier('nonce');
            $finalizedScopes[] = $scope;
            $finalizedScopeNames[] = "nonce";
        }

        $this->getSystemLogger()->debug(
            "ScopeRepository->finalizeScopes() scopes finalized ",
            ['finalizedScopes' => $finalizedScopeNames, 'clientScopes' => $clientScopes
            ,
            'initialScopes' => $scopeListNames]
        );
        return $finalizedScopes;
    }

    public function getRequestScopes()
    {
        return $this->requestScopes;
    }

    public function getSessionScopes()
    {
        return $this->sessionScopes;
    }

    public function getClaimRepository() {
        if (!isset($this->claimRepository)) {
            $this->claimRepository = new ClaimRepository();
        }
        return $this->claimRepository;
    }


    public function fhirRequiredSmartScopes(): array
    {
        return $this->getServerScopeList()->requiredSmartOnFhirScopes();
    }

    public function fhirScopesV1(): array
    {
        return $this->getServerScopeList()->fhirResourceScopesV1();
    }

    public function fhirScopesV2(): array
    {
        return $this->fhirScopesV2();
    }

    /**
     * Method will qualify current scopes based on active FHIR resources.
     * Allowed permissions are validated from the default scopes and client role.
     *
     * @return array
     */
    public function getCurrentSmartScopes(): array
    {
        $this->getSystemLogger()->debug("ScopeRepository->getCurrentSmartScopes() setting up smart scopes");
        $scopesSupportedList = $this->getServerScopeList()->getAllSupportedScopesList();

        // for backwards compatability we are going to fire for all three scope types, FHIR being first
        $scopeEvents = [
            RestApiScopeEvent::API_TYPE_FHIR,
            RestApiScopeEvent::API_TYPE_STANDARD
        ];
        foreach ($scopeEvents as $event) {
            $scopesEvent = new RestApiScopeEvent();
            $scopesEvent->setSystemScopesEnabled($this->getServerConfig()->areSystemScopesEnabled());
            $scopesEvent->setApiType($event);
            $scopesEvent->setScopes($scopesSupportedList);
            // TODO: @adunsulag we need to extract this global out of the this class so we can inject and test it.
            $scopesEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch($scopesEvent, RestApiScopeEvent::EVENT_TYPE_GET_SUPPORTED_SCOPES, 10);
            if ($scopesEvent instanceof RestApiScopeEvent) {
                $scopesSupportedList = $scopesEvent->getScopes();
            }
        }

        return $scopesSupportedList;
    }

    // made public for now!
    public function buildScopeValidatorArray(array $currentServerScopes): array
    {
        $scopePermissionArray = [];
        foreach ($currentServerScopes as $scope) {
            $scopeObject = ScopeEntity::createFromString($scope);
            if (empty($scopePermissionArray[$scopeObject->getScopeLookupKey()])) {
                $scopePermissionArray[$scopeObject->getScopeLookupKey()] = new ResourceScopeEntityList($scopeObject->getScopeLookupKey());
            }
            $scopePermissionArray[$scopeObject->getScopeLookupKey()][] = $scopeObject;
        }
        return $scopePermissionArray;
    }

    public function lookupDescriptionForScope($scope, bool $isPatient) : string
    {
        $requiredSmart = [
            "openid" => xl("Permission to retrieve information about the current logged-in user"),
            "fhirUser" => xl("Identity Information - Permission to retrieve information about the current logged-in user"),
            "online_access" => xl("Request ability to access data while the current logged-in user remains logged in"),
            "offline_access" => xl("Request ability to access data even when the current logged-in user has logged out"),
            "launch" => xl("Permission to obtain information from the EHR for the current session context when app is launched from an EHR."),
            "launch/patient" => xl("When launching outside the EHR, ask for a patient to be selected at launch time."),
            "api:oemr" => xl("Permission to use the OpenEMR standard api."),
            "api:fhir" => xl("Permission to use the OpenEMR FHIR api"),
            "api:port" => xl("Permission to use the OpenEMR apis from inside the patient portal"),
            'system/Patient.$export' => xl("Permission to export Patient Compartment resources"),
            'system/Group.$export' => xl("Permission to export Patient Compartment resources connected to a Patient Group"),
            'system/*.$bulkdata-status' => xl("Permission to check the job status of a bulkdata export"),
            'system/*.$export' => xl("Permission to export the entire system dataset the is exportable")
        ];

        if (isset($requiredSmart[$scope])) {
            return $requiredSmart[$scope];
        }
        $scope = ScopeEntity::createFromString($scope);
        if (empty($scope->getResource())) {
            return $this->getServerScopeList()->lookupDescriptionForFullScopeString($scope);
        } else if (!empty($scope->getOperation())) {
            return $this->lookupDescriptionForResourceOperation($scope);
        } else {
            return $this->lookupDescriptionForSmartScope($scope);
        }
    }

    private function lookupDescriptionForSmartScope(ScopeEntity $scope): string
    {
        $permissions = $scope->getPermissions();
        $permissionStrings = [];
        if ($permissions->v1Read) {
            $permissionStrings[] = xl("View, search and access");
        } else if ($permissions->v1Write) {
            $permissionStrings[] = xl("Create or modify");
        }
        if ($permissions->create) {
            $permissionStrings[] = xl("Create new records");
        }
        if ($permissions->update) {
            $permissionStrings[] = xl("Update existing records");
        }
        if ($permissions->delete) {
            $permissionStrings[] = xl("Delete records");
        }
        if ($permissions->search) {
            $permissionStrings[] = xl("Search existing records");
        }
        if ($permissions->search) {
            $permissionStrings[] = xl("Access or retrieve an existing record");
        }
        $description = xl('Permission to do the following actions') . " " . implode(" ", $permissionStrings);
        $description .= " " . xl("on the following resources") . " " . $this->getServerScopeList()->lookupDescriptionForResourceScope($scope->getResource(), $scope->getContext());

        return $description;
    }

    private function lookupDescriptionForResourceOperation(ScopeEntity $scope)
    {
        $resource = $scope->getResource();
        $description = match($scope->getOperation()) {
            '$export' => match($scope->getResource()) {
                '*' => xl("Permission to export the entire system dataset that is exportable"),
                'Patient' => xl("Permission to export Patient Compartment resources"),
                'Group' => xl("Permission to export Patient Compartment resources connected to a Patient Group"),
                default => xl("Permission to export all resources of type") . " " . $resource,
            }
            ,'$bulkdata-status' => match($scope->getResource()) {
                '*' => xl("Permission to check the job status of a bulkdata export"),
                default => xl("Permission to check the job status of a bulkdata export for resource type") . " " . $resource,
            }
            ,'$docref' => match($scope->getResource()) {
                'DocumentReference' => xl("Create a Clinical Summary of Care Document (CCD) or retrieve the most current CCD"),
                default => xl("Create a document reference for resource type") . " " . $resource,
            }
            ,default => throw new InvalidArgumentException("Unknown operation for scope: " . $scope->getOperation())
        };
        return $description;
    }

    /**
     * @param $resource
     * @param $context
     * @param $isPatient
     * @param $isReadPermission
     * @return string
     */
    private function lookupDescriptionForResourceScope(ScopeEntity $scope, $isPatient): string
    {
        $description = $isReadPermission ? xl("Read Access: View, search and access") : xl("Write Access: Create or modify");
        $description .= " ";
        switch ($resource) {
            case 'AllergyIntolerance':
                $description .= xl("allergies/adverse reactions");
                break;
            case 'Appointment':
                $description .= xl("appointments");
                break;
            case 'Observation':
                $description .= xl("observations including laboratory,vitals, and social history records");
                break;
            case 'CarePlan':
                $description .= xl("care plan information including treatment information and notes");
                break;
            case 'CareTeam':
                $description .= xl("care team information including practitioners, organizations, persons, and related individuals");
                break;
            case 'Condition':
                $description .= xl("conditions including health concerns, problems, and encounter diagnoses");
                break;
            case 'Device':
                $description .= xl("implantable medical device records");
                break;
            case 'DiagnosticReport':
                $description .= xl("diagnostic reports including laboratory,cardiology,radiology, and pathology reports");
                break;
            case 'DocumentReference':
                $description .= xl("clinical and non-clinical documents");
                break;
            case 'Encounter':
                $description .= xl("encounter information");
                break;
            case 'Goal':
                $description .= xl("goals");
                break;
            case 'Immunization':
                $description .= xl("immunization history");
                break;
            case 'MedicationRequest':
                $description .= xl("planned and prescribed medication history including self-reported medications");
                break;
            case 'Medication':
                $description .= xl("drug information related to planned and prescribed medication history");
                break;
            case 'Organization':
                $description .= xl("companies, facilities, insurances, and other organizations");
                break;
            case 'Patient':
                $description .= xl("patient basic demographics including names,communication preferences,race,ethnicity,birth sex,previous names and other administrative information");
                break;
            case 'Practitioner':
                $description .= xl("provider basic demographic information and other administrative information");
                break;
            case 'PractitionerRole':
                $description .= xl("practitioner role for a practitioner (including speciality, location, contact information)");
                break;
            case 'Procedure':
                $description .= xl("procedures");
                break;
            case 'Location':
                $description .= xl("locations associated with a patient, provider, or organization");
                break;
            case 'Provenance':
                $description .= xl("provenance information (including person(s) responsible for the information, author organizations, and transmitter organizations)");
                break;
            case 'ValueSet':
                $description .= xl("value set records");
                break;
            default:
                $description .= xl("medical records for this resource type");
                break;
        }
        if ($context == "user") {
            $description .= ". " . xl("Application is requesting access to all patient data for this resource you have access to");
        } else if ($context == "system") {
            $description .= ". " . xl("Application is requesting access to all data in entire system for this resource");
        }
        return $description;
    }

    /**
     * Checks if the given scopes array requires any manual approval by an administrator before an oauth2 client can be authorized
     * @param bool $is_confidential_client Whether the client is confidential (can keep a secret safe) or a public app
     * @param array $scopes The scopes to be checked to see if we need manual approval
     * @param string $oauthAppManualApprovalSetting The OAuthManualApproval setting from the globals table
     * @return bool true if there exist scopes that require manual review by an administrator, false otherwise
     */
    public function hasScopesThatRequireManualApproval(bool $is_confidential_client, array $scopes, $oauthManualApprovalSetting = '0'): bool
    {
        // note eventually this method could have a db lookup to check against if admins want to vet this
        // possibly we could have an event dispatched here as well if we want someone to provide / extend that kind of functionality

        // if a public app requests the launch scope we also do not let them through unless they've been manually
        // authorized by an administrator user.
        if (!$is_confidential_client) {
            if (in_array("launch", $scopes)) {
                return true;
            }
        }
        // as not all jurisdictions have to comply with ONC rules we will still check against the globals flag in case
        // a user has turned off auto-enabling of apps and wants to lock down their installation
        if ($oauthManualApprovalSetting == '1') {
            return true;
        }

        if ($is_confidential_client) {
            // ONC requires that a patient be allowed to use an app of their choice and as long as it does not use user/system scopes there can be
            // no prohibiting the patient app selection due to Information Blocking Rule, EMRs must authorize the app within 2 business days
            // to deal with this we auto-enable confidential apps that ONLY use patient/* scopes even if they request offline_access scope
            // we still prohibit any confidential app that is allowing an in-EHR context to be auto-enabled since they are listed inside
            // the patient demographics screen (and other locations possibly in the future)
            if ($this->hasUserScopes($scopes) || $this->hasSystemScopes($scopes)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $scopes
     * @return bool
     */
    private function hasUserScopes(array $scopes): bool
    {
        return $this->scopeArrayHasString($scopes, 'user/');
    }

    /**
     * @param array $scopes
     * @return bool
     */
    private function hasSystemScopes(array $scopes): bool
    {
        return $this->scopeArrayHasString($scopes, 'system/');
    }

    /**
     * @param array $scopes
     * @param $str
     * @return bool
     */
    private function scopeArrayHasString(array $scopes, $str): bool
    {
        foreach ($scopes as $scope) {
            if (str_contains($scope, $str)) {
                return true;
            }
        }
        return false;
    }
}
