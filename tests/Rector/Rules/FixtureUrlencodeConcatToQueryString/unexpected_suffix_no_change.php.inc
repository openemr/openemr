<?php

namespace OpenEMR\Tests\Rector\Fixture;

function oddSuffix(string $a): string
{
    return 'x.php?a=' . urlencode($a) . '%20tail';
}

?>
