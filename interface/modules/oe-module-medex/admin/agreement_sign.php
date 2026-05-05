<?php
/**
 * MedEx Agreement Signature Viewer
 *
 * Renders Terms/BAA with required electronic signature capture for onboarding.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\MedExConfig;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "<html><body>" . xlt('Access denied') . "</body></html>";
    exit;
}

$type = strtolower(trim((string)($_GET['type'] ?? 'terms')));
if (!in_array($type, ['terms', 'baa'], true)) {
    $type = 'terms';
}

$version = trim((string)($_GET['version'] ?? ($type === 'terms' ? MedExConfig::TERMS_VERSION : MedExConfig::BAA_VERSION)));
$title = ($type === 'terms') ? xlt('MedEx Terms and Conditions') : xlt('MedEx Business Associate Agreement (BAA)');
$displayUrl = ($type === 'terms') ? MedExConfig::termsUrl() : MedExConfig::baaUrl();
$informationId = ($type === 'terms') ? 5 : 8;
// Use server-side base URL for content fetch (k8s-safe and cluster-safe).
$fetchBaseUrl = rtrim(MedExConfig::baseUrl(), '/');
$bodyUrl = $fetchBaseUrl . '/index.php?route=information/information/agree&information_id=' . $informationId;
function medexGetPracticeName(): string
{
    $facility = sqlQuery("SELECT name FROM facility WHERE primary_business_entity = 1 ORDER BY id LIMIT 1");
    if (!$facility) {
        $facility = sqlQuery("SELECT name FROM facility ORDER BY id LIMIT 1");
    }
    $name = trim((string)($facility['name'] ?? ''));
    if ($name !== '') {
        return $name;
    }
    return trim((string)($GLOBALS['openemr_name'] ?? 'OpenEMR Practice'));
}

function medexRemoveLegacyPrintInstruction(string $html): string
{
    $patterns = [
        '/Print a copy\s*Sign and return a copy to MedEx\s*support@medexbank\.com\.?/i',
        '/Print a copy\.?/i',
        '/Sign and return a copy to MedEx\s*support@medexbank\.com\.?/i',
    ];
    foreach ($patterns as $pattern) {
        $html = preg_replace($pattern, '', $html) ?? $html;
    }
    return $html;
}

function medexFetchAgreementBody(string $url): string
{
    $raw = '';

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_USERAGENT => 'MedEx-Onboarding/1.0',
            ]);
            $body = curl_exec($ch);
            $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (is_string($body) && $http >= 200 && $http < 300) {
                $raw = $body;
            }
        }
    }

    if (trim($raw) === '' && ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'header' => "User-Agent: MedEx-Onboarding/1.0\r\n",
            ]
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if (is_string($body)) {
            $raw = $body;
        }
    }

    return (trim($raw) === '') ? '' : $raw;
}

$agreementHtml = medexFetchAgreementBody($bodyUrl);
$agreementHtml = medexRemoveLegacyPrintInstruction($agreementHtml);
$practiceName = medexGetPracticeName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo text($title); ?></title>
    <?php Header::setupHeader(['fontawesome']); ?>
    <style>
        :root {
            --brand: #0f4b8f;
            --line: #dbeafe;
            --muted: #64748b;
        }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            background: #f8fafc;
        }
        .page {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .head {
            border-bottom: 1px solid var(--line);
            background: #fff;
            padding: 14px 18px;
        }
        .title {
            font-size: 20px;
            font-weight: 800;
            color: var(--brand);
            margin: 0 0 4px;
        }
        .sub {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
        }
        .body {
            flex: 1 1 auto;
            overflow-y: auto;
            background: #fff;
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
            line-height: 1.55;
        }
        .legal-paper {
            background-color: #f4e8cf;
            background-image:
                radial-gradient(circle at 18% 22%, rgba(110, 82, 35, 0.07) 0, rgba(110, 82, 35, 0) 30%),
                radial-gradient(circle at 80% 74%, rgba(110, 82, 35, 0.06) 0, rgba(110, 82, 35, 0) 28%),
                repeating-linear-gradient(
                    0deg,
                    rgba(84, 61, 24, 0.025) 0px,
                    rgba(84, 61, 24, 0.025) 1px,
                    rgba(244, 232, 207, 0) 2px,
                    rgba(244, 232, 207, 0) 6px
                );
            border: 1px solid #b99f6a;
            border-radius: 6px;
            padding: 14px 16px;
            min-height: 100%;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            line-height: 1.6;
            color: #1f1a12;
        }
        .legal-paper h2,
        .legal-paper h3,
        .legal-paper h4 {
            font-family: "Times New Roman", Times, serif;
            color: #1b1510;
        }
        .fallback {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 12px;
        }
        .sign {
            background: #fff;
            padding: 14px 18px 18px;
            display: grid;
            gap: 10px;
            border-top: 1px solid var(--line);
        }
        .practice {
            border: 1px solid #dbeafe;
            background: #eff6ff;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            color: #1e3a8a;
        }
        .practice strong {
            color: #0f172a;
        }
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }
        .input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }
        .note {
            font-size: 12px;
            color: var(--muted);
        }
        .readiness {
            font-size: 12px;
            color: #475569;
            font-weight: 700;
        }
        .readiness.ok {
            color: #15803d;
            font-weight: 600;
        }
        .error {
            color: #b91c1c;
            font-size: 12px;
            display: none;
        }
        .ok {
            color: #15803d;
            font-size: 12px;
            display: none;
            font-weight: 700;
        }
        .btn {
            background: var(--brand);
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
            width: fit-content;
        }
        .btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }
        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
        }
        .btn-secondary {
            min-width: 92px;
            height: 40px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f4b8f;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-secondary:disabled {
            color: #94a3b8;
            border-color: #cbd5e1;
            background: #f1f5f9;
            cursor: not-allowed;
        }
        @media (max-width: 760px) {
            .row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="head">
            <p class="title"><?php echo text($title); ?></p>
            <p class="sub"><?php echo xlt('Version'); ?> <?php echo text($version); ?> • <?php echo xlt('Review and complete your electronic signature to continue onboarding.'); ?></p>
        </div>
        <div class="body">
            <div class="legal-paper">
                <?php if ($agreementHtml !== ''): ?>
                    <?php echo $agreementHtml; ?>
                <?php else: ?>
                    <div class="fallback">
                        <?php echo xlt('Unable to load agreement text in embedded view right now.'); ?>
                    </div>
                <?php endif; ?>
                <div id="agreement-end-marker" style="height:1px;"></div>
            </div>
        </div>
        <div class="sign">
            <div class="practice">
                <?php echo xlt('Company'); ?>:
                <strong id="practice_name_display"><?php echo text($practiceName); ?></strong>
            </div>
            <div class="row">
                <input type="text" id="legal_corporate_name" class="input" placeholder="<?php echo xla('Legal Corporate Name (required)'); ?>" value="<?php echo attr($practiceName); ?>" disabled>
                <input type="text" id="signer_name" class="input" placeholder="<?php echo xla('Legal Name (required)'); ?>" disabled>
                <input type="text" id="signer_title" class="input" placeholder="<?php echo xla('Title/Role (optional)'); ?>" disabled>
            </div>
            <label class="note">
                <input type="checkbox" id="attest" disabled> <?php echo xlt('I am authorized to sign this agreement on behalf of my practice and agree to these terms electronically.'); ?>
            </label>
            <div id="sign_error" class="error"><?php echo xlt('Enter your legal corporate name, legal signer name, and confirm authorization to sign.'); ?></div>
            <div id="sign_ok" class="ok"><?php echo xlt('Electronic signature recorded.'); ?></div>
            <div class="actions">
                <button type="button" id="sign_btn" class="btn" disabled><?php echo xlt('Sign'); ?></button>
                <button type="button" id="print_btn" class="btn-secondary" title="<?php echo xla('Print or download signed agreement'); ?>" disabled>
                    <i class="fa fa-print" aria-hidden="true"></i> <?php echo xlt('Print'); ?>
                </button>
            </div>
            <div id="readiness_state" class="readiness"><?php echo xlt('Scroll through the full agreement and complete required fields to enable signing.'); ?></div>
        </div>
    </div>

    <script>
        (function () {
            const signBtn = document.getElementById("sign_btn");
            const printBtn = document.getElementById("print_btn");
            const legalCorporateNameEl = document.getElementById("legal_corporate_name");
            const signerNameEl = document.getElementById("signer_name");
            const signerTitleEl = document.getElementById("signer_title");
            const attestEl = document.getElementById("attest");
            const errorEl = document.getElementById("sign_error");
            const okEl = document.getElementById("sign_ok");
            const readinessEl = document.getElementById("readiness_state");
            const bodyEl = document.querySelector(".body");
            const endMarkerEl = document.getElementById("agreement-end-marker");
            const practiceName = <?php echo json_encode($practiceName); ?>;
            const agreementTitle = <?php echo json_encode($title); ?>;
            const agreementVersion = <?php echo json_encode($version); ?>;
            const agreementType = <?php echo json_encode($type); ?>;
            const pdfFileBase = agreementType === "terms" ? "MedEX_Terms" : "MedEX_BAA";
            const signedLabel = <?php echo json_encode(xl('Signed')); ?>;
            let signedPayload = null;
            let agreementRead = false;
            let endObserver = null;

            function setError(show) {
                errorEl.style.display = show ? "block" : "none";
            }

            function setOk(show) {
                okEl.style.display = show ? "block" : "none";
            }

            function hasRequiredInputs() {
                const legalCorporateName = (legalCorporateNameEl.value || "").trim();
                const signerName = (signerNameEl.value || "").trim();
                return !!legalCorporateName && !!signerName && !!attestEl.checked;
            }

            function setFieldsEnabled(enabled) {
                legalCorporateNameEl.disabled = !enabled;
                signerNameEl.disabled = !enabled;
                signerTitleEl.disabled = !enabled;
                attestEl.disabled = !enabled;
            }

            function updateSignEnabledState() {
                const canSign = agreementRead && hasRequiredInputs() && !signedPayload;
                signBtn.disabled = !canSign;
                if (signedPayload) {
                    setFieldsEnabled(false);
                    readinessEl.textContent = "";
                    readinessEl.style.display = "none";
                    readinessEl.classList.remove("ok");
                    return;
                }
                setFieldsEnabled(agreementRead);
                if (agreementRead) {
                    readinessEl.textContent = "";
                    readinessEl.style.display = "none";
                    readinessEl.classList.remove("ok");
                    return;
                }
                readinessEl.style.display = "block";
                readinessEl.textContent = "Scroll through the full agreement and complete required fields to enable signing.";
                readinessEl.classList.remove("ok");
            }

            function evaluateAgreementRead() {
                if (!bodyEl) {
                    agreementRead = true;
                    updateSignEnabledState();
                    return;
                }
                if (agreementRead) {
                    updateSignEnabledState();
                    return;
                }
                const scrollHeight = Math.max(0, bodyEl.scrollHeight || 0);
                const clientHeight = Math.max(0, bodyEl.clientHeight || 0);
                const scrollTop = Math.max(0, bodyEl.scrollTop || 0);
                const remaining = scrollHeight - (scrollTop + clientHeight);
                if ((scrollHeight - clientHeight) <= 2) {
                    agreementRead = true;
                } else if (remaining <= 6) {
                    agreementRead = true;
                }
                updateSignEnabledState();
            }

            function markAgreementRead() {
                if (agreementRead) {
                    return;
                }
                agreementRead = true;
                if (endObserver) {
                    endObserver.disconnect();
                    endObserver = null;
                }
                updateSignEnabledState();
            }

            function formatSignedAt(iso) {
                try {
                    const d = new Date(iso);
                    if (isNaN(d.getTime())) {
                        return iso;
                    }
                    return d.toLocaleString();
                } catch (e) {
                    return iso;
                }
            }

            function getAgreementBodyHtml() {
                return bodyEl ? bodyEl.innerHTML : "";
            }

            function openPrintableDocument(payload) {
                const signedAtHuman = formatSignedAt(payload.signed_at);
                const companyName = (payload.legal_corporate_name || payload.practice_name || "");
                const printableHtml = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>${pdfFileBase}.pdf</title>
  <style>
    body{font-family:Segoe UI,Arial,sans-serif;color:#0f172a;margin:24px;line-height:1.5;}
    h1{margin:0 0 8px;color:#0f4b8f;font-size:26px;}
    .meta{margin:0 0 16px;padding:12px;border:1px solid #cbd5e1;border-radius:8px;background:#f8fafc;}
    .meta-row{margin:2px 0;}
    .meta-label{display:inline-block;min-width:170px;color:#475569;}
    .agreement{margin-top:14px;}
  </style>
</head>
<body>
  <h1>${agreementTitle}</h1>
  <div class="meta">
    <div class="meta-row"><span class="meta-label">Company:</span>${companyName}</div>
    <div class="meta-row"><span class="meta-label">Agreement Version:</span>${agreementVersion}</div>
    <div class="meta-row"><span class="meta-label">Signer Name:</span>${payload.signer_name || ""}</div>
    <div class="meta-row"><span class="meta-label">Signer Title:</span>${payload.signer_title || ""}</div>
    <div class="meta-row"><span class="meta-label">Signed At:</span>${signedAtHuman}</div>
  </div>
  <div class="agreement">${getAgreementBodyHtml()}</div>
</body>
</html>`;
                const w = window.open("", "_blank", "noopener,noreferrer");
                if (w) {
                    w.document.open();
                    w.document.write(printableHtml);
                    w.document.close();
                    w.document.title = `${pdfFileBase}.pdf`;
                    w.focus();
                    w.print();
                    return;
                }

                // Popup may be blocked from embedded contexts; print via hidden iframe fallback.
                const printFrame = document.createElement("iframe");
                printFrame.style.position = "fixed";
                printFrame.style.right = "0";
                printFrame.style.bottom = "0";
                printFrame.style.width = "0";
                printFrame.style.height = "0";
                printFrame.style.border = "0";
                document.body.appendChild(printFrame);

                const frameDoc = printFrame.contentWindow?.document;
                if (!frameDoc || !printFrame.contentWindow) {
                    document.body.removeChild(printFrame);
                    return;
                }
                frameDoc.open();
                frameDoc.write(printableHtml);
                frameDoc.close();

                setTimeout(function () {
                    try {
                        printFrame.contentWindow.focus();
                        printFrame.contentWindow.print();
                    } finally {
                        setTimeout(function () {
                            if (printFrame.parentNode) {
                                printFrame.parentNode.removeChild(printFrame);
                            }
                        }, 1000);
                    }
                }, 120);
            }

            signBtn.addEventListener("click", function () {
                const signerName = (signerNameEl.value || "").trim();
                const legalCorporateName = (legalCorporateNameEl.value || "").trim();
                const signerTitle = (signerTitleEl.value || "").trim();
                const attest = !!attestEl.checked;
                if (!legalCorporateName || !signerName || !attest) {
                    setOk(false);
                    setError(true);
                    return;
                }
                if (!agreementRead) {
                    setOk(false);
                    setError(true);
                    return;
                }
                setError(false);
                setOk(true);
                signBtn.disabled = true;
                signBtn.textContent = signedLabel;
                printBtn.disabled = false;
                legalCorporateNameEl.readOnly = true;
                signerNameEl.readOnly = true;
                signerTitleEl.readOnly = true;
                attestEl.disabled = true;

                const payload = {
                    source: "medex-agreement-signer",
                    action: "signed",
                    type: agreementType,
                    practice_name: practiceName,
                    legal_corporate_name: legalCorporateName,
                    signer_name: signerName,
                    signer_title: signerTitle,
                    signed_at: new Date().toISOString()
                };
                signedPayload = payload;

                if (window.parent && window.parent !== window) {
                    window.parent.postMessage(payload, "*");
                }
            });

            printBtn.addEventListener("click", function () {
                if (!signedPayload) {
                    return;
                }
                openPrintableDocument(signedPayload);
            });

            if (bodyEl) {
                bodyEl.addEventListener("scroll", evaluateAgreementRead);
            }
            if (bodyEl && endMarkerEl && typeof IntersectionObserver !== "undefined") {
                endObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.target === endMarkerEl && entry.isIntersecting) {
                            markAgreementRead();
                        }
                    });
                }, {
                    root: bodyEl,
                    threshold: 1.0
                });
                endObserver.observe(endMarkerEl);
            }
            legalCorporateNameEl.addEventListener("input", updateSignEnabledState);
            signerNameEl.addEventListener("input", updateSignEnabledState);
            attestEl.addEventListener("change", updateSignEnabledState);
            evaluateAgreementRead();
            setTimeout(evaluateAgreementRead, 250);
            setTimeout(evaluateAgreementRead, 800);
        })();
    </script>
</body>
</html>
