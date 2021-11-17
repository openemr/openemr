<?php


namespace OpenEMR\Services\Qdm\Interfaces;


use OpenEMR\Cqm\Qdm\QDMBaseType;

interface QdmServiceInterface
{
    public function getSqlStatement();

    public function makeQdmModel(array $record);

    public function executeQuery();
}
