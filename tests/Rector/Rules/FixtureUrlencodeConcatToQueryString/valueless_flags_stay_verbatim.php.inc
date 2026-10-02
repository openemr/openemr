<?php

namespace OpenEMR\Tests\Rector\Fixture;

function documentUrl(string $webroot, int $pid): string
{
    return $webroot . '/controller.php?document&retrieve&patient_id=' . urlencode((string) $pid) . '&document_id=';
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function documentUrl(string $webroot, int $pid): string
{
    return $webroot . '/controller.php?document&retrieve&' . \OpenEMR\Common\Http\QueryString::build(['patient_id' => (string) $pid, 'document_id' => '']);
}

?>
