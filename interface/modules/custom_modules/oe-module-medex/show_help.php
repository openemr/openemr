<?php

/**
 * MedEx Module Help - Modal Display
 *
 * Displays help content in a modern modal overlay matching the module's design system.
 * Called by ModuleManagerListener when user clicks Help button in Module Manager.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Read and parse the HELP.md file
$helpFile = __DIR__ . '/HELP.md';
$helpContent = '';

if (file_exists($helpFile)) {
    $markdown = file_get_contents($helpFile);

    // Parse markdown into structured sections
    $sections = [];
    $currentSection = null;
    $lines = explode("\n", $markdown);

    foreach ($lines as $line) {
        // Main section (## Header)
        if (preg_match('/^## (.+)$/', $line, $matches)) {
            if ($currentSection) {
                $sections[] = $currentSection;
            }
            $currentSection = [
                'title' => trim($matches[1]),
                'content' => '',
                'id' => strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($matches[1])))
            ];
        } elseif ($currentSection) {
            $currentSection['content'] .= $line . "\n";
        }
    }
    if ($currentSection) {
        $sections[] = $currentSection;
    }

    // Function to convert markdown to HTML
    function mdToHtml($md) {
        // Headers
        $md = preg_replace('/^### (.+)$/m', '<h4 class="help-h4">$1</h4>', $md);
        $md = preg_replace('/^#### (.+)$/m', '<h5 class="help-h5">$1</h5>', $md);

        // Bold
        $md = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $md);

        // Inline code
        $md = preg_replace('/`([^`]+)`/', '<code>$1</code>', $md);

        // Links
        $md = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank">$1</a>', $md);

        // Unordered lists
        $md = preg_replace('/^\s*[-*]\s+(.+)$/m', '<li>$1</li>', $md);
        $md = preg_replace('/(<li>.+<\/li>\n?)+/s', '<ul>$0</ul>', $md);

        // Numbered lists
        $md = preg_replace('/^\s*\d+\.\s+(.+)$/m', '<li>$1</li>', $md);

        // Code blocks
        $md = preg_replace('/```(.+?)```/s', '<pre><code>$1</code></pre>', $md);

        // Horizontal rules
        $md = str_replace('---', '<hr class="help-hr">', $md);

        // Paragraphs
        $md = preg_replace('/\n\n+/', '</p><p>', $md);
        $md = '<p>' . $md . '</p>';
        $md = preg_replace('/<p>\s*<\/p>/', '', $md);
        $md = preg_replace('/<p>(<[hul]|<hr)/', '$1', $md);
        $md = preg_replace('/(<\/[hul]>|<\/pre>|<hr[^>]*>)<\/p>/', '$1', $md);

        return $md;
    }
} else {
    $sections = [[
        'title' => 'Error',
        'content' => 'Help documentation not found.',
        'id' => 'error'
    ]];
}
?>

<style id="medexHelpStyle">
.medex-help-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.medex-help-modal {
    background: white;
    border-radius: 12px;
    max-width: 1400px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    animation: slideUp 0.3s ease-out;
}

.medex-help-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 24px 30px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}

.medex-help-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.medex-help-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 24px;
    line-height: 1;
    transition: all 0.2s;
}

.medex-help-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.medex-help-body {
    display: flex;
    flex: 1;
    overflow: hidden;
}

.medex-help-sidebar {
    width: 280px;
    background: #f8f9fa;
    border-right: 1px solid #e9ecef;
    overflow-y: auto;
    flex-shrink: 0;
}

.medex-help-nav {
    padding: 20px 0;
}

.medex-help-nav-item {
    padding: 12px 24px;
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid transparent;
    font-size: 14px;
    color: #495057;
    font-weight: 500;
}

.medex-help-nav-item:hover {
    background: #e9ecef;
    border-left-color: #667eea;
    color: #667eea;
}

.medex-help-nav-item.active {
    background: white;
    border-left-color: #667eea;
    color: #667eea;
    font-weight: 600;
}

.medex-help-content {
    flex: 1;
    overflow-y: auto;
    padding: 30px 40px;
}

.medex-help-section {
    display: none;
    animation: fadeIn 0.3s ease-out;
}

.medex-help-section.active {
    display: block;
}

.medex-help-section h3 {
    color: #667eea;
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 24px 0;
    padding-bottom: 12px;
    border-bottom: 3px solid #667eea;
}

.help-h4 {
    color: #495057;
    font-size: 20px;
    font-weight: 600;
    margin: 28px 0 16px 0;
}

.help-h5 {
    color: #6c757d;
    font-size: 16px;
    font-weight: 600;
    margin: 20px 0 12px 0;
}

.medex-help-content p {
    line-height: 1.7;
    color: #495057;
    margin-bottom: 16px;
}

.medex-help-content ul,
.medex-help-content ol {
    margin: 16px 0 16px 24px;
    line-height: 1.8;
}

.medex-help-content li {
    color: #495057;
    margin-bottom: 8px;
}

.medex-help-content code {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 2px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #d63384;
}

.medex-help-content pre {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 20px 0;
}

.medex-help-content pre code {
    background: none;
    border: none;
    padding: 0;
    color: #212529;
}

.medex-help-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
}

.medex-help-content th,
.medex-help-content td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.medex-help-content th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 14px;
}

.medex-help-content tr:last-child td {
    border-bottom: none;
}

.medex-help-content tr:nth-child(even) {
    background: #f8f9fa;
}

.help-hr {
    border: none;
    border-top: 2px solid #e9ecef;
    margin: 32px 0;
}

.medex-help-content a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.medex-help-content a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.medex-help-content strong {
    color: #212529;
    font-weight: 600;
}

/* Search box */
.medex-help-search {
    padding: 16px 20px;
    border-bottom: 1px solid #e9ecef;
}

