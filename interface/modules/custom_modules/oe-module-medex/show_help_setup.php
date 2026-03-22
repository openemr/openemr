<?php
/**
 * MedEx Setup Help — shown after Install but before Enable.
 * Content mirrors help.php which is the canonical setup guide.
 * $modId is in scope (passed from ModuleManagerListener::help_requested).
 */
$setupModId = (int)($modId ?? 0);
?>
<style id="mxSetupStyle">
.mx-setup-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.72); backdrop-filter: blur(4px);
    z-index: 99999; display: flex; align-items: center; justify-content: center;
    padding: 20px; animation: mxsFadeIn .2s ease-out;
}
@keyframes mxsFadeIn  { from{opacity:0} to{opacity:1} }
@keyframes mxsSlideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
@keyframes mxsFadeOut { from{opacity:1} to{opacity:0} }

.mx-setup-modal {
    background: #fff; border-radius: 14px;
    max-width: 640px; width: 100%;
    box-shadow: 0 25px 60px rgba(0,0,0,.45);
    animation: mxsSlideUp .28s ease-out; overflow: hidden;
}
.mx-setup-header {
    background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    padding: 22px 28px; color: #fff;
    display: flex; align-items: center; justify-content: space-between;
}
.mx-setup-header h2 { margin:0; font-size:20px; font-weight:700; display:flex; align-items:center; gap:10px; }
.mx-setup-close {
    background: rgba(255,255,255,.2); border: none; color:#fff;
    width:32px; height:32px; border-radius:50%;
    font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center;
    transition: background .2s;
}
.mx-setup-close:hover { background:rgba(255,255,255,.35); }

