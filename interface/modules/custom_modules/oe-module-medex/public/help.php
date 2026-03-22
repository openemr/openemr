<?php

/**
 * MedEx Module Help Page
 *
 * Displays the comprehensive help documentation for the MedEx module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Load globals for session and authentication
require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . '/../src/MedExConfig.php');

// Check if user is logged in
if (!isset($_SESSION['authUserID'])) {
    die("Not authorized");
}

// Read the HELP.md file
$helpFile = __DIR__ . '/../HELP.md';
$helpContent = '';

if (file_exists($helpFile)) {
    $helpContent = file_get_contents($helpFile);

    // Helper function to generate anchor IDs from header text
    function generateAnchorId($text) {
        $id = strtolower(trim($text));
        $id = preg_replace('/[^a-z0-9\s-]/', '', $id);
        $id = preg_replace('/[\s]+/', '-', $id);
        $id = preg_replace('/-+/', '-', $id);
        return $id;
    }

    // Basic markdown to HTML conversion
    // Convert headers with anchor IDs for navigation
    $helpContent = preg_replace_callback('/^# (.*?)$/m', function($m) {
        return '<h1 id="' . generateAnchorId($m[1]) . '">' . $m[1] . '</h1>';
    }, $helpContent);
    $helpContent = preg_replace_callback('/^## (.*?)$/m', function($m) {
        return '<h2 id="' . generateAnchorId($m[1]) . '">' . $m[1] . '</h2>';
    }, $helpContent);
    $helpContent = preg_replace_callback('/^### (.*?)$/m', function($m) {
        return '<h3 id="' . generateAnchorId($m[1]) . '">' . $m[1] . '</h3>';
    }, $helpContent);
    $helpContent = preg_replace_callback('/^#### (.*?)$/m', function($m) {
        return '<h4 id="' . generateAnchorId($m[1]) . '">' . $m[1] . '</h4>';
    }, $helpContent);

    // Convert horizontal rules
    $helpContent = preg_replace('/^---$/m', '<hr>', $helpContent);

    // Convert bold
    $helpContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $helpContent);

    // Convert inline code
    $helpContent = preg_replace('/`([^`]+)`/', '<code>$1</code>', $helpContent);

    // Convert links
    $helpContent = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank">$1</a>', $helpContent);

    // Convert unordered lists
    $helpContent = preg_replace('/^\- (.*?)$/m', '<li>$1</li>', $helpContent);
    $helpContent = preg_replace('/(<li>.*?<\/li>\n)+/s', '<ul>$0</ul>', $helpContent);

    // Convert numbered lists
    $helpContent = preg_replace('/^\d+\. (.*?)$/m', '<li>$1</li>', $helpContent);

    // Convert code blocks
    $helpContent = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $helpContent);

    // Convert paragraphs
    $helpContent = preg_replace('/\n\n/', '</p><p>', $helpContent);
    $helpContent = '<p>' . $helpContent . '</p>';

    // Clean up empty paragraphs
    $helpContent = preg_replace('/<p>\s*<\/p>/', '', $helpContent);
} else {
    $helpContent = '<div class="alert alert-warning">Help file not found at: ' . htmlspecialchars($helpFile) . '</div>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedEx Module Help</title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?? ''; ?>/bootstrap/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .help-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .help-header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .help-header h1 {
            color: #007bff;
            margin: 0;
        }
        .help-content h1 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 2em;
        }
        .help-content h2 {
            color: #0056b3;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        .help-content h3 {
            color: #333;
            margin-top: 25px;
            margin-bottom: 12px;
            font-size: 1.25em;
        }
        .help-content h4 {
            color: #666;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        .help-content ul,
        .help-content ol {
            margin-left: 30px;
            margin-bottom: 20px;
        }
        .help-content li {
            margin-bottom: 8px;
        }
        .help-content code {
            background-color: #f4f4f4;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: "Courier New", Courier, monospace;
            font-size: 0.9em;
            color: #d63384;
        }
        .help-content pre {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            overflow-x: auto;
            margin: 20px 0;
        }
        .help-content pre code {
            background-color: transparent;
            padding: 0;
            color: #212529;
        }
        .help-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }
        .help-content table th,
        .help-content table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        .help-content table th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
        }
        .help-content table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .help-content hr {
            border: none;
            border-top: 2px solid #dee2e6;
            margin: 30px 0;
        }
        .help-content a {
            color: #007bff;
            text-decoration: none;
        }
        .help-content a:hover {
            text-decoration: underline;
        }
        .back-link {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .tutorial-banner {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .tutorial-banner .banner-text {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .tutorial-banner .banner-text i {
            font-size: 2rem;
        }
        .tutorial-banner h3 {
            margin: 0 0 5px 0;
            font-size: 1.2rem;
        }
        .tutorial-banner p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .tutorial-banner .btn-tutorial {
            background: white;
            color: #28a745;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .tutorial-banner .btn-tutorial:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        @media print {
            .back-link {
                display: none;
            }
            .tutorial-banner {
                display: none;
            }
            .help-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <a href="javascript:window.close();" class="btn btn-secondary back-link">Close Window</a>

    <div class="help-container">
        <div class="tutorial-banner">
            <div class="banner-text">
                <i>🎓</i>
                <div>
                    <h3>Interactive Tutorial Available</h3>
                    <p>Learn how to use MedEx with our step-by-step visual guide</p>
                </div>
            </div>
            <a href="<?php echo htmlspecialchars(MedExConfig::mainSiteUrl() . '/help/tutorial.html'); ?>" target="_blank" class="btn-tutorial">Launch Tutorial →</a>
        </div>

        <div class="help-header">
            <h1>📘 MedEx Module Help</h1>
            <p class="text-muted">Comprehensive documentation for the MedEx Communication Platform</p>
        </div>

        <div class="help-content">
            <?php echo $helpContent; ?>
        </div>
    </div>
</body>
</html>
