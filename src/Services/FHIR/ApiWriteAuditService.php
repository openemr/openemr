<?php

/**
 * Records successful FHIR writes made through OAuth2 clients.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ApiWriteAuditService
{
    public function shouldAudit(HttpRestRequest $request): bool
    {
        return !$request->isLocalApi()
            && $request->hasRequestRequiredScope()
            && $request->getClientId() !== null
            && $request->getClientId() !== ''
            && $request->getAccessTokenId() !== '';
    }

    public function recordSuccessfulWrite(
        HttpRestRequest $request,
        string $resourceType,
        FHIRDomainResource $afterResource,
        ApiWriteAction $action,
        ?FHIRDomainResource $beforeResource,
        ?string $patientUuid,
        int $auditOption
    ): int {
        $resourceId = $afterResource->getId()->getValue();
        if ($resourceId === '') {
            throw new \LogicException('A successful FHIR write returned an empty resource id');
        }

        $clientId = $request->getClientId();
        if ($clientId === null || $clientId === '' || !$request->hasRequestRequiredScope()) {
            throw new \LogicException('OAuth client and required scope are needed for an API write audit row');
        }

        $session = $request->hasSession() ? $request->getSession() : null;
        $userId = $session === null ? null : $this->sessionInteger($session, 'authUserID');
        $patientId = null;
        if ($patientUuid !== null && UuidRegistry::isValidStringUUID($patientUuid)) {
            $patientRecord = QueryUtils::querySingleRow(
                'SELECT `pid` FROM `patient_data` WHERE `uuid` = ?',
                [UuidRegistry::uuidToBytes($patientUuid)]
            );
            $patientId = $patientRecord === false
                ? null
                : $this->integerValue($patientRecord['pid'] ?? null);
        }
        if ($patientId === null && $session !== null) {
            $patientId = $this->sessionInteger($session, 'pid');
        }
        $storeSnapshots = $auditOption === 2;
        $beforeSnapshot = $storeSnapshots && $beforeResource !== null
            ? json_encode($beforeResource, JSON_THROW_ON_ERROR)
            : null;
        $afterSnapshot = $storeSnapshots
            ? json_encode($afterResource, JSON_THROW_ON_ERROR)
            : null;

        return QueryUtils::sqlInsert(
            'INSERT INTO `api_write_audit` (
                `client_id`, `user_id`, `patient_id`, `resource_type`, `resource_uuid`,
                `action`, `scope`, `token_id`, `before_snapshot`, `after_snapshot`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $clientId,
                $userId,
                $patientId,
                $resourceType,
                $resourceId,
                $action->value,
                $request->getRequestRequiredScope()->getIdentifier(),
                $request->getAccessTokenId(),
                $beforeSnapshot,
                $afterSnapshot,
            ]
        );
    }

    public function linkApiLog(int $auditId, int $apiLogId): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `api_write_audit` SET `api_log_id` = ? WHERE `id` = ?',
            [$apiLogId, $auditId]
        );
    }

    private function sessionInteger(SessionInterface $session, string $key): ?int
    {
        return $this->integerValue($session->get($key));
    }

    private function integerValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }
        if (!is_string($value)) {
            return null;
        }
        $parsedValue = filter_var($value, FILTER_VALIDATE_INT);
        return is_int($parsedValue) && $parsedValue > 0 ? $parsedValue : null;
    }
}
