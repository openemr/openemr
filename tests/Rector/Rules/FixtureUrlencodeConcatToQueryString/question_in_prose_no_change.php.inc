<?php

namespace OpenEMR\Tests\Rector\Fixture;

function prose(string $name): string
{
    return 'Delete this? name=' . urlencode($name);
}

?>
