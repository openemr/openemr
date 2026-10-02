<?php

namespace OpenEMR\Tests\Rector\Fixture;

function untyped($id): string
{
    return 'x.php?id=' . urlencode($id);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function untyped($id): string
{
    return 'x.php?' . \OpenEMR\Common\Http\QueryString::buildUntyped(['id' => $id]);
}

?>
