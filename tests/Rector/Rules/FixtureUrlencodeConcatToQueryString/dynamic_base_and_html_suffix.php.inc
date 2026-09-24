<?php

namespace OpenEMR\Tests\Rector\Fixture;

function anchor(string $webroot, string $file, string $label): string
{
    return "<a href='" . $webroot . "/interface/get.php?key=" . urlencode($file) . "&view=1' class='btn'>" . $label . "</a>";
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function anchor(string $webroot, string $file, string $label): string
{
    return "<a href='" . $webroot . "/interface/get.php?" . \OpenEMR\Common\Http\QueryString::build(['key' => $file, 'view' => '1']) . "' class='btn'>" . $label . "</a>";
}

?>
