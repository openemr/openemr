<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\Data;

function template(string $id, string $name, array $rows): void
{
    ?>
<a href="x.php?id=<?php echo attr_url($id); ?>&mode=edit">edit</a>
    <?php foreach ($rows as $row) { ?>
    <a href="x.php?mode=view&row=<?php echo $row; ?>">view</a>
<?php } ?>
<a href="x.php?<?php echo attr($name); ?>=1">dynamic</a>
<a href="x.php?<?php echo attr(\OpenEMR\Common\Http\QueryString::build(['id' => $id])); ?>">built</a>
<p>Total: <?php echo attr($id); ?></p>
<a href="<?php echo attr($name); ?>document_id=<?php echo attr_url($id); ?>">continued</a>
<script>
    var url = 'x.php?mode=list&order=' + <?php echo js_url($id); ?>;
    var sum = 'total ' + <?php echo js_escape($id); ?>;
</script>
    <?php
}
