<?php

namespace OpenEMR\Tests\Rector\Fixture;

function jump(string $section): string
{
    return 'x.php?s=' . urlencode($section) . '&blank=#top';
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function jump(string $section): string
{
    return 'x.php?' . \OpenEMR\Common\Http\QueryString::build(['s' => $section, 'blank' => '']) . '#top';
}

?>
