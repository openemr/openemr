<?php

namespace OpenEMR\Tests\Rector\Fixture;

function spaced(string $id): string
{
    return "&nbsp;<a href='x.php?id=" . urlencode($id) . "'>view</a>";
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function spaced(string $id): string
{
    return "&nbsp;<a href='x.php?" . \OpenEMR\Common\Http\QueryString::build(['id' => $id]) . "'>view</a>";
}

?>
