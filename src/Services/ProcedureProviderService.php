<?php

/**
 * ProcedureProviderService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Common\Database\QueryUtils;

class ProcedureProviderService extends BaseService
{
    private const PROVIDER_TABLE = "procedure_providers";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::PROVIDER_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::PROVIDER_TABLE]);
    }

    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        $sqlBindArray = array();
        $sql = "SELECT  prov.ppid
                        ,prov.uuid
                        ,prov.name
                        ,prov.npi
                        ,prov.send_app_id
                        ,prov.send_fac_id
                        ,prov.recv_app_id
                        ,prov.recv_fac_id
                        ,prov.direction
                        ,prov.protocol
                        ,prov.remote_host
                        ,prov.orders_path
                        ,prov.results_path
                        ,prov.notes
                        ,prov.lab_director
                        ,prov.active
                        ,prov.type
                        FROM procedure_providers prov
                        ";

        $processingResult = new ProcessingResult();
        try {
            $whereFragment = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
            $sql .= $whereFragment->getFragment();
            $records = QueryUtils::fetchRecords($sql, $whereFragment->getBoundValues());

            if (!empty($records)) {
                foreach ($records as $row) {
                    $resultRecord = $this->createResultRecordFromDatabaseResult($row);
                    $processingResult->addData($resultRecord);
                }
            }
        } catch (SqlQueryException $exception) {
            // we shouldn't hit a query exception
            (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $processingResult->addInternalError("Error selecting data from database");
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error($exception->getMessage(), $exception);
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $processingResult;
    }
}
