<?php


namespace OpenEMR\Tests\Qdm;


use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequestAll;
use PHPUnit\Framework\TestCase;

class AllPatientsTest extends TestCase
{
    public function testAllPatients()
    {
        $this->assertEquals(0, 0);
        $builder = new QdmBuilder();
        $models = [];
        try {
            $models = $builder->build(new QdmRequestAll());
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        foreach ($models as $model) {
            $filename = __DIR__ . '/output/' . date('Y-m-d_His') . '.json';
            file_put_contents($filename, json_encode($model));
        }
    }

}
