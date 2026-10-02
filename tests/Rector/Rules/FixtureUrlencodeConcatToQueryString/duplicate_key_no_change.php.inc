<?php

namespace OpenEMR\Tests\Rector\Fixture;

function duplicate(string $a, string $b): string
{
    return 'x.php?k=' . urlencode($a) . '&k=' . urlencode($b);
}

?>
