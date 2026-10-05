<?php

namespace OpenEMR\Tests\Rector\Fixture;

function row(string $a, string $b, string $c): void
{
?>
    <a href="<?php echo attr('x.php?a=' . urlencode($a) . '&b=' . urlencode($b) . '&c=' . urlencode($c)); ?>">link</a>
<?php
}

?>
-----
<?php

namespace OpenEMR\Tests\Rector\Fixture;

function row(string $a, string $b, string $c): void
{
?>
    <a href="<?php echo attr('x.php?' . \OpenEMR\Common\Http\QueryString::build(['a' => $a, 'b' => $b, 'c' => $c])); ?>">link</a>
<?php
}

?>
