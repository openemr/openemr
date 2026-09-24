<?php

namespace OpenEMR\Tests\Rector\Fixture;

function mixedValue(string $a): string
{
    return 'x.php?a=prefix' . urlencode($a);
}

?>
