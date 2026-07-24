<?php

/**
 * SmartLaunchToken represents the opaque SMART 'launch' context values that are used to send the EHR session context
 * to the app which the app then hands back to the oauth2 authorization server.
 * @package OpenEMR\FHIR\SMART
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\SMART;

use OpenEMR\BC\ServiceContainer;

class SMARTLaunchToken
{
    public const INTENT_PATIENT_DEMOGRAPHICS_DIALOG = 'patient.demographics.dialog';

    public const INTENT_QUESTIONNAIRE_ASSESSMENT = 'questionnaire.assessment.dialog';

    public const VALID_INTENTS = [self::INTENT_PATIENT_DEMOGRAPHICS_DIALOG, self::INTENT_APPOINTMENT_DIALOG, self::INTENT_ENCOUNTER_DIALOG, self::INTENT_MAIN_TAB, self::INTENT_QUESTIONNAIRE_ASSESSMENT];

    // used on the appointment add/edit dialog, context will include the selected appointment
    // for now this intent is used by custom apps that consume the openemr.appointment.add_edit_event.close.before event
    // to present a SMART app as a 2nd step to the add/edit appointment workflow
    public const INTENT_APPOINTMENT_DIALOG = 'appointment.edit.dialog';

    public const INTENT_ENCOUNTER_DIALOG = 'encounter.forms.dialog';

    /*
     * When a module is launched from a main menu item into a main tab, the intent is set to this value.
     */
    public const INTENT_MAIN_TAB = 'main.tab';

    /**
     * @var string|null The patient UUID If
     */
    private $patient;
    private $intent;
    private $encounter;
    /**
     * @var string The uuid of the appointment
     */
    private ?string $appointmentUuid;

    /**
     * @var array<int, array<mixed>> Additional SMART fhirContext items (reference/canonical/identifier form).
     */
    private array $fhirContext = [];

    /**
     * @var string|null SMART application workflow context.
     */
    private ?string $appContext = null;

    public function __construct($patientUUID = null, $encounterUUID = null)
    {
        if (isset($patientUUID) && !is_string($patientUUID)) {
            throw new \InvalidArgumentException("patientUUID must be a string");
        }
        if (isset($encounterUUID) && !is_string($encounterUUID)) {
            throw new \InvalidArgumentException("encounterUUID must be a string");
        }
        $this->patient = $patientUUID;
        $this->encounter = $encounterUUID;
        $this->appointmentUuid = null;
    }

    /**
     * @return mixed
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $patient
     */
    public function setPatient($patient): void
    {
        $this->patient = $patient;
    }

    /**
     * @return mixed
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * @param mixed $encounter
     */
    public function setEncounter($encounter): void
    {
        $this->encounter = $encounter;
    }

    /**
     * @return mixed
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param mixed $intent
     */
    public function setIntent($intent): void
    {
        $this->intent = $intent;
    }

    /**
     * Adds a validated relative FHIR reference to the SMART launch context.
     */
    public function addFhirContextReference(string $resourceType, string $resourceId): void
    {
        if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $resourceType) !== 1) {
            throw new \InvalidArgumentException("FHIR context resource type is invalid");
        }
        if (preg_match('/^[A-Za-z0-9\-.]{1,64}$/', $resourceId) !== 1) {
            throw new \InvalidArgumentException("FHIR context resource id is invalid");
        }
        $this->addFhirContextItem(['reference' => $resourceType . '/' . $resourceId]);
    }

    /**
     * Adds a validated fhirContext item per SMART App Launch 2.2 "fhirContext":
     * each item SHALL include at least one of reference, canonical, or identifier;
     * type is RECOMMENDED with canonical/identifier; role, when present, SHALL NOT
     * be empty and custom roles must be absolute URIs.
     *
     * @param array<mixed> $item
     */
    public function addFhirContextItem(array $item): void
    {
        $allowedKeys = ['reference', 'canonical', 'identifier', 'type', 'role'];
        foreach (array_keys($item) as $key) {
            if (!in_array($key, $allowedKeys, true)) {
                throw new \InvalidArgumentException("FHIR context item contains unsupported key");
            }
        }

        $reference = $item['reference'] ?? null;
        $canonical = $item['canonical'] ?? null;
        $identifier = $item['identifier'] ?? null;
        if ($reference === null && $canonical === null && $identifier === null) {
            throw new \InvalidArgumentException("FHIR context item must include a reference, canonical, or identifier");
        }

        if ($reference !== null) {
            if (
                !is_string($reference)
                || preg_match('/^[A-Z][A-Za-z0-9]*\/[A-Za-z0-9\-.]{1,64}$/', $reference) !== 1
            ) {
                throw new \InvalidArgumentException("FHIR context resource reference is invalid");
            }
            $referenceType = explode('/', $reference, 2)[0];
            $itemRole = $item['role'] ?? null;
            if (in_array($referenceType, ['Patient', 'Encounter'], true) && (!is_string($itemRole) || $itemRole === '')) {
                // Patient and Encounter are top-level launch parameters and are not
                // permitted in fhirContext unless they carry a non-launch role.
                throw new \InvalidArgumentException("Patient and Encounter references are not permitted in fhirContext");
            }
        }
        if ($canonical !== null) {
            $canonicalUrl = is_string($canonical) ? explode('|', $canonical, 2)[0] : null;
            if ($canonicalUrl === null || $canonicalUrl === '' || filter_var($canonicalUrl, FILTER_VALIDATE_URL) === false) {
                throw new \InvalidArgumentException("FHIR context canonical is invalid");
            }
        }
        if ($identifier !== null && (!is_array($identifier) || $identifier === [])) {
            throw new \InvalidArgumentException("FHIR context identifier is invalid");
        }
        if (array_key_exists('type', $item) && (!is_string($item['type']) || preg_match('/^[A-Z][A-Za-z0-9]*$/', $item['type']) !== 1)) {
            throw new \InvalidArgumentException("FHIR context type is invalid");
        }
        if (array_key_exists('role', $item)) {
            $role = $item['role'];
            if (!is_string($role) || $role === '' || filter_var($role, FILTER_VALIDATE_URL) === false) {
                throw new \InvalidArgumentException("FHIR context role must be a non-empty absolute URI");
            }
        }

        if (!in_array($item, $this->fhirContext, true)) {
            $this->fhirContext[] = $item;
        }
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function getFhirContext(): array
    {
        return $this->fhirContext;
    }

    public function setAppContext(?string $appContext): void
    {
        $this->appContext = $appContext;
    }

    public function getAppContext(): ?string
    {
        return $this->appContext;
    }

    public function serialize()
    {
        $context = [];
        $encounter = $this->getEncounter();
        $patient = $this->getPatient();
        $intent = $this->getIntent();
        if (!empty($encounter)) {
            $context['e'] = $encounter;
        }
        if (!empty($patient)) {
            $context['p'] = $patient;
        }
        if (!empty($intent)) {
            $context['i'] = $intent;
        }
        if (!empty($this->getAppointmentUuid())) {
            $context['apt'] = $this->getAppointmentUuid();
        }
        $fhirContext = $this->getFhirContext();
        if ($fhirContext !== []) {
            $context['fc'] = $fhirContext;
        }
        $appContext = $this->getAppContext();
        if ($appContext !== null && $appContext !== '') {
            $context['ctx'] = $appContext;
        }

        // no security is really needed here... just need to be able to wrap
        // the current context into some kind of opaque id that the app will pass to the server and we can then
        // return to system
        $cryptoGen = ServiceContainer::getCrypto();
        $jsonEncoded = json_encode($context);
        ServiceContainer::getLogger()->debug(self::class . "->serialize() Context before encryption", ['context' => $context, 'json' => $jsonEncoded]);
        $launchParams = $cryptoGen->encryptStandard($jsonEncoded !== false ? $jsonEncoded : null);
        return base64_encode($launchParams); // make it URL safe
    }

    /**
     * @param $serialized
     * @return self
     * @throws \JsonException
     * @throws \InvalidArgumentException
     */
    public static function deserializeToken($serialized): self
    {
        $token = new self();
        $token->deserialize($serialized);
        return $token;
    }

    /**
     * @param $serialized
     * @return void
     * @throws \JsonException
     * @throws \InvalidArgumentException
     */
    public function deserialize($serialized)
    {
        $cryptoGen = ServiceContainer::getCrypto();
        $jsonEncrypted = base64_decode((string) $serialized);
        if ($jsonEncrypted === false) {
            throw new \InvalidArgumentException("serialized token is not valid base64");
        }
        $jsonEncoded = $cryptoGen->decryptStandard($jsonEncrypted);
        if ($jsonEncoded === false) {
            throw new \InvalidArgumentException("serialized token could not be decrypted.  Token was either invalid or something is wrong with the encryption keys");
        }

        // invalid json let it throw here
        $context = json_decode($jsonEncoded, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($context)) {
            throw new \InvalidArgumentException("serialized token context must be a JSON object");
        }
        ServiceContainer::getLogger()->debug(self::class . "->deserialize() Decoded context is ", $context);
        if (!empty($context['p'])) {
            $this->setPatient($context['p']);
        }
        if (!empty($context['e'])) {
            $this->setEncounter($context['e']);
        }
        if (!empty($context['i']) && $this->isValidIntent($context['i'])) {
            $this->setIntent($context['i']);
        }
        if (!empty($context['apt'])) {
            $this->setAppointmentUuid($context['apt']);
        }
        if (isset($context['fc'])) {
            if (!is_array($context['fc'])) {
                throw new \InvalidArgumentException("FHIR launch context must be an array");
            }
            foreach ($context['fc'] as $fhirContextItem) {
                if (!is_array($fhirContextItem)) {
                    throw new \InvalidArgumentException("FHIR launch context item is invalid");
                }
                // addFhirContextItem re-validates every key, so a tampered or
                // malformed token still fails closed here.
                $this->addFhirContextItem($fhirContextItem);
            }
        }
        if (isset($context['ctx'])) {
            if (!is_string($context['ctx'])) {
                throw new \InvalidArgumentException("SMART app context must be a string");
            }
            $this->setAppContext($context['ctx']);
        }
    }

    public function isValidIntent($intent)
    {
        return array_search($intent, self::VALID_INTENTS) !== false;
    }
    public function setAppointmentUuid(string $appointmentUuid)
    {
        $this->appointmentUuid = $appointmentUuid;
    }

    public function getAppointmentUuid(): ?string
    {
        return $this->appointmentUuid;
    }
}
