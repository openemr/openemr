<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;

abstract class AbstractQdmService
{
    protected $request;
    protected $codeTypesService;

    /**
     * We want to try to prevent implementations of this base service
     * because we want to pass in a standard set of dependencies.
     *
     * AbstractQdmService constructor.
     * @param QdmRequestInterface $request
     * @param CodeTypesService $codeTypesService
     */
    final public function __construct(QdmRequestInterface $request, CodeTypesService $codeTypesService)
    {
        $this->request = $request;
        $this->codeTypesService = $codeTypesService;
    }

    public function getPatientIdColumn()
    {
        return 'pid';
    }

    public abstract function getSqlStatement();

    public abstract function makeQdmModel(array $record);

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
        } catch(SqlQueryException $exception) {
            error_log($exception->getMessage());
            throw new \Exception("There is likely an error in Service query, must contain a patient ID. 'pid' not found and getPatientIdColumn() not implemented.");
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
        return $this->codeTypesService->getSystemForCodeType($codeType, true);
    }

    /**
     * Convert a code formatted in openEMR database style, ie: system:code
     * to a QDM Object
     *
     * @param $openEmrCode
     * @return Code|null
     * @throws \Exception
     */
    public function makeQdmCode($openEmrCode)
    {
        $code = null;
        $res = explode(":", $openEmrCode); //split diagnosis type and code
        if (
            !empty($res[0]) &&
            !empty($res[1])
        ) {
            // If there is a space in the name, replace with a dash, for example "SNOMED CT" becomes "SNOMED-CT" because that's what we have in our lookup table
            $systemName = str_replace(" ", "-", $res[0]);
            $code = new Code([
                'code' => $res[1],
                'system' => $this->getSystemForCodeType($systemName)
            ]);
        }

        return $code;
    }

    /**
     * Convert a field containing multiple codes in a single string, into an array of QDM codes.
     *
     * For issues that have multiple diagnosis coded, they are semicolon-separated
     * explode() will return an array containing the individual diagnosis if there is no semicolon.
     *
     * @param $openEmrMultiCode
     * @return array
     */
    public function explodeAndMakeCodeArray($openEmrMultiCode)
    {
        $multiple = explode(";", $openEmrMultiCode);
        $codes = [];
        foreach ($multiple as $individual) {
            $code = $this->makeQdmCode($individual);
            $codes[] = $code;
        }
        return $codes;
    }
}
