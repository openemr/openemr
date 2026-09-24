<?php

namespace OpenEMR\Tests\Rector\Fixture;

function preEncodedLiteral(string $a): string
{
    return 'x.php?q=a%20b&a=' . urlencode($a);
}

?>
