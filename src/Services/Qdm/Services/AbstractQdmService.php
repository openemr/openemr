<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
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
        $trimmedId = ltrim($id, '\x0');
        $decimal = hexdec($trimmedId);
        return $decimal;
    }

    public function validDateOrNull($date)
    {
        if (strpos($date, '0000-00-00') !== false) {
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
        if (strpos(strtolower($sql), 'where')) {
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
        $res = explode(":", $openEmrCode); //split diagnosis type and code

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
        $multiple = explode(";", $openEmrMultiCode);
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
