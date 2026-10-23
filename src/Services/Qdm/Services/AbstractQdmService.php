<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qdm\QdmRecord;

abstract class AbstractQdmService
{
    /**
     * Value in ob_reason_status indicates negated observation (observation not done)
     */
    public const NEGATED = 'negated';

    protected $request;
    protected $codeTypesService;

    /**
     * We want to try to prevent implementations of this base service
     * because we want to pass in a standard set of dependencies.
     *
     * AbstractQdmService constructor.
     *
     * @param QdmRequestInterface $request
     * @param CodeTypesService    $codeTypesService
     */
    final public function __construct(QdmRequestInterface $request, CodeTypesService $codeTypesService)
    {
        $this->request = $request;
        $this->codeTypesService = $codeTypesService;
    }

    /**
     * Which lists rows (problems, concerns, allergies) the measures count, as SQL: not marked
     * Entered in Error or Refuted in the issue's Verification Status (lists.verification, from
     * the condition-verification and allergyintolerance-verification lists), which the issue
     * screen offers for both. Any other status is kept, including the column's default, empty.
     *
     * @param string $table lists or its alias, as it appears in the query
     */
    public static function countedListEntry(string $table): string
    {
        return "$table.`verification` NOT IN ('entered-in-error', 'refuted')";
    }

    public static function convertToObjectIdBSONFormat($id)
    {
        // max bigint size will fit in 16 characters so we will always have enough space for this.
        $padded_hex = sprintf("%024X", $id);
        return $padded_hex;
    }

    public static function convertIdFromBSONObjectIdFormat($id)
    {
        // max bigint size is 8 bytes which will fit fine
        // string ID should be prefixed with 0s so the converted data type should be far smaller
        $trimmedId = ltrim((string) $id, '\x0');
        $decimal = hexdec($trimmedId);
        return $decimal;
    }

    public function validDateOrNull($date)
    {
        if (empty($date) || str_contains((string) $date, '0000-00-00')) {
            return null;
        }
        return new DateTime([
            'date' => $date
        ]);
    }

    public function getPatientIdColumn()
    {
        return 'pid';
    }

    abstract public function getSqlStatement();

    abstract public function makeQdmModel(QdmRecord $recordObj);

    public function executeQuery()
    {
        $sql = $this->getSqlStatement();

        $filterClause = $this->request->getFilter()->getFilterClause();
        if ($this->getPatientIdColumn() !== 'pid') {
            $filterClause = str_replace('pid', $this->getPatientIdColumn(), $filterClause);
        }

        // Apply the filter based on request type
        // If there's already a "WHERE" clause, then add an "AND" otherwise add our WHERE clause.
        if (strpos(strtolower((string) $sql), 'where')) {
            $sql .= " AND ( " . $filterClause . " )";
        } else {
            $sql .= " WHERE ( " . $filterClause . " )";
        }

        // By default, we have no bound values
        $binds = false;
        if (is_array($this->request->getFilter()->getBoundValues())) {
            $binds = $this->request->getFilter()->getBoundValues();
        }

        try {
            $result = sqlStatementThrowException($sql, $binds);
        } catch (SqlQueryException $exception) {
            error_log($exception->getMessage());
            throw new \Exception("There is likely an error in Service query, must contain a patient ID. " .
                " 'pid' not found and getPatientIdColumn() not implemented.");
        }

        return $result;
    }

    /**
     * SQL that is true unless the row's form was deleted from the visit: delete_form.php sets
     * forms.deleted = 1 and leaves the form's own rows in place. The form has to be the row's
     * patient's too, since form ids can repeat across patients (the procedure order and others
     * pick theirs with an unlocked MAX() + 1). A row with no forms row is kept.
     *
     * @param string $formId  the column holding the row's form id, as it appears in the query
     * @param string $pid     the column holding the row's patient, as it appears in the query
     * @param string $formdir the form's directory, as forms.formdir records it
     */
    public static function notInDeletedForm(string $formId, string $pid, string $formdir): string
    {
        return "NOT EXISTS (
                    SELECT 1 FROM `forms`
                    WHERE `forms`.`form_id` = $formId AND `forms`.`pid` = $pid
                      AND `forms`.`formdir` = '$formdir' AND `forms`.`deleted` = 1
                )";
    }

    /**
     * @return QdmRequestInterface
     */
    public function getRequest(): QdmRequestInterface
    {
        return $this->request;
    }

    public function getSystemForCodeType($codeType)
    {
        // If there is a space in the name, replace with a dash, for example "SNOMED CT" becomes "SNOMED-CT"
        // because that's what we have in our lookup table
        $codeType = str_replace(" ", "-", $codeType);

        if ($codeType == 'OID') {
            // When there is a negation, the code is an OID from a measure value set.
            // There is no official code system for this, as they are OIDs
            $system = '';
        } elseif ($codeType == 'HCPCS-Level-II') {
            $system = '2.16.840.1.113883.6.285';
        } else {
            $system = $this->codeTypesService->getSystemForCodeType($codeType, true);
        }

        return $system;
    }

    /**
     * Convert a code formatted in openEMR database style, ie: system:code
     * to a QDM Object
     *
     * @param  $openEmrCode
     * @return Code|null
     * @throws \Exception
     */
    public function makeQdmCode($openEmrCode)
    {
        $codeModel = null;
        $code = null;
        $system = null;
        $res = !empty($openEmrCode) ? explode(":", (string) $openEmrCode) : []; //split diagnosis type and code

        // TODO For some reason, the import imports allergy codes like this: 'RXNORM:CVX:135' OR 'RXNORM:CVX:135'
        // so we have to account for the case where there are three parts to our exploded code
        if (count($res) == 3) {
            $code = $res[2];
            $system = $res[1];
        } elseif (count($res) == 2) {
            $code = $res[1];
            $system = $res[0];
        }

        if (
            !empty($code)
            && !empty($system)
        ) {
            $codeModel = new Code([
                'code' => $code,
                'system' => $this->getSystemForCodeType($system)
            ]);
        }

        return $codeModel;
    }

    /**
     * Convert a field containing multiple codes in a single string, into an array of QDM codes.
     *
     * For issues that have multiple diagnosis coded, they are semicolon-separated
     * explode() will return an array containing the individual diagnosis if there is no semicolon.
     *
     * @param  $openEmrMultiCode
     * @return array
     */
    public function explodeAndMakeCodeArray($openEmrMultiCode)
    {
        $multiple = !empty($openEmrMultiCode) ? explode(";", (string) $openEmrMultiCode) : [];
        $codes = [];
        foreach ($multiple as $individual) {
            $code = $this->makeQdmCode($individual);
            if ($code !== null) {
                $codes[] = $code;
            }
        }
        return $codes;
    }
}
