<?php

/**
 * PatientTransaction Service
 * DAL to be used for Transactions
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jonathan Moore <Jdcmoore@aol.com>
 * @copyright Copyright (c) 2022 Jonathan Moore <Jdcmoore@aol.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use MongoDB\Driver\Query;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;
use OpenEMR\Validators\BaseValidator;

class PatientTransactionService extends BaseService
{
    const TABLE_NAME = "transactions";
    const _formPredicate = 'd.form_id';
    const _transactionPredicate = 't.pid';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function getSelectStatement($predicateColumnName)
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

    private function getOneFromDb($tid)
    {

        $sqlBindArray = array();

        $sql = $this->getSelectStatement(self::_formPredicate);
        array_push($sqlBindArray, $tid);

        $records = QueryUtils::fetchRecords($sql, $sqlBindArray);

        return $records;
    }

    public function getAll($pid)
    {
        $processingResult = new ProcessingResult();
        $sqlBindArray = array();

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
        sqlBeginTrans();
        $transactionId = $this->insertTransaction($pid, $data);
        if ($transactionId == false) {
            return false;
        }


        $lbtDataId = $this->insertTransactionForm($transactionId, $data);
        if ($lbtDataId == false) {
            return false;
        }
        sqlCommitTrans();
        return ["id" => $transactionId, "form_id" => $lbtDataId];
    }

    public function insertTransaction($pid, $data)
    {
        $user = $_SESSION['authUser'];
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
            array(
                $data["type"],
                $pid,
                $data['groupname'],
                $user
            )
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

        $params = array
        (
            $transactionId, $data["body"],
            $transactionId, $data["referralDate"],
            $transactionId, $data["referDiagnosis"],
            $transactionId, $referById,
            $transactionId, $referToId,
            $transactionId, strtolower($data["riskLevel"]),
            $transactionId, $data["includeVitals"],
            $transactionId, $data["authorization"],
            $transactionId, $data["visits"],
            $transactionId, $data["validFrom"],
            $transactionId, $data["validThrough"],
        );

        $results = sqlInsert(
            $sql,
            $params
        );

        if (!$results) {
            return false;
        }

        return $results;
    }

    public function update($tid, $data)
    {

        $referById = $this->getUserIdByNpi($data["referByNpi"]);
        $referToId = $this->getUserIdByNpi($data["referToNpi"]);
        $body = $data["body"];
        $referralDate = $data["referralDate"];
        $referralDiagnosis = $data["referDiagnosis"];
        $riskLevel = strtolower($data["riskLevel"]);
        $includeVitals = $data["includeVitals"];
        $authorization = $data["authorization"];
        $visits = $data["visits"];
        $validFrom = $data["validFrom"];
        $validThrough = $data["validThrough"];

        sqlBeginTrans();
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
        sqlCommitTrans();

        return $this->getOneFromDb($tid);
    }

    public function updateTransactionForm($formId, $fieldId, $value)
    {
        if (empty($value) == false) {
            $sql = "Update lbt_data SET field_value = ? Where field_id = ? and form_id = ?";
            $params = array($value, $fieldId, $formId);
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
        } catch (exception $ex) {
            return $ex;
        }
    }
}
