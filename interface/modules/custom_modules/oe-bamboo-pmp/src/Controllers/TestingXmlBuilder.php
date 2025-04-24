<?php

/*
 *
 *   package   OpenEMR
 *   link      http://www.open-emr.org
 *   author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)
 *   All rights reserved
 */

namespace Juggernaut\Module\Bamboo\Controllers;


use Juggernaut\Module\Bamboo\Interfaces\DataRequestXmlBuilders;
class TestingXmlBuilder
{
    private DataRequestXmlBuilders $dataRequestXmlBuilders;

    public function __construct(DataRequestXmlBuilders $dataRequestXmlBuilders)
    {
        $this->dataRequestXmlBuilders = $dataRequestXmlBuilders;
    }
    public function fetchXmlBuild()
    {
        return $this->dataRequestXmlBuilders->buildReportDataRequestXml();
    }
}
