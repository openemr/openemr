<?php

namespace OpenEMR\Tests\Rector\Fixture;

function boolValue(bool $flag): string
{
    return 'x.php?flag=' . urlencode($flag);
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function boolValue(bool $flag): string
{
    return 'x.php?' . \OpenEMR\Common\Http\QueryString::buildUntyped(['flag' => $flag]);
}

?>
