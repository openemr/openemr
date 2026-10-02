<?php

namespace OpenEMR\Tests\Rector\Fixture;

function partialKeys(string $a, string $b, string $c): string
{
    return 'x.php?k=' . urlencode($a) . '&j=' . urlencode($b) . '&k=' . urlencode($c);
}

?>
