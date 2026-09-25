<?php

namespace OpenEMR\Tests\Rector\Fixture;

function arrayKey(string $a): string
{
    return 'x.php?ids[]=' . urlencode($a);
}

?>
