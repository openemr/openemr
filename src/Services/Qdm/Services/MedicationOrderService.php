<?php


namespace OpenEMR\Services\Qdm\Services;


use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\MedicationActive;
use OpenEMR\Cqm\Qdm\MedicationOrder;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class MedicationOrderService extends AbstractMedicationService implements QdmServiceInterface
{
    public function getModelClass()
    {
        return MedicationOrder::class;
    }
}
