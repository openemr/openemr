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

$tutorialUrl = 'https://medexbank.com/help/tutorial.html';
if (class_exists('\\OpenEMR\\Modules\\MedEx\\MedExConfig')) {
    $tutorialUrl = \OpenEMR\Modules\MedEx\MedExConfig::tutorialUrl();
}

// Check if user is logged in
if (!isset($_SESSION['authUserID'])) {
    die("Not authorized");
}

// Read the HELP.md file
$helpFile = __DIR__ . '/../HELP.md';
$helpContent = '';

// Helper function to generate anchor IDs from header text
function generateAnchorId($text)
{
    $id = strtolower(trim($text));
    $id = preg_replace('/[^a-z0-9\s-]/', '', $id);
    $id = preg_replace('/[\s]+/', '-', $id);
    $id = preg_replace('/-+/', '-', $id);
    return trim((string)$id, '-');
}

/**
 * Render a small markdown subset safely and deterministically.
 * This avoids regex backtracking failures that can blank the help body.
 */
function renderInlineMarkdown($text)
{
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $text);
    return $text;
}

function renderHelpMarkdown($markdown)
{
    $lines = preg_split("/\r\n|\n|\r/", (string)$markdown);
    $html = [];
    $inCode = false;
    $listType = null; // 'ul' | 'ol'
    $inParagraph = false;

    $closeParagraph = function () use (&$html, &$inParagraph) {
        if ($inParagraph) {
            $html[] = '</p>';
            $inParagraph = false;
        }
    };
    $closeList = function () use (&$html, &$listType) {
        if ($listType === 'ul') {
            $html[] = '</ul>';
        } elseif ($listType === 'ol') {
            $html[] = '</ol>';
        }
        $listType = null;
    };

    foreach ($lines as $line) {
        $line = rtrim((string)$line, "\t ");

        if ($inCode) {
            if (preg_match('/^```/', $line)) {
                $html[] = '</code></pre>';
                $inCode = false;
            } else {
                $html[] = htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            continue;
        }

        if (preg_match('/^```/', $line)) {
            $closeParagraph();
            $closeList();
            $html[] = '<pre><code>';
            $inCode = true;
            continue;
        }

        if ($line === '') {
            $closeParagraph();
            $closeList();
            continue;
        }

        if (preg_match('/^(#{1,4})\s+(.*)$/', $line, $m)) {
            $closeParagraph();
            $closeList();
            $level = strlen($m[1]);
            $headingText = trim($m[2]);
            $id = generateAnchorId($headingText);
            $html[] = '<h' . $level . ' id="' . htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . renderInlineMarkdown($headingText)
                . '</h' . $level . '>';
            continue;
        }

        if (preg_match('/^---+$/', $line)) {
            $closeParagraph();
            $closeList();
            $html[] = '<hr>';
            continue;
        }

        if (preg_match('/^\-\s+(.*)$/', $line, $m)) {
            $closeParagraph();
            if ($listType !== 'ul') {
                $closeList();
                $html[] = '<ul>';
                $listType = 'ul';
            }
            $html[] = '<li>' . renderInlineMarkdown($m[1]) . '</li>';
            continue;
        }

        if (preg_match('/^\d+\.\s+(.*)$/', $line, $m)) {
            $closeParagraph();
            if ($listType !== 'ol') {
                $closeList();
                $html[] = '<ol>';
                $listType = 'ol';
            }
            $html[] = '<li>' . renderInlineMarkdown($m[1]) . '</li>';
            continue;
        }

        $closeList();
        if (!$inParagraph) {
            $html[] = '<p>';
            $inParagraph = true;
        } else {
            $html[] = '<br>';
        }
        $html[] = renderInlineMarkdown($line);
    }

    if ($inCode) {
        $html[] = '</code></pre>';
    }
    if ($listType !== null) {
        $closeList();
    }
    if ($inParagraph) {
        $closeParagraph();
    }

    return implode("\n", $html);
}

if (file_exists($helpFile)) {
    $rawHelpContent = file_get_contents($helpFile);
    if ($rawHelpContent === false) {
        $helpContent = '<div class="alert alert-warning">Unable to read help file at: ' . htmlspecialchars($helpFile) . '</div>';
    } else {
        $helpContent = renderHelpMarkdown($rawHelpContent);
        if (trim((string)$helpContent) === '') {
            $helpContent = '<pre>' . htmlspecialchars($rawHelpContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
        }
    }
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
    <style>
        :root {
            --bg-a: #f4f8ff;
            --bg-b: #edf6f0;
            --ink: #0f172a;
            --muted: #4b5563;
            --brand: #0b74d1;
            --brand-2: #0ea5a4;
            --panel: #ffffff;
            --panel-border: #d7e3f4;
            --shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            font-family: "Manrope", "Segoe UI", sans-serif;
            line-height: 1.65;
            padding: 28px 20px;
            color: var(--ink);
            background: radial-gradient(circle at 10% 10%, var(--bg-a), transparent 45%),
                        radial-gradient(circle at 80% 0%, var(--bg-b), transparent 40%),
                        #f7fafc;
        }
        .help-container {
            max-width: 1320px;
            margin: 0 auto;
            background-color: var(--panel);
            padding: 28px;
            border-radius: 18px;
            border: 1px solid var(--panel-border);
            box-shadow: var(--shadow);
        }
        .help-header {
            border-bottom: 1px solid #e7eef8;
            padding-bottom: 18px;
            margin-bottom: 22px;
        }
        .help-header h1 {
            margin: 0;
            font-size: 2rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        .help-header p {
            margin: 8px 0 0;
            color: var(--muted);
        }
        .help-content h1 {
            color: #0f2747;
            border-bottom: 1px solid #dbe8fa;
            padding-bottom: 10px;
            margin-top: 30px;
            margin-bottom: 16px;
            font-size: 1.85rem;
        }
        .help-content h2 {
            color: #12335a;
            margin-top: 26px;
            margin-bottom: 12px;
            font-size: 1.45rem;
            letter-spacing: -0.01em;
        }
        .help-content h3 {
            color: #1f3f67;
            margin-top: 18px;
            margin-bottom: 10px;
            font-size: 1.15rem;
        }
        .help-content h4 {
            color: #30557f;
            margin-top: 16px;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        .help-content ul,
        .help-content ol {
            margin-left: 20px;
            margin-bottom: 16px;
        }
        .help-content li {
            margin-bottom: 6px;
            color: #1f2937;
        }
        .help-content code {
            background-color: #eef4ff;
            border: 1px solid #d4e2fb;
            padding: 2px 7px;
            border-radius: 6px;
            font-family: "Courier New", Courier, monospace;
            font-size: 0.9em;
            color: #0f4ca1;
        }
        .help-content pre {
            background-color: #0d1b2a;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #20324a;
            overflow-x: auto;
            margin: 20px 0;
        }
        .help-content pre code {
            background-color: transparent;
            padding: 0;
            color: #e5edf8;
            border: none;
        }
        .help-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 16px 0;
            border: 1px solid #d8e4f2;
        }
        .help-content table th,
        .help-content table td {
            border: 1px solid #d8e4f2;
            padding: 12px;
            text-align: left;
        }
        .help-content table th {
            background-color: #0f4c8a;
            color: white;
            font-weight: 600;
        }
        .help-content table tr:nth-child(even) {
            background-color: #f8fbff;
        }
        .help-content hr {
            border: none;
            border-top: 1px solid #dbe6f5;
            margin: 20px 0;
        }
        .help-content a {
            color: #0b5db5;
            text-decoration: none;
        }
        .help-content a:hover {
            text-decoration: underline;
        }
        .help-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 24px;
            align-items: start;
        }
        .help-toc {
            position: sticky;
            top: 20px;
            background: linear-gradient(180deg, #f8fbff 0%, #f6fffb 100%);
            border: 1px solid #d3e5fa;
            border-radius: 12px;
            padding: 16px;
            max-height: calc(100vh - 80px);
            overflow: auto;
        }
        .help-toc h4 {
            margin: 0 0 10px;
            font-size: 0.8rem;
            color: #0f4e8f;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 800;
        }
        .help-toc ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .help-toc li {
            margin: 0 0 6px;
        }
        .help-toc a {
            display: block;
            padding: 8px 10px;
            border-radius: 8px;
            color: #15365a;
            text-decoration: none;
            font-size: 0.9rem;
            line-height: 1.3;
            border: 1px solid transparent;
        }
        .help-toc a:hover {
            background: #e8f2ff;
            border-color: #cfe3ff;
            text-decoration: none;
        }
        .help-toc a.is-active {
            background: #dceeff;
            border-color: #bdd9ff;
            font-weight: 700;
        }
        .help-main {
            min-width: 0;
        }
        .help-content {
            color: #1f2937;
            font-size: 1rem;
        }
        @media (max-width: 992px) {
            .help-layout {
                grid-template-columns: 1fr;
            }
            .help-toc {
                position: static;
                max-height: none;
            }
        }
        .back-link {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 999px;
            border: 1px solid #c8d9ee;
            background: #ffffff;
            color: #18446f;
        }
        .tutorial-banner {
            background: linear-gradient(130deg, #0c4a8a, #0c7a9a);
            color: white;
            padding: 16px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 12px 24px rgba(12, 74, 138, 0.22);
        }
        .tutorial-banner .banner-text {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .tutorial-banner .banner-text i {
            font-size: 1.6rem;
        }
        .tutorial-banner h3 {
            margin: 0 0 5px 0;
            font-size: 1rem;
        }
        .tutorial-banner p {
            margin: 0;
            opacity: 0.88;
            font-size: 0.85rem;
        }
        .tutorial-banner .btn-tutorial {
            background: #ffffff;
            color: #0b4c8f;
            padding: 9px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
        }
        .tutorial-banner .btn-tutorial:hover {
            background: #eef6ff;
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Instrument+Sans:wght@400;500;600;700&display=swap');

        :root {
            --mx-bg: #0b1220;
            --mx-bg-soft: #121a2b;
            --mx-panel: rgba(255, 255, 255, 0.92);
            --mx-ink: #0f172a;
            --mx-muted: #475569;
            --mx-accent: #00a3ff;
            --mx-accent-2: #18c08f;
            --mx-border: rgba(148, 163, 184, 0.28);
            --mx-shadow: 0 24px 70px rgba(2, 8, 23, 0.25);
        }

        body {
            font-family: "Instrument Sans", "Segoe UI", sans-serif !important;
            background:
                radial-gradient(900px 500px at 5% -10%, rgba(0, 163, 255, 0.25), transparent 50%),
                radial-gradient(900px 500px at 95% 0%, rgba(24, 192, 143, 0.22), transparent 46%),
                linear-gradient(180deg, var(--mx-bg) 0%, var(--mx-bg-soft) 100%) !important;
            color: var(--mx-ink);
            padding: 24px 20px 28px !important;
        }

        .help-container {
            background: var(--mx-panel) !important;
            backdrop-filter: blur(10px);
            border: 1px solid var(--mx-border) !important;
            border-radius: 22px !important;
            box-shadow: var(--mx-shadow) !important;
            padding: 26px !important;
            max-width: 1380px !important;
        }

        .help-header h1 {
            font-family: "Sora", sans-serif !important;
            font-weight: 800 !important;
            font-size: clamp(1.9rem, 2.5vw, 2.6rem) !important;
            letter-spacing: -0.03em;
        }

        .help-header p {
            color: var(--mx-muted) !important;
            font-size: 1.03rem;
        }

        .help-layout {
            grid-template-columns: 320px 1fr !important;
            gap: 26px !important;
        }

        .help-toc {
            background: linear-gradient(180deg, #f7fbff 0%, #f5fdf9 100%) !important;
            border-radius: 16px !important;
            border: 1px solid #d6e7fb !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.75);
            padding: 16px !important;
        }

        .help-toc h4 {
            font-family: "Sora", sans-serif;
            font-size: 0.75rem !important;
            letter-spacing: 0.14em !important;
            color: #0f4f8f !important;
        }

        .help-toc a {
            font-weight: 500;
            border-radius: 10px !important;
            padding: 9px 11px !important;
            transition: all 0.18s ease;
        }

        .help-toc a:hover {
            transform: translateX(2px);
        }

        .help-main {
            background: #ffffff;
            border: 1px solid #e3ecf8;
            border-radius: 16px;
            padding: 22px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
        }
        .help-quick-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin: 0 0 20px;
        }
        .help-quick-card {
            display: block;
            border: 1px solid #d8e5f7;
            border-radius: 12px;
            padding: 12px 14px;
            text-decoration: none;
            background: linear-gradient(180deg, #ffffff 0%, #f8fcff 100%);
            color: #0f2442;
            transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
        }
        .help-quick-card:hover {
            transform: translateY(-2px);
            border-color: #b9d7ff;
            box-shadow: 0 10px 22px rgba(9, 42, 90, 0.12);
            text-decoration: none;
        }
        .help-quick-title {
            display: block;
            font-family: "Sora", sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .help-quick-text {
            display: block;
            font-size: 0.85rem;
            color: #446183;
            line-height: 1.35;
        }

        .help-content h2 {
            font-family: "Sora", sans-serif !important;
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.02em;
            color: #0f2f57 !important;
            margin-top: 30px !important;
            position: relative;
            padding-left: 16px;
        }

        .help-content h2::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0.22em;
            width: 6px;
            height: 1.2em;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--mx-accent), var(--mx-accent-2));
        }

        .help-content p,
        .help-content li {
            font-size: 1.03rem;
        }

        .help-content ul li {
            margin-bottom: 7px !important;
        }

        .tutorial-banner {
            border-radius: 14px !important;
            background: linear-gradient(120deg, #0a5fb2 0%, #007f9a 45%, #11a86f 100%) !important;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .btn-tutorial {
            border-radius: 10px !important;
            font-weight: 700 !important;
        }

        @media (max-width: 992px) {
            .help-main {
                padding: 16px;
            }
            .help-layout {
                grid-template-columns: 1fr !important;
            }
            .help-quick-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .help-quick-grid {
                grid-template-columns: 1fr;
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
            <a href="<?php echo htmlspecialchars($tutorialUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" target="_blank" class="btn-tutorial">Launch Tutorial →</a>
        </div>

        <div class="help-header">
            <h1>MedEx Communication Guide</h1>
            <p>User guide for front desk, clinical staff, and admins running day-to-day MedEx workflows.</p>
        </div>

        <div class="help-quick-grid">
            <a class="help-quick-card" href="#start-here-connection">
                <span class="help-quick-title">Connection Check</span>
                <span class="help-quick-text">Confirm Online status before any sends.</span>
            </a>
            <a class="help-quick-card" href="#a-la-carte-credits-what-they-are">
                <span class="help-quick-title">Credits Explained</span>
                <span class="help-quick-text">What credits are and what they control.</span>
            </a>
            <a class="help-quick-card" href="#how-to-set-auto-renew">
                <span class="help-quick-title">Set Auto-Renew</span>
                <span class="help-quick-text">Prevent reminder and campaign interruptions.</span>
            </a>
            <a class="help-quick-card" href="#low-balance-and-failed-send-recovery">
                <span class="help-quick-title">Recover Failed Sends</span>
                <span class="help-quick-text">Recharge, retry safely, and stabilize workflow.</span>
            </a>
        </div>

        <div class="help-layout">
            <aside class="help-toc" id="help-toc">
                <h4>On This Page</h4>
                <ul id="help-toc-list"></ul>
            </aside>
            <div class="help-main">
                <div class="help-content" id="help-content">
                    <?php echo $helpContent; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const content = document.getElementById('help-content');
        const tocList = document.getElementById('help-toc-list');
        if (!content || !tocList) {
            return;
        }

        const headings = content.querySelectorAll('h2[id], h3[id]');
        if (!headings.length) {
            document.getElementById('help-toc').style.display = 'none';
            return;
        }

        headings.forEach((heading) => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#' + heading.id;
            a.textContent = heading.textContent || '';
            if (heading.tagName.toLowerCase() === 'h3') {
                a.style.paddingLeft = '18px';
            }
            li.appendChild(a);
            tocList.appendChild(li);
        });

        const links = tocList.querySelectorAll('a');
        const byId = new Map();
        links.forEach((link) => byId.set(link.getAttribute('href').slice(1), link));

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                links.forEach((l) => l.classList.remove('is-active'));
                const active = byId.get(entry.target.id);
                if (active) {
                    active.classList.add('is-active');
                }
            });
        }, { rootMargin: '-20% 0px -70% 0px', threshold: 0.01 });

        headings.forEach((heading) => observer.observe(heading));
    })();
    </script>
</body>
</html>
