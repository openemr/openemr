<?php

/**
 * PatientTransaction Service
 * DAL to be used for Transactions
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jonathan Moore <Jdcmoore@aol.com>
 * @copyright Copyright (c) 2022 Jonathan Moore <Jdcmoore@aol.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Validators\ProcessingResult;
use Particle\Validator\Validator;

class PatientTransactionService extends BaseService
{
    const TABLE_NAME = "transactions";
    const _formPredicate = 'd.form_id';
    const _transactionPredicate = 't.pid';

    /**
     * @param ReferralTaskService $referralTaskService makes a saved referral's FHIR Task (#14710)
     */
    public function __construct(private readonly ReferralTaskService $referralTaskService = new ReferralTaskService())
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function getSelectStatement($predicateColumnName): string
    {

        $criteriaItemsWhitelist = [
            self::_formPredicate,
            self::_transactionPredicate
        ];

        return
        "
        SELECT
            d.form_id,
            t.title,
            t.groupname,
            t.authorized,
            MAX(
                CASE WHEN d.field_id = 'body' THEN d.field_value ELSE ''
            END
            ) AS body,
            MAX(
                CASE WHEN d.field_id = 'refer_date' THEN d.field_value ELSE ''
            END
            ) AS refer_date,
            MAX(
                CASE WHEN d.field_id = 'refer_diag' THEN d.field_value ELSE ''
            END
            ) AS refer_diag,
            MAX(
                CASE WHEN d.field_id = 'refer_from' THEN d.field_value ELSE ''
            END
            ) AS refer_from,
            MAX(
                CASE WHEN d.field_id = 'refer_to' THEN d.field_value ELSE ''
            END
            ) AS refer_to,
            MAX(
                CASE WHEN d.field_id = 'refer_risk_level' THEN d.field_value ELSE ''
            END
            ) AS refer_risk_level,
            MAX(
                CASE WHEN d.field_id = 'refer_vitals' THEN d.field_value ELSE ''
            END
            ) AS refer_vitals,

            MAX(
                CASE WHEN d.field_id = 'refer_authorization' THEN d.field_value ELSE ''
            END
            ) AS refer_authorization,
            MAX(
                CASE WHEN d.field_id = 'refer_visits' THEN d.field_value ELSE ''
            END
            ) AS refer_visits,
            MAX(
                CASE WHEN d.field_id = 'refer_validFrom' THEN d.field_value ELSE ''
            END
            ) AS refer_validFrom,
            MAX(
                CASE WHEN d.field_id = 'refer_validThrough' THEN d.field_value ELSE ''
            END
            ) AS refer_validThrough
        FROM
            transactions t
        LEFT JOIN lbt_data d ON
            d.form_id = t.id
        WHERE " . escape_identifier($predicateColumnName, $criteriaItemsWhitelist, true) . " = ?
        GROUP BY
            d.form_id,
            t.groupname,
            t.title;
        ";
    }

    private function getOneFromDb($tid): array
    {

        $sqlBindArray = [];

        $sql = $this->getSelectStatement(self::_formPredicate);
        array_push($sqlBindArray, $tid);

        $records = QueryUtils::fetchRecords($sql, $sqlBindArray);

        return $records;
    }

    public function getAll($pid): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $sqlBindArray = [];

        $sql = $this->getSelectStatement(self::_transactionPredicate);
        array_push($sqlBindArray, $pid);

        $records = QueryUtils::fetchRecords($sql, $sqlBindArray);

        if (count($records) > 0) {
            $processingResult->addData($records);
        }

        return $processingResult;
    }

    public function insert($pid, $data)
    {
        try {
            $saved = QueryUtils::inTransaction(function () use ($pid, $data) {
                $transactionId = $this->insertTransaction($pid, $data);
                if ($transactionId == false) {
                    throw new PatientTransactionInsertFailedException('Failed to insert patient transaction');
                }

                $lbtDataId = $this->insertTransactionForm($transactionId, $data);
                if ($lbtDataId == false) {
                    throw new PatientTransactionInsertFailedException('Failed to insert patient transaction form');
                }

                return ["id" => $transactionId, "form_id" => $lbtDataId];
            });
        } catch (PatientTransactionInsertFailedException) {
            return false;
        }
        $this->createReferralTask($saved['id']);
        return $saved;
    }

    /**
     * The referral's FHIR Task and its reply, when the fhir_referral_tasks global is on (#14710), made
     * by the user of the service's session (setSession()), else of the active one. The referral is
     * already committed, so a failure here is logged rather than thrown (afterSave()).
     */
    private function createReferralTask(mixed $transactionId): void
    {
        if (!is_numeric($transactionId)) {
            return;
        }
        $userId = ($this->getSession() ?? SessionWrapperFactory::getInstance()->getActiveSession())->get('authUserID');
        $this->referralTaskService->afterSave((int)$transactionId, is_numeric($userId) ? (int)$userId : null);
    }

    public function insertTransaction($pid, $data)
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $user = $session->get('authUser');
        $sql =
        "
            INSERT INTO transactions SET
                date=NOW(),
                title=?,
                pid=?,
                groupname=?,
                authorized=1,
                user=?
        ";

        $results = sqlInsert(
            $sql,
            [
                $data["type"],
                $pid,
                $data['groupname'],
                $user
            ]
        );

        if (!$results) {
            return false;
        }

        return $results;
    }

    public function insertTransactionForm($transactionId, $data)
    {
        $referById = $this->getUserIdByNpi($data["referByNpi"]);
        $referToId = $this->getUserIdByNpi($data["referToNpi"]);

        $sql =
        "
            INSERT INTO lbt_data (form_id, field_id, field_value) VALUES
            (?,  'body', ?),
            (?,  'refer_date', ?),
            (?,  'refer_diag', ?),
            (?,  'refer_from', ?),
            (?,  'refer_to', ?),
            (?,  'refer_risk_level', ?),
            (?,  'refer_vitals', ?),
            (?,  'refer_authorization', ?),
            (?,  'refer_visits', ?),
            (?,  'refer_validFrom', ?),
            (?,  'refer_validThrough', ?)
        ";

        $params =
        [
            $transactionId, $data["body"],
            $transactionId, $data["referralDate"],
            $transactionId, $data["referDiagnosis"],
            $transactionId, $referById,
            $transactionId, $referToId,
            $transactionId, strtolower((string) $data["riskLevel"]),
            $transactionId, $data["includeVitals"],
            $transactionId, $data["authorization"],
            $transactionId, $data["visits"],
            $transactionId, $data["validFrom"],
            $transactionId, $data["validThrough"],
        ];

        // lbt_data has no auto increment column, so sqlInsert()'s insert id is always 0.
        // The rows are keyed by the transaction id; throws on failure.
        QueryUtils::sqlStatementThrowException($sql, $params);

        return $transactionId;
    }

    public function update($tid, $data)
    {

        $referById = $this->getUserIdByNpi($data["referByNpi"]);
        $referToId = $this->getUserIdByNpi($data["referToNpi"]);
        $body = $data["body"];
        $referralDate = $data["referralDate"];
        $referralDiagnosis = $data["referDiagnosis"];
        $riskLevel = strtolower((string) $data["riskLevel"]);
        $includeVitals = $data["includeVitals"];
        $authorization = $data["authorization"];
        $visits = $data["visits"];
        $validFrom = $data["validFrom"];
        $validThrough = $data["validThrough"];

        QueryUtils::inTransaction(function () use ($tid, $referById, $referToId, $body, $referralDate, $referralDiagnosis, $riskLevel, $includeVitals, $authorization, $visits, $validFrom, $validThrough): void {
            // As the Transactions screen does on every save, so that the change shows as the
            // referral's last update (the FHIR ServiceRequest's meta.lastUpdated and _lastUpdated).
            QueryUtils::sqlStatementThrowException('UPDATE transactions SET date = NOW() WHERE id = ?', [$tid]);
            $this->updateTransactionForm($tid, 'refer_from', $referById);
            $this->updateTransactionForm($tid, 'refer_to', $referToId);
            $this->updateTransactionForm($tid, 'body', $body);
            $this->updateTransactionForm($tid, 'refer_date', $referralDate);
            $this->updateTransactionForm($tid, 'refer_diag', $referralDiagnosis);
            $this->updateTransactionForm($tid, 'refer_risk_level', $riskLevel);
            $this->updateTransactionForm($tid, 'refer_vitals', $includeVitals);
            $this->updateTransactionForm($tid, 'refer_authorization', $authorization);
            $this->updateTransactionForm($tid, 'refer_visits', $visits);
            $this->updateTransactionForm($tid, 'refer_validFrom', $validFrom);
            $this->updateTransactionForm($tid, 'refer_validThrough', $validThrough);
        });
        $this->createReferralTask($tid);

        return $this->getOneFromDb($tid);
    }

    public function updateTransactionForm($formId, $fieldId, $value)
    {
        if (empty($value) == false) {
            $sql = "Update lbt_data SET field_value = ? Where field_id = ? and form_id = ?";
            $params = [$value, $fieldId, $formId];
            $res = sqlStatement($sql, $params);
        }
    }

    public function validate($transaction)
    {
        $transactionType = $transaction["type"];

        if (empty($transactionType)) {
            $this->throwException('type is not valid', 'type');
        }

        $validator = new Validator();
        switch ($transactionType) {
            case "LBTref":
                $validator->required('referralDate')->datetime('Y-m-d');
                $validator->required('body')->lengthBetween(2, 150);
                $validator->required('groupname')->string();
                $validator->required('referByNpi')->string();
                break;
        }

        return $validator->validate($transaction);
    }

    public function getUserIdByNpi($npi)
    {
        try {
            return QueryUtils::fetchSingleValue('Select id FROM users WHERE npi = ? ', 'id', [$npi]);
        } catch (\Throwable $ex) {
            return $ex;
        }
    }
}
