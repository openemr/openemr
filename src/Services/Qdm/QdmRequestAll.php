<?php


namespace OpenEMR\Services\Qdm;


use OpenEMR\Events\BoundFilter;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;

class QdmRequestAll implements QdmRequestInterface
{

    /**
     * QdmQuery constructor.
     * @param array $pids
     */
    public function __construct()
    {
    }

    public function getFilter()
    {
        return new BoundFilter();
    }
}
