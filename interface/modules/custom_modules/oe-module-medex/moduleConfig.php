<?php
/**
 * Module Configuration File
 * Required by OpenEMR to register the gear icon in Module Manager.
 * When loaded in the configure iframe, injects a modal overlay into the
 * parent document that loads public/status.php (which is state-aware:
 * not-registered, disabled, online, offline).
 */

$module_config = 1;

if (basename($_SERVER['SCRIPT_FILENAME']) === 'moduleConfig.php') {
    if (empty($_GET['site'])) {
        $_GET['site'] = 'default';
    }

    require_once(__DIR__ . '/../../../globals.php');

    $statusUrl = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/public/status.php';
    ?><!DOCTYPE html><html><head></head><body>
    <script>
    (function() {
        try {
            var T  = window.top;
            var TD = T.document;

            // Hide the configure drawer that opened this iframe
            var p = window.parent;
            if (p && p.document) {
                p.document.querySelectorAll('tr[id^="ConfigRow_"]').forEach(function(r) {
                    r.style.display = 'none';
                });
            }

            // Inject modal CSS into top document
            if (!TD.getElementById('medexConfigStyle')) {
                var style = TD.createElement('style');
                style.id = 'medexConfigStyle';
                style.textContent = [
                    '.medex-modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.5); backdrop-filter:blur(5px); -webkit-backdrop-filter:blur(5px); display:flex; align-items:center; justify-content:center; z-index:9999; animation:medexFadeIn .2s ease-out; }',
                    '.medex-modal-content { background:#fff; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,.3); max-width:700px; max-height:95vh; width:90%; overflow:auto; position:relative; animation:medexSlideUp .3s ease-out; }',
                    '.medex-modal-close { position:absolute; top:15px; right:15px; background:#f8f9fa; border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:24px; line-height:1; color:#666; transition:all .2s; z-index:10; }',
                    '.medex-modal-close:hover { background:#e9ecef; color:#333; transform:rotate(90deg); }',
                    '.medex-modal-iframe { width:100%; height:70vh; min-height:500px; border:none; border-radius:12px; display:block; }',
                    '@keyframes medexFadeIn { from{opacity:0} to{opacity:1} }',
                    '@keyframes medexSlideUp { from{transform:translateY(30px);opacity:0} to{transform:translateY(0);opacity:1} }',
                    '@keyframes medexFadeOut { from{opacity:1} to{opacity:0} }'
                ].join('\n');
                TD.head.appendChild(style);
            }

            // Remove any existing modal first
            var existing = TD.getElementById('medexStatusModal');
            if (existing) existing.remove();

            // Create modal
            var modal = TD.createElement('div');
            modal.className = 'medex-modal-overlay';
            modal.id = 'medexStatusModal';
            modal.innerHTML = '<div class="medex-modal-content">' +
                '<button class="medex-modal-close" title="Close">\u00d7</button>' +
                '<iframe class="medex-modal-iframe" src="<?php echo addslashes($statusUrl); ?>"></iframe>' +
                '</div>';

            // Close handlers
            var closeModal = function() {
                modal.style.animation = 'medexFadeOut .2s ease-out';
                setTimeout(function() { modal.remove(); }, 190);
            };
            modal.querySelector('.medex-modal-close').addEventListener('click', closeModal);
            modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
            var escHandler = function(e) { if (e.key === 'Escape') { closeModal(); TD.removeEventListener('keydown', escHandler); } };
            TD.addEventListener('keydown', escHandler);

            TD.body.appendChild(modal);
        } catch(e) { console.error('MedEx gear config:', e); }
    })();
    </script>
    </body></html><?php
    exit;
}
