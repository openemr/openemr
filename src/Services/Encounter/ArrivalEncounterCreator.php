<?php

/**
 * Creates the encounter for a patient checked in from the calendar, the flow board, appointment status or
 * front payment.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Encounter;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\EncounterService;
use Psr\Log\LoggerInterface;

/**
 * Inserts an arrival encounter through EncounterService::insertEncounter(), so it gets a UUID when it's created,
 * passes the same validation and fires the same save events as an encounter created any other way.
 *
 * The columns are the ones todaysEncounterCheck() has always stored: the session user's facility name, the
 * facility and billing facility given or else the user's, the facility's POS code, 0 for a provider or a
 * category that isn't given, and an encounter form stored as authorized.
 */
final readonly class ArrivalEncounterCreator
{
    private const DEFAULT_REASON = 'Please indicate visit reason';

    private EncounterService $encounterService;
    private LoggerInterface $logger;

    public function __construct(?EncounterService $encounterService = null, ?LoggerInterface $logger = null)
    {
        $this->encounterService = $encounterService ?? new EncounterService();
        $this->logger = $logger ?? ServiceContainer::getLogger();
    }

    /**
     * Takes what todaysEncounterCheck() is given, as its callers pass it (request and appointment values).
     *
     * @param mixed $provider the provider id, or a list whose first entry is
     *
     * @return int the new encounter number, or 0 when it couldn't be created
     */
    public function create(
        mixed $patientId,
        mixed $date,
        mixed $reason,
        mixed $facilityId,
        mixed $billingFacilityId,
        mixed $provider,
        mixed $categoryId,
    ): int {
        $pid = self::idOrZero($patientId);
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $user = QueryUtils::querySingleRow(
            "SELECT `username`, `facility`, `facility_id` FROM `users` WHERE `id` = ?",
            [$session->get('authUserID')]
        );
        $user = is_array($user) ? $user : [];
        $userFacilityId = self::idOrZero($user['facility_id'] ?? null);
        $visitFacilityId = self::idOrZero($facilityId) ?: $userFacilityId;

        $puuid = QueryUtils::fetchSingleValue("SELECT `uuid` FROM `patient_data` WHERE `pid` = ?", 'uuid', [$pid]);
        if (!is_string($puuid) || $puuid === '') {
            $this->logger->error("Arrival encounter not created: the patient has no UUID", ['pid' => $pid]);
            return 0;
        }

        $data = [
            'pc_catid' => self::idOrZero($categoryId),
            'class_code' => EncounterService::DEFAULT_CLASS_CODE,
            'date' => (is_string($date) && $date !== '') ? $date : date('Y-m-d'),
            'reason' => $this->reason($reason),
            'facility_id' => $visitFacilityId,
            'billing_facility' => self::idOrZero($billingFacilityId) ?: $userFacilityId,
            'provider_id' => self::idOrZero(is_array($provider) ? ($provider[0] ?? null) : $provider),
            'user' => $user['username'] ?? '',
            // An empty group makes addForm() use the session's provider group, as this path always has.
            'group' => '',
        ];

        // Left out when NULL, so the column keeps its NULL default: insertEncounter() would store '' (0 for
        // pos_code), where this path has always stored NULL.
        $facilityName = $user['facility'] ?? null;
        if ($facilityName !== null) {
            $data['facility'] = $facilityName;
        }
        $posCode = QueryUtils::fetchSingleValue("SELECT `pos_code` FROM `facility` WHERE `id` = ?", 'pos_code', [$visitFacilityId]);
        if ($posCode !== null) {
            $data['pos_code'] = $posCode;
        }

        // The encounter form is stored as authorized, as this path always has (addForm()'s "1"), not as the
        // provider id insertEncounter() stores by default.
        $result = $this->encounterService->insertEncounter(UuidRegistry::uuidToString($puuid), $data, '1');
        $record = $result->hasData() ? $result->getFirstDataResult() : null;
        if (!$result->isValid() || $result->hasInternalErrors() || !is_array($record)) {
            $this->logger->error("Arrival encounter not created", [
                'pid' => $pid,
                'validationMessages' => $result->getValidationMessages(),
                'internalErrors' => $result->getInternalErrors(),
            ]);
            return 0;
        }
        return self::idOrZero($record['encounter'] ?? null);
    }

    private function reason(mixed $reason): string
    {
        if (OEGlobalsBag::getInstance()->getBoolean('auto_create_prevent_reason')) {
            return self::DEFAULT_REASON;
        }
        // '' and '0' have always fallen back to the default, as falsy values.
        if (is_string($reason) && $reason !== '' && $reason !== '0') {
            return $reason;
        }
        return xl(self::DEFAULT_REASON);
    }

    /**
     * A positive id as an int, else 0: what an empty or missing id has always been stored as.
     */
    private static function idOrZero(mixed $id): int
    {
        return is_numeric($id) ? max(0, (int) $id) : 0;
    }
}
