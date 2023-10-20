<?php

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

require_once(__DIR__ . "/../../../globals.php");

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */
$bootstrap = Bootstrap::instantiate($GLOBALS['kernel']->getEventDispatcher(), $GLOBALS['kernel']);
$exporter = $bootstrap->getExporter();

$result = null;
$includeDocuments = false;
if (isset($_POST['submit'])) {
    $pid = intval($_POST['pid'] ?? 0);
    $includeDocuments = intval($_POST['include_documents'] ?? 0) === 1;
    if ($pid > 0) {
        $result = $exporter->exportPatient($pid, $includeDocuments);
    } else {
        $result = $exporter->exportAll($includeDocuments);
    }
}
// TODO: twigify this file
?>
<html>
<?php Header::setupHeader(); ?>
<body>
<?php if (isset($result)) : ?>
<a href="<?php echo $result->downloadLink; ?>">Download Export</a>
<p>Tables Exported</p>
<ul>
    <?php foreach ($result->exportedTables as $tableResult) { ?>
        <li><?php echo $tableResult->tableName; ?>: <?php echo $tableResult->count; ?></li>
    <?php } ?>
</ul>
    <?php if ($includeDocuments) : ?>
    <p>Documents Exported: <?php echo $result->exportedDocumentCount; ?></p>
    <?php endif; ?>
<?php else : ?>
<p>Run Export</p>
<!-- add form to run export.  Form needs to have an input checkbox to include documents, an input textbox to specify a patient pid -->
<form method="post" action="moduleConfig.php">
        <div>
        <label>
            Specific Patient To Export (specify pid)
            <input type="text" name="pid" value="" />
        </label>
        </div>
        <div>
        <label>
            Include Patient Document Files From Storage (this will make the export much larger)
            <input type="checkbox" name="include_documents" value="1" />
        </label>
        </div>
    <input type="submit" name="submit" class="btn btn-primary" value="Export">
</form>
<?php endif; ?>

</body>
</html>