.medex-help-search input {
    width: 100%;
    padding: 10px 16px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
}

.medex-help-search input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Scrollbar styling */
.medex-help-sidebar::-webkit-scrollbar,
.medex-help-content::-webkit-scrollbar {
    width: 8px;
}

.medex-help-sidebar::-webkit-scrollbar-track,
.medex-help-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.medex-help-sidebar::-webkit-scrollbar-thumb,
.medex-help-content::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 4px;
}

.medex-help-sidebar::-webkit-scrollbar-thumb:hover,
.medex-help-content::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

@media (max-width: 768px) {
    .medex-help-sidebar {
        display: none;
    }

    .medex-help-content {
        padding: 20px;
    }

    .medex-help-section {
        display: block !important;
    }
}
</style>

<div class="medex-help-overlay" id="medexHelpOverlay" style="display: flex !important;" onclick="if(event.target === this) closeMedexHelp()">
    <div class="medex-help-modal">
        <div class="medex-help-header">
            <h2>
                <span>📘</span>
                <span>MedEx Help & Documentation</span>
            </h2>
            <p style="margin:0;padding:2px 0 4px;font-size:12px;font-style:italic;opacity:.65;text-align:center;">Let's use this to think like a user.</p>
            <button class="medex-help-close" onclick="closeMedexHelp()" title="Close (Esc)">×</button>
        </div>

        <div class="medex-help-body">
            <div class="medex-help-sidebar">
                <div class="medex-help-search">
                    <input type="text" id="helpSearch" placeholder="🔍 Search help..." onkeyup="searchHelp(this.value)">
                </div>
                <div class="medex-help-nav" id="helpNav">
                    <?php foreach ($sections as $index => $section): ?>
                        <div class="medex-help-nav-item <?php echo $index === 0 ? 'active' : ''; ?>"
                             onclick="showSection('<?php echo $section['id']; ?>')">
                            <?php echo htmlspecialchars($section['title']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="medex-help-content">
                <?php foreach ($sections as $index => $section): ?>
                    <div class="medex-help-section <?php echo $index === 0 ? 'active' : ''; ?>"
                         id="section-<?php echo $section['id']; ?>"
                         data-title="<?php echo htmlspecialchars(strtolower($section['title'])); ?>">
                        <h3><?php echo htmlspecialchars($section['title']); ?></h3>
                        <?php echo mdToHtml($section['content']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var T  = window.top;
    var TD = T.document;
    // Copy both style blocks into top document
    ['medexHelpStyle', 'medexHelpStyle2'].forEach(function(sid) {
        if (!TD.getElementById(sid)) {
            var src = document.getElementById(sid);
            if (!src) return;
            var ts = TD.createElement('style');
            ts.id = sid;
            ts.textContent = src.textContent;
            TD.head.appendChild(ts);
        }
    });
    // Functions on top window (onclick attrs execute in top context after adoption)
    T.showSection = function(sectionId) {
        TD.querySelectorAll('.medex-help-section').forEach(function(s){ s.classList.remove('active'); });
        TD.querySelectorAll('.medex-help-nav-item').forEach(function(i){ i.classList.remove('active'); });
        var section = TD.getElementById('section-' + sectionId);
        if (section) { section.classList.add('active'); section.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        if (event && event.target) event.target.classList.add('active');
    };
    T.searchHelp = function(query) {
        query = query.toLowerCase().trim();
        if (!query) {
            TD.querySelectorAll('.medex-help-section').forEach(function(s){ s.style.display = 'block'; });
            return;
        }
        TD.querySelectorAll('.medex-help-section').forEach(function(section) {
            var title = section.getAttribute('data-title');
            var content = section.textContent.toLowerCase();
            if ((title && title.includes(query)) || content.includes(query)) {
                section.style.display = 'block'; section.classList.add('active');
            } else {
                section.style.display = 'none'; section.classList.remove('active');
            }
        });
    };
    T.closeMedexHelp = function() {
        var overlay = TD.getElementById('medexHelpOverlay');
        var s1 = TD.getElementById('medexHelpStyle');
        var s2 = TD.getElementById('medexHelpStyle2');
        var logDiv = document.getElementById('install_upgrade_log');
        if (overlay) {
            overlay.style.animation = 'fadeOut 0.2s ease-out';
            setTimeout(function(){ overlay.remove(); if (s1) s1.remove(); if (s2) s2.remove(); if (logDiv) logDiv.style.display = 'none'; }, 200);
        }
    };
    // Adopt overlay into top document body
    var overlay = document.getElementById('medexHelpOverlay');
    if (overlay) TD.body.appendChild(TD.adoptNode(overlay));
    // Hide the install_upgrade_log div — action.js shows it on success; our leftover markup causes a 500px gap
    var logDiv = document.getElementById('install_upgrade_log');
    if (logDiv) logDiv.style.display = 'none';
    // Escape key and smooth scroll in top document
    TD.addEventListener('keydown', function kh(e){ if (e.key === 'Escape') { T.closeMedexHelp(); TD.removeEventListener('keydown', kh); } });
    var content = TD.querySelector('.medex-help-content');
    if (content) content.style.scrollBehavior = 'smooth';
})();
</script>

<style id="medexHelpStyle2">
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}
</style>
