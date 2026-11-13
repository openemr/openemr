<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
     * @param string $table lists or its alias, as the service's own SQL writes it (never a value)
     */
    protected static function countedListEntry(string $table): string
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

    abstract public function getSqlStatement();

    abstract public function makeQdmModel(QdmRecord $recordObj);

    public function executeQuery()
    {
        // The request's patient filter applies to the service's rows, by the pid column each row
        // carries (QdmBuilder requires it). It is never spliced into the service's own SQL, so a
        // WHERE, OR or GROUP BY anywhere in that SQL can't change which rows it filters.
        $serviceSql = $this->getSqlStatement();
        $filterClause = $this->request->getFilter()->getFilterClause();
        if (!is_string($serviceSql) || !is_string($filterClause)) {
            throw new \LogicException('A QDM service gives its SQL, and its request its filter, as strings.');
        }
        $sql = "SELECT * FROM ( $serviceSql ) AS `qdm_rows` WHERE ( $filterClause )";

        // By default, we have no bound values
        $binds = false;
        if (is_array($this->request->getFilter()->getBoundValues())) {
            $binds = $this->request->getFilter()->getBoundValues();
        }

        try {
            $result = sqlStatementThrowException($sql, $binds);
        } catch (SqlQueryException $exception) {
            throw new \Exception('A QDM service query failed.', 0, $exception);
        }

        return $result;
    }

    /**
     * Which rows of an encounter form the measures count, as SQL: the one rule for a counted form row,
     * which observations, vitals, orders and care plan items all use. A row is left out when every
     * forms row filing it is deleted: delete_form.php sets forms.deleted = 1, and for most forms
     * leaves the form's own rows in place. One kept forms row is enough, as when a C-CDA import has
     * filed the same vitals twice and one copy was deleted from the visit. A row no forms row files is
     * kept, unless its form is always filed (QdmForm::alwaysFiled()). The forms row has to be the
     * row's patient's, since form ids can repeat across patients: the Observation form and the C-CDA
     * import pick theirs with an unlocked MAX() + 1.
     *
     * @param string $formId the column holding the row's form id, as the service's own SQL writes it (never a value)
     * @param string $pid    the column holding the row's patient, as the service's own SQL writes it (never a value)
     */
    protected static function countedFormRow(string $formId, string $pid, QdmForm $form): string
    {
        $filings = "SELECT 1 FROM `forms`
                    WHERE `forms`.`form_id` = $formId AND `forms`.`pid` = $pid AND `forms`.`formdir` = '{$form->value}'";
        $kept = "EXISTS ($filings AND `forms`.`deleted` = 0)";
        return $form->alwaysFiled() ? $kept : "(NOT EXISTS ($filings AND `forms`.`deleted` = 1) OR $kept)";
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
