<?php

namespace OpenEMR\Tests\Rector\Fixture;

function adjacent(string $a, string $b): string
{
    return 'x.php?a=' . urlencode($a) . urlencode($b);
}

?>
