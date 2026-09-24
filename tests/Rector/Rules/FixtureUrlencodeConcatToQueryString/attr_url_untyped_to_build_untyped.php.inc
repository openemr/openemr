<?php

namespace OpenEMR\Tests\Rector\Fixture;

function attrUrlUntyped($id): string
{
    return "<a href='x.php?id=" . attr_url($id) . "'>";
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function attrUrlUntyped($id): string
{
    return "<a href='x.php?" . \OpenEMR\Common\Http\QueryString::buildUntyped(['id' => $id]) . "'>";
}

?>
