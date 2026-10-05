<?php

namespace OpenEMR\Tests\Rector\Fixture;

function nullable(?string $id): string
{
    return 'x.php?id=' . urlencode($id);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function nullable(?string $id): string
{
    return 'x.php?' . \OpenEMR\Common\Http\QueryString::buildUntyped(['id' => $id]);
}

?>
