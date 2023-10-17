<?php

namespace OpenEMR\Modules\EhiExporter;

require_once(__DIR__ . "/../../../globals.php");

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */
$bootstrap = Bootstrap::instantiate($GLOBALS['kernel']->getEventDispatcher(), $GLOBALS['kernel']);
$exporter = $bootstrap->getExporter();

$result = $exporter->exportAll();
?>
<html>
<body>
<a href="<?php echo $result->downloadLink; ?>">Download Export</a>
<p>Tables Exported</p>
<ul>
    <?php foreach ($result->exportedTables as $tableResult) { ?>
        <li><?php echo $tableResult->tableName; ?>: <?php echo $tableResult->count; ?></li>
    <?php } ?>
</ul>
</body>
</html>

