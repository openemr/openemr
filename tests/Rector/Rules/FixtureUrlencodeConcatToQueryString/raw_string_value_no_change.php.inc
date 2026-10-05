<?php

namespace OpenEMR\Tests\Rector\Fixture;

function preEncoded(string $qs): string
{
    return 'x.php?a=1&b=' . $qs;
}

?>
