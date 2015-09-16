<?php

namespace DOMPDFModuleTest\Service;

use DOMPDFModuleTest\Framework\TestCase;

class PdfStrategyTest extends TestCase
{
    public function testUniqueInstancesFromFactory()
    {
        $dompdfInstance1 = $this->getServiceManager()->get('dompdf');
        $dompdfInstance2 = $this->getServiceManager()->get('dompdf');
        
        $this->assertNotSame($dompdfInstance1, $dompdfInstance2);
    }
}
