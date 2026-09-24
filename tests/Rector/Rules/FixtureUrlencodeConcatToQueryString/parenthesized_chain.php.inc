<?php

namespace OpenEMR\Tests\Rector\Fixture;

function grouped(string $a, int $b): string
{
    return 'x.php?a=' . (urlencode($a) . ('&b=' . $b));
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function grouped(string $a, int $b): string
{
    return 'x.php?' . \OpenEMR\Common\Http\QueryString::build(['a' => $a, 'b' => $b]);
}

?>
