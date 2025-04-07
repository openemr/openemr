<?php

require_once("../../../interface/globals.php");

use OpenEMR\Core\Header;

// Define default values.
$sourcePath_default = $GLOBALS['OE_SITE_DIR'] . '/documents/import_ccdas';
$openemrPath_default = $_SERVER["DOCUMENT_ROOT"] ?? '';
$site_default = $_SESSION['site_id'] ?? 'default';
$isDev_default = 'false';
$enableMoves_default = 'false';
$dedup_default = 'true';

// Set form value variables: use submitted values if available; otherwise, use defaults.
$sourcePath_val = isset($_POST['sourcePath']) ? trim($_POST['sourcePath']) : $sourcePath_default;
$openemrPath_val = isset($_POST['openemrPath']) ? trim($_POST['openemrPath']) : $openemrPath_default;
$site_val = isset($_POST['site']) ? trim($_POST['site']) : $site_default;
$isDev_val = isset($_POST['isDev']) ? trim($_POST['isDev']) : $isDev_default;
$enableMoves_val = isset($_POST['enableMoves']) ? trim($_POST['enableMoves']) : $enableMoves_default;
$dedup_val = isset($_POST['dedup']) ? trim($_POST['dedup']) : $dedup_default;

ob_implicit_flush(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('CCDA Batch Import'); ?></title>
    <script>
        function scrollToBottom() {
            // Allow form submission to continue, then scroll
            setTimeout(() => {
                window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'});
            }, 0);
        }
    </script>
    <script>
        $('#importForm').on('submit', function () {
            setTimeout(function () {
                $('html, body').animate({scrollTop: $(document).height()}, 'slow');
            }, 0);
        });
    </script>

</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-1"><?php echo xlt('Batch Import CCDAs'); ?></h1>
        <div class="col-12">
            <label><?php echo xlt('This tool allows you to batch import CCDAs into OpenEMR.'); ?></label><br />
            <label><?php echo xlt('Please ensure that the source path contains the CCDAs to be imported.'); ?></label><br />
            <label><?php echo xlt('The OpenEMR path is where OpenEMR is installed.'); ?></label><br />
            <p>
                <?php
                echo "<h5>Usage: php import_ccda.php [OPTIONS]</h5>";
                echo "Options:<br />";
                echo "  --authName      Required if isDev=false. userAuth so Documents can be saved/moved.<br />";
                echo "  --sourcePath     Required. Path to the directory containing CCDA files to import.<br />";
                echo "  --site           Required. OpenEMR site ID.<br />";
                echo "  --openemrPath    Required. Path to OpenEMR web root.<br />";
                echo "  --isDev          Optional. Set to 'true' for development mode, 'false' for production. Default: true.<br />";
                echo "  --enableMoves    Optional. Set to 'true' to move processed files, 'false' to disable. Default: false.<br />";
                echo "  --dedup          Optional. Set to 'true' to enable duplicate checking, 'false' to disable. Default: false.<br />";
                echo "  --help           Show this help message.<br />";
                ?>
            </p><br />
        </div>
        <div class="col-12">
            <form id="importForm" method="post" onload="scrollToBottom()">
                <div class="form-group">
                    <label for="sourcePath">Source Path:</label>
                    <input type="text" class="form-control" id="sourcePath" name="sourcePath"
                        value="<?php echo attr($sourcePath_val); ?>"
                        placeholder="/path/to/import/documents" required>
                </div>
                <div class="form-group">
                    <label for="openemrPath">OpenEMR Path:</label>
                    <input type="text" class="form-control" id="openemrPath" name="openemrPath"
                        value="<?php echo attr($openemrPath_val); ?>"
                        placeholder="/var/www/openemr" required>
                </div>
                <div class="form-group">
                    <label for="site">Site:</label>
                    <input type="text" class="form-control" id="site" name="site"
                        value="<?php echo attr($site_val); ?>"
                        placeholder="default" required>
                </div>
                <div class="form-group">
                    <label for="isDev">Development Mode (true/false):</label>
                    <select class="form-control" id="isDev" name="isDev">
                        <option value="true" <?php echo ($isDev_val === 'true') ? 'selected' : ''; ?>>True</option>
                        <option value="false" <?php echo ($isDev_val === 'false') ? 'selected' : ''; ?>>False</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="enableMoves">Enable Moves (true/false):</label>
                    <select class="form-control" id="enableMoves" name="enableMoves">
                        <option value="false" <?php echo ($enableMoves_val === 'false') ? 'selected' : ''; ?>>False</option>
                        <option value="true" <?php echo ($enableMoves_val === 'true') ? 'selected' : ''; ?>>True</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dedup">Dedup (true/false):</label>
                    <select class="form-control" id="dedup" name="dedup">
                        <option value="false" <?php echo ($dedup_val === 'false') ? 'selected' : ''; ?>>False</option>
                        <option value="true" <?php echo ($dedup_val === 'true') ? 'selected' : ''; ?>>True</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-4" name="submit" value="start">Start Import</button>
            </form>
        </div>
    </div>
    <div class="container">
        <?php
        if (isset($_POST['submit'])) {
            // Build the command.
            $sourcePath = escapeshellarg($sourcePath_val);
            $processedPath = text(rtrim($sourcePath_val, '/') . "/processed");
            $site = escapeshellarg($site_val);
            $openemrPath = escapeshellarg($openemrPath_val);
            $isDev = escapeshellarg($isDev_val);
            $enableMoves = escapeshellarg($enableMoves_val);
            $dedup = escapeshellarg($dedup_val);
            $authName = escapeshellarg($_SESSION['authUser'] ?? 'admin');
            $scriptPath = __DIR__ . '/import_ccda.php';
            $cmd = "php " . escapeshellarg($scriptPath) .
                " --authName=$authName" .
                " --sourcePath=$sourcePath" .
                " --site=$site" .
                " --openemrPath=$openemrPath" .
                " --isDev=$isDev" .
                " --enableMoves=$enableMoves" .
                " --dedup=$dedup";

            echo "<div class='card mb-4'>";
            echo "  <div class='card-header'>" . xlt('Command') . "</div>";
            echo "  <div class='card-body'><pre class='py-4 px-2'>" . text($cmd) . "\n\nMove Files Directory = " . $processedPath . "</pre></div>";
            echo "</div>";
            echo "<div class='card mt-4'>";
            echo "  <div class='card-header'>" . xlt('Import Output') . "</div>";
            echo "  <div class='card-body'><pre class='py-4' id='output'>";
            // Open the process for reading.
            $handle = popen($cmd, 'r');
            // Read and output each line as it is generated.
            if (is_resource($handle)) {
                while (!feof($handle)) {
                    $line = fgets($handle);
                    if ($line !== false) {
                        echo text($line);
                        // Flush output to the browser.
                        flush();
                        ob_flush();
                    }
                }
                pclose($handle);
            }
            echo "</pre></div>";
            echo "</div>";
        }
        ?>
    </div>
    <script>scrollToBottom()</script>
</body>
</html>
