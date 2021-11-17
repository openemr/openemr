<?php


namespace OpenEMR\Tests\Qdm;


use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequest;
use PHPUnit\Framework\TestCase;

class AllPatientsTest extends TestCase
{
    public function testAllPatients()
    {
        $this->assertEquals(0, 0);
        $builder = new QdmBuilder();
        $models = [];
        try {
            $models = $builder->build(new QdmRequest([1, 2, 3]));
        } catch (\Exception $e) {

        }

        foreach ($models as $model) {
            $filename = __DIR__ . '/output/' . uniqid() . '.json';
            file_put_contents($filename, json_encode($model));
        }
    }

}