.mx-setup-body { padding: 28px; }
.mx-setup-intro { font-size:14px; color:#6c757d; margin:0 0 26px; line-height:1.6; }

.mx-step {
    display: flex; gap: 18px; margin-bottom: 22px;
    padding-bottom: 22px; border-bottom: 1px solid #f0f0f0;
}
.mx-step:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

.mx-step-num {
    flex-shrink: 0;
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg,#667eea,#764ba2);
    color: #fff; font-size: 18px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 8px rgba(102,126,234,.35);
}
.mx-step-content {}
.mx-step-title {
    font-size: 15px; font-weight: 700; color: #2d3748;
    margin: 0 0 6px; display:flex; align-items:center; gap:8px;
}
.mx-step-desc { font-size: 13px; color: #718096; margin: 0; line-height: 1.6; }
.mx-step-action { margin-top: 10px; }

.mx-sbtn {
    padding: 8px 16px; border-radius: 7px; font-size: 13px;
    font-weight: 600; cursor: pointer; border: none;
    transition: all .2s; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.mx-sbtn-primary {
    background: linear-gradient(135deg,#667eea,#764ba2); color: #fff;
    box-shadow: 0 2px 6px rgba(102,126,234,.4);
}
.mx-sbtn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(102,126,234,.45); color:#fff; text-decoration:none; }
.mx-sbtn-gear {
    background: #fff3cd; color: #856404; border: 1px solid #ffc107;
}
.mx-sbtn-gear:hover { background: #ffe69c; transform:translateY(-1px); }

.mx-setup-footer {
    padding: 14px 28px 20px;
    display: flex; justify-content: space-between; align-items: center;
    border-top: 1px solid #f0f0f0;
}
.mx-setup-footer-note { font-size: 12px; color: #adb5bd; }
.mx-sbtn-close {
    background: #f1f3f5; color: #495057;
}
.mx-sbtn-close:hover { background: #e9ecef; }
</style>

<div class="mx-setup-overlay" id="mxSetupOverlay" onclick="if(event.target===this)closeMxSetup()">
    <div class="mx-setup-modal">

        <div class="mx-setup-header">
            <h2>⚙️ MedEx — 3-Step Setup</h2>
            <button class="mx-setup-close" onclick="closeMxSetup()" title="Close">×</button>
        </div>
        <p style="margin:0;padding:4px 24px 0;font-size:12px;font-style:italic;opacity:.65;text-align:center;">Let's use this to think like a user.</p>

        <div class="mx-setup-body">
            <p class="mx-setup-intro">
                To begin using the MedEx Communication Platform, complete these three steps.
            </p>

            <!-- Step 1 -->
            <div class="mx-step">
                <div class="mx-step-num">1</div>
                <div class="mx-step-content">
                    <p class="mx-step-title"><i class="fa fa-toggle-on"></i> Enable the module first</p>
                    <p class="mx-step-desc">
                        The module is not yet enabled. You need to <strong>Enable</strong> it in the
                        Module Manager before proceeding — then click the gear icon to configure.
                    </p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="mx-step">
                <div class="mx-step-num">2</div>
                <div class="mx-step-content">
                    <p class="mx-step-title"><i class="fa fa-cogs"></i> Configuration</p>
                    <p class="mx-step-desc">
                        Once enabled, click the <i class="fa fa-gear"></i> <strong>Gear Icon</strong>
                        next to the module in the Module Manager to access the MedEx Dashboard.
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="mx-step">
                <div class="mx-step-num">3</div>
                <div class="mx-step-content">
                    <p class="mx-step-title"><i class="fa fa-user-plus"></i> Registration</p>
                    <p class="mx-step-desc">
                        If you are a new customer, you will be guided through an onboarding wizard
                        to create your account and select your communication services.
                    </p>
                </div>
            </div>
        </div>

        <div class="mx-setup-footer">
            <a href="https://api.hipaabank.net/help/tutorial.html" target="_blank" style="font-size:12px;color:#667eea;text-decoration:none;">
                <i class="fa fa-book"></i> MedEx Help &amp; Docs ↗
            </a>
            <button class="mx-sbtn mx-sbtn-close" onclick="closeMxSetup()">Close</button>
        </div>
    </div>
</div>

<script>
(function() {
    var T  = window.top;
    var TD = T.document;
    // Copy styles into top document
    if (!TD.getElementById('mxSetupStyle')) {
        var ts = TD.createElement('style');
        ts.id = 'mxSetupStyle';
        ts.textContent = document.getElementById('mxSetupStyle').textContent;
        TD.head.appendChild(ts);
    }
    // Close function on top window
    T.closeMxSetup = function() {
        var o = TD.getElementById('mxSetupOverlay');
        var s = TD.getElementById('mxSetupStyle');
        var logDiv = document.getElementById('install_upgrade_log');
        if (o) { o.style.animation = 'mxsFadeOut .2s ease-out'; setTimeout(function(){ o.remove(); if (s) s.remove(); if (logDiv) logDiv.style.display = 'none'; }, 190); }
    };
    // Gear click — mirrors help.php closeAndOpenStatus() but targets top.document
    // (the modal is now adopted into top.document.body, not in a popup)
    T.mxClickGear = function() {
        T.closeMxSetup();
        setTimeout(function() {
            var configLinks = TD.querySelectorAll('a[onclick*="configure"]');
            for (var i = 0; i < configLinks.length; i++) {
                var row = configLinks[i].closest('tr');
                if (row && row.textContent.indexOf('MedEx Communication Manager') !== -1) {
                    configLinks[i].click();
                    return;
                }
            }
            // Fallback: nothing found (shouldn't happen), do nothing
        }, 220); // slight delay so fade-out completes first
    };
    // Adopt overlay into top document body
    var overlay = document.getElementById('mxSetupOverlay');
    if (overlay) TD.body.appendChild(TD.adoptNode(overlay));
    // Hide the install_upgrade_log div — action.js shows it on success; our leftover markup causes a 500px gap
    var logDiv = document.getElementById('install_upgrade_log');
    if (logDiv) logDiv.style.display = 'none';
    // Escape key on top document
    TD.addEventListener('keydown', function kh(e){ if (e.key === 'Escape') { T.closeMxSetup(); TD.removeEventListener('keydown', kh); } });
})();
</script>
