<?php
/**
 * MedEx Pre-Install Help - What is MedEx?
 * Shown when user clicks Help before the module has been installed/configured.
 */
?>
<style id="mxPreStyle">
.mx-pre-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.72);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    animation: mxFadeIn .2s ease-out;
}
@keyframes mxFadeIn { from{opacity:0} to{opacity:1} }
@keyframes mxSlideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
@keyframes mxFadeOut { from{opacity:1} to{opacity:0} }

.mx-pre-modal {
    background: #fff;
    border-radius: 14px;
    max-width: 740px;
    width: 100%;
    box-shadow: 0 25px 60px rgba(0,0,0,.45);
    animation: mxSlideUp .28s ease-out;
    overflow: hidden;
}
.mx-pre-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 28px 32px 22px;
    color: #fff;
    display: flex; align-items: center; justify-content: space-between;
}
.mx-pre-header-left { display:flex; align-items:center; gap:14px; }
.mx-pre-logo-circle {
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
}
.mx-pre-header h2 { margin:0; font-size:22px; font-weight:700; }
.mx-pre-header p  { margin:4px 0 0; font-size:13px; opacity:.85; }
.mx-pre-close {
    background: rgba(255,255,255,.2); border: none; color: #fff;
    width: 34px; height: 34px; border-radius: 50%;
    font-size: 22px; line-height:1; cursor: pointer; transition: background .2s;
    display:flex; align-items:center; justify-content:center;
}
.mx-pre-close:hover { background: rgba(255,255,255,.35); }

.mx-pre-body { padding: 28px 32px 24px; }
.mx-pre-tagline {
    font-size: 15px; color: #495057; line-height: 1.65;
    margin: 0 0 24px;
}
.mx-pre-features {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 14px; margin-bottom: 28px;
}
.mx-feature-card {
    background: #f8f9ff; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 16px 18px;
    display: flex; align-items: flex-start; gap: 12px;
}
.mx-feature-icon {
    font-size: 22px; flex-shrink: 0; margin-top: 2px;
}
.mx-feature-title { font-weight: 600; font-size: 14px; color: #2d3748; margin: 0 0 4px; }
.mx-feature-desc  { font-size: 12px; color: #718096; margin: 0; line-height: 1.5; }

.mx-pre-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding-top: 16px; border-top: 1px solid #e9ecef;
}
.mx-pre-footer-note { font-size: 12px; color: #adb5bd; }
.mx-pre-actions { display: flex; gap: 10px; }
.mx-btn {
    padding: 10px 20px; border-radius: 8px; font-size: 14px;
    font-weight: 600; cursor: pointer; border: none; transition: all .2s; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.mx-btn-primary {
    background: linear-gradient(135deg,#667eea,#764ba2); color: #fff;
    box-shadow: 0 2px 8px rgba(102,126,234,.4);
}
.mx-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(102,126,234,.5); color:#fff; text-decoration:none; }
.mx-btn-secondary {
    background: #f1f3f5; color: #495057;
}
.mx-btn-secondary:hover { background: #e9ecef; }
</style>

<div class="mx-pre-overlay" id="mxPreOverlay" onclick="if(event.target===this)closeMxPre()">
    <div class="mx-pre-modal">
        <div class="mx-pre-header">
            <div class="mx-pre-header-left">
                <div class="mx-pre-logo-circle">💬</div>
                <div>
                    <h2>MedEx Communications Platform</h2>
                    <p>HIPAA-compliant patient engagement for OpenEMR</p>
                    <p style="margin:2px 0 0;font-size:11px;font-style:italic;opacity:.7;">Let's use this to think like a user.</p>
                </div>
            </div>
            <button class="mx-pre-close" onclick="closeMxPre()" title="Close">×</button>
        </div>

        <div class="mx-pre-body">
            <p class="mx-pre-tagline">
                MedEx adds a full suite of HIPAA-compliant communication tools directly inside OpenEMR —
                appointment reminders, secure patient communications, calendar modernization, calendar feeds,
                Re-scheduling assistants, PDF Filler and more.
                Getting started takes about 5 minutes.
            </p>

            <div class="mx-pre-features">
                <div class="mx-feature-card">
                    <div class="mx-feature-icon">📱</div>
                    <div>
                        <p class="mx-feature-title">Appointment Reminders</p>
                        <p class="mx-feature-desc">Automated Appointment Reminders by text/e-mail/voice, directly from your calendar. Reduces no-shows by up to 70%.</p>
                    </div>
                </div>
                <div class="mx-feature-card">
                    <div class="mx-feature-icon">🔒</div>
                    <div>
                        <p class="mx-feature-title">Secure Patient Chat</p>
                        <p class="mx-feature-desc">HIPAA-compliant messaging — send a secure link via text or email, no app required.</p>
                    </div>
                </div>
                <div class="mx-feature-card">
                    <div class="mx-feature-icon">📄</div>
                    <div>
                        <p class="mx-feature-title">PDF Management</p>
                        <p class="mx-feature-desc">Send and store intake forms and documents — all linked to patient records.</p>
                    </div>
                </div>
                <div class="mx-feature-card">
                    <div class="mx-feature-icon">🗓️</div>
                    <div>
                        <p class="mx-feature-title">Calendar AI &amp; Sync</p>
                        <p class="mx-feature-desc">AI-powered schedule templating, and secure provider calendar exports.</p>
                    </div>
                </div>
            </div>

            <div class="mx-pre-footer">
                <span class="mx-pre-footer-note">Pricing starts at $9.95/mo · No contracts</span>
                <div class="mx-pre-actions">
                    <button class="mx-btn mx-btn-secondary" onclick="closeMxPre()">Close</button>
                    <a href="https://medexbank.com/help/tutorial.html" target="_blank" class="mx-btn mx-btn-primary">
                        View Interactive Tutorial <span>↗</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var T  = window.top;
    var TD = T.document;
    // Copy styles into top document so they apply after adoption
    if (!TD.getElementById('mxPreStyle')) {
        var ts = TD.createElement('style');
        ts.id = 'mxPreStyle';
        ts.textContent = document.getElementById('mxPreStyle').textContent;
        TD.head.appendChild(ts);
    }
    // Define close on top window — onclick attrs execute in top context after adoption
    T.closeMxPre = function() {
        var o = TD.getElementById('mxPreOverlay');
        var s = TD.getElementById('mxPreStyle');
        var logDiv = document.getElementById('install_upgrade_log');
        if (o) { o.style.animation = 'mxFadeOut .2s ease-out'; setTimeout(function(){ o.remove(); if (s) s.remove(); if (logDiv) logDiv.style.display = 'none'; }, 190); }
    };
    // Adopt overlay into top document body (escapes iframe viewport constraint)
    var overlay = document.getElementById('mxPreOverlay');
    if (overlay) TD.body.appendChild(TD.adoptNode(overlay));
    // Hide the install_upgrade_log div — action.js shows it on success; our leftover markup causes a 500px gap
    var logDiv = document.getElementById('install_upgrade_log');
    if (logDiv) logDiv.style.display = 'none';
    // Escape key on top document
    TD.addEventListener('keydown', function kh(e){ if (e.key === 'Escape') { T.closeMxPre(); TD.removeEventListener('keydown', kh); } });
})();
</script>
