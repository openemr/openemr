<?php

namespace OpenEMR\Tests\Rector\Fixture;

function raw(string $id): string
{
    return 'x.php?id=' . rawurlencode($id);
}

?>
