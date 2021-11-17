<?php

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\AbstractType;
use OpenEMR\Services\CodeTypesService;

abstract class AbstractQdmService
{
    protected $request;
    protected $codeTypesService;

    /**
     * We want to try to prevent implementations of this base service
     * because we want to pass in a standard set of dependencies.
     *
     * AbstractQdmService constructor.
     * @param QdmRequest $request
     * @param CodeTypesService $codeTypesService
     */
    final public function __construct(QdmRequest $request, CodeTypesService $codeTypesService)
    {
        $this->request = $request;
        $this->codeTypesService = $codeTypesService;
    }

    public abstract function getSqlStatement();

    public abstract function makeQdmModel(array $record);

    public function executeQuery()
    {
        $sql = $this->getSqlStatement();
        $records = sqlStatement($sql);
        return $records;
    }

    /**
     * @return QdmRequest
     */
    public function getRequest(): QdmRequest
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
     * @return Code
     */
    public function makeQdmCode($openEmrCode)
    {
        $res = explode(":", $openEmrCode); //split diagnosis type and code
        return new Code([
            'code' => $res[1],
            'system' => $this->getSystemForCodeType($res[0])
        ]);
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
