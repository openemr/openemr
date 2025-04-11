<?php

/**
 * Webpage wrapper for batch import of ccda documents.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../interface/globals.php");

use OpenEMR\Core\Header;

// Define default values.
$sourcePath_default = $GLOBALS['OE_SITE_DIR'] . '/documents/import_ccdas';
$openemrPath_default = $_SERVER["DOCUMENT_ROOT"] ?? '';
$site_default = $_SESSION['site_id'] ?? 'default';
$isDev_default = 'false';
$enableMoves_default = 'false';
$dedup_default = 'true';

// Set form value variables.
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
            setTimeout(() => {
                window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'});
            }, 0);
        }
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
                <button type="submit" class="btn btn-primary mb-4" id="submit-form" name="submit" value="start">
                    <?php echo xlt('Start Import'); ?>
                </button>
                <button type="button" class="btn btn-warning mb-4" id="reload-form" value="start" onclick="location.replace('batchImport.php');">
                    <?php echo xlt('Reload Form'); ?>
                </button>

            </form>
        </div>
    </div>
    <div class="container">
        <?php
        if (isset($_POST['submit'])) {
            echo "<script>document.getElementById('submit-form').disabled = true;</script>";
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
            echo "    <div class='card-header'>" . xlt('Command') . "</div>";
            echo "    <div class='card-body'><pre class='py-4 px-2'>" . text($cmd) . "\n\n" . text("Move Files Directory = ") . $processedPath . "</pre></div>";
            echo "</div>";
            echo "<div class='card mt-4'>";
            echo "   <div class='card-header'>" . xlt('Import Output') . "</div>";
            echo "   <div class='card-body'><pre class='py-4' id='output'>";

            // Use proc_open instead of popen.
            $descriptor = [
                0 => ["pipe", "r"],  // stdin is a pipe that the child will read from
                1 => ["pipe", "w"],  // stdout is a pipe that the child will write to
                2 => ["pipe", "w"]   // stderr is a pipe that the child will write to
            ];

            $process = proc_open($cmd, $descriptor, $pipes);

            if (is_resource($process)) {
                // Get process status and store the PID in session.
                $status = proc_get_status($process);
                $_SESSION['import_pid'] = $status['pid'];
                echo "<script>console.log('Import process started with PID: " . $status['pid'] . "');</script>";
                // Read output continuously.
                while (!feof($pipes[1])) {
                    $line = fgets($pipes[1]);
                    if ($line !== false) {
                        echo text($line);
                        flush();
                        ob_flush();
                        echo "<script>scrollToBottom();</script>";
                    }
                }
                proc_close($process);
                unset($_SESSION['import_pid']);
            }
            echo "</pre></div>";
            echo "</div>";
        }
        ?>
    </div>
    <script>scrollToBottom();</script>
</body>
</html>
