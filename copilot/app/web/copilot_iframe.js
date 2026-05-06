// app/web/copilot_iframe.js
(() => {
  const params = new URLSearchParams(window.location.search);
  const PATIENT_ID = params.get("patient_id");
  const PHYSICIAN = params.get("physician_user_id") || "admin";

  if (window.pdfjsLib) {
    window.pdfjsLib.GlobalWorkerOptions.workerSrc =
      "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
  }

  // Session bootstrap — minted lazily on the first chat submit OR eagerly
  // on iframe load (for the pending-intake banner). Both call sites await
  // the same in-flight promise so the eager load-time request and the
  // first chat submit don't race and end up minting two sessions
  // (W2 KR5 round-5 codex P2 fix).
  let sessionId = null;
  let sessionInflight = null;

  async function ensureSession() {
    if (sessionId !== null) return sessionId;
    if (sessionInflight !== null) return sessionInflight;
    sessionInflight = (async () => {
      const r = await fetch("/v1/sessions", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          patient_id: PATIENT_ID,
          physician_user_id: PHYSICIAN,
        }),
      });
      if (!r.ok) {
        const text = await r.text();
        throw new Error(`session_bootstrap_failed:${r.status}:${text}`);
      }
      const data = await r.json();
      sessionId = data.session_id;
      // W2 KR5: kick the pending-intakes fetch on first session bootstrap.
      // Fire-and-forget — banner pops in async; chat works without waiting.
      refreshPendingIntakes(sessionId).catch(() => {});
      return sessionId;
    })();
    try {
      return await sessionInflight;
    } finally {
      sessionInflight = null;
    }
  }

  // ───────────── W2 KR5: pending-intake banner ─────────────

  // Per-session in-memory dismissal set. Persistent ack is Final-scope.
  const acknowledgedDocIds = new Set();

  async function refreshPendingIntakes(sid) {
    const banner = document.getElementById("pending-intakes-banner");
    const list = document.getElementById("pending-intakes-list");
    const countEl = document.getElementById("pending-intakes-count");
    const toggle = document.getElementById("pending-intakes-toggle");
    if (!banner || !list || !countEl || !toggle) return;

    let body;
    try {
      const r = await fetch(`/v1/sessions/${encodeURIComponent(sid)}/pending_intakes`);
      if (!r.ok) return;  // Silent — observability surface, not blocking.
      body = await r.json();
    } catch {
      return;
    }
    const visible = (body.items || []).filter((it) => !acknowledgedDocIds.has(it.doc_id));
    if (visible.length === 0) {
      banner.hidden = true;
      return;
    }
    banner.hidden = false;
    countEl.textContent = String(visible.length);
    list.replaceChildren();
    for (const item of visible) {
      const li = document.createElement("li");
      li.textContent = `${item.doc_type} — ${item.doc_id} (${item.uploaded_at.slice(0, 10)})`;
      li.dataset.docId = item.doc_id;
      li.onclick = () => onPendingIntakeClick(item, li);
      list.appendChild(li);
    }
    toggle.onclick = () => {
      const expanded = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", expanded ? "false" : "true");
      list.hidden = expanded;
    };
  }

  function onPendingIntakeClick(item, listItemEl) {
    // Open the existing bbox modal pointed at this doc's parent record.
    // The user can then click into specific fields if extractions exist.
    const recordId = `DocumentReference/${item.doc_id}`;
    openBboxModal(recordId);
    // In-memory dismiss: mark this doc acknowledged so the banner shrinks.
    acknowledgedDocIds.add(item.doc_id);
    listItemEl.classList.add("acknowledged");
    listItemEl.onclick = null;
    // Re-derive the count locally (avoid round-trip).
    const remaining = document.querySelectorAll(
      "#pending-intakes-list li:not(.acknowledged)"
    ).length;
    const countEl = document.getElementById("pending-intakes-count");
    const banner = document.getElementById("pending-intakes-banner");
    if (countEl) countEl.textContent = String(remaining);
    if (banner && remaining === 0) banner.hidden = true;
  }
  document.getElementById("patient-banner").textContent =
    PATIENT_ID ? `Patient: ${PATIENT_ID}` : "(no patient context)";

  // W2 KR5: bootstrap session eagerly on load so the pending-intake banner
  // can appear without waiting for the first chat submit. Failures are
  // tolerated — chat still works lazily on the user's first message.
  if (PATIENT_ID) {
    ensureSession().catch(() => {});
  }

  const dropZone = document.getElementById("drop-zone");
  const fileInput = document.getElementById("file-input");
  const paperclip = document.getElementById("paperclip");
  const conversation = document.getElementById("conversation");
  const form = document.getElementById("chat-form");
  const questionInput = document.getElementById("question-input");
  const modal = document.getElementById("bbox-modal");
  const modalCanvas = document.getElementById("bbox-modal-canvas");
  const modalLabel = document.getElementById("bbox-source-label");
  const modalClose = document.getElementById("bbox-modal-close");

  modalClose.onclick = () => modal.close();
  modal.addEventListener("cancel", (e) => { e.preventDefault(); modal.close(); });

  paperclip.onclick = () => fileInput.click();
  fileInput.onchange = () => {
    if (fileInput.files.length) uploadFile(fileInput.files[0]);
    fileInput.value = "";
  };

  ["dragenter", "dragover"].forEach((t) =>
    dropZone.addEventListener(t, (e) => {
      e.preventDefault();
      dropZone.dataset.state = "dragover";
    })
  );
  ["dragleave", "drop"].forEach((t) =>
    dropZone.addEventListener(t, (e) => {
      e.preventDefault();
      if (t !== "drop") dropZone.dataset.state = "idle";
    })
  );
  dropZone.addEventListener("drop", (e) => {
    if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
  });

  async function uploadFile(file) {
    if (!PATIENT_ID) {
      appendMessage("system", "No patient_id in iframe URL — cannot upload.");
      return;
    }
    dropZone.dataset.state = "uploading";
    appendMessage("system", `Uploading ${file.name}…`);
    const docType =
      file.name.toLowerCase().includes("intake") ? "intake_form_doc" : "lab_doc";
    const fd = new FormData();
    fd.append("file", file);
    fd.append("patient_id", PATIENT_ID);
    fd.append("doc_type", docType);
    fd.append("mime_type", file.type || "application/pdf");
    fd.append("physician_user_id", PHYSICIAN);
    const r = await fetch("/v1/documents/attach", { method: "POST", body: fd });
    dropZone.dataset.state = "idle";
    if (!r.ok) {
      appendMessage("system", `Upload failed: ${r.status} ${await r.text()}`);
      return;
    }
    const data = await r.json();
    const dedup = data.was_dedup_hit ? " (deduped — already extracted)" : "";
    appendMessage(
      "system",
      `Extracted ${data.bbox_overlay.length} fact(s) from ${file.name}${dedup}.`
    );
    // Cache the overlay so citation chips can open the modal without a fetch.
    overlayCache[data.doc_id] = { overlay: data.bbox_overlay };
  }

  const overlayCache = {};

  form.onsubmit = async (e) => {
    e.preventDefault();
    const q = questionInput.value.trim();
    if (!q) return;
    appendMessage("user", q);
    questionInput.value = "";

    // Bootstrap (or reuse) the session before the first chat call.
    let sid;
    try {
      sid = await ensureSession();
    } catch (err) {
      appendMessage("system", `Session error: ${err.message}`);
      return;
    }

    const r = await fetch("/v1/chat", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        session_id: sid,
        question: q,
      }),
    });
    if (!r.ok) {
      appendMessage("system", `Chat error: ${r.status}`);
      return;
    }
    const data = await r.json();
    appendMessageWithCitations(data.response?.prose ?? data.prose ?? "", data.response?.claims ?? data.claims ?? []);
  };

  function appendMessage(role, text) {
    const li = document.createElement("li");
    li.className = role;
    li.textContent = text;
    conversation.appendChild(li);
    li.scrollIntoView({ block: "end" });
  }

  function appendMessageWithCitations(prose, claims) {
    const li = document.createElement("li");
    li.className = "assistant";
    li.textContent = prose + " ";
    for (const c of claims) {
      const chip = document.createElement("span");
      chip.className = "citation-chip";
      chip.textContent = c.display || c.record_id;
      chip.dataset.recordId = c.record_id;
      chip.onclick = () => openBboxModal(c.record_id);
      li.appendChild(chip);
    }
    conversation.appendChild(li);
    li.scrollIntoView({ block: "end" });
  }

  function drawBboxOverlay(ctx, bbox, width, height) {
    if (bbox.length !== 4 || bbox.some(Number.isNaN)) return;
    const [x, y, w, h] = bbox;
    ctx.strokeStyle = "rgba(220, 60, 60, 0.9)";
    ctx.lineWidth = 3;
    ctx.strokeRect(x * width, y * height, w * width, h * height);
  }

  function drawTextFallback(ctx, message) {
    ctx.fillStyle = "#fafafa";
    ctx.fillRect(0, 0, modalCanvas.width, modalCanvas.height);
    ctx.fillStyle = "#222";
    ctx.font = "14px sans-serif";
    ctx.fillText(message, 20, 30);
  }

  async function renderImagePreview(blob, page, bbox) {
    const url = URL.createObjectURL(blob);
    try {
      const img = await new Promise((resolve, reject) => {
        const el = new Image();
        el.onload = () => resolve(el);
        el.onerror = () => reject(new Error("image_load_failed"));
        el.src = url;
      });
      modalCanvas.width = img.naturalWidth;
      modalCanvas.height = img.naturalHeight;
      const ctx = modalCanvas.getContext("2d");
      ctx.drawImage(img, 0, 0);
      drawBboxOverlay(ctx, bbox, modalCanvas.width, modalCanvas.height);
    } finally {
      URL.revokeObjectURL(url);
    }
  }

  async function renderPdfPreview(arrayBuffer, page, bbox) {
    if (!window.pdfjsLib) {
      throw new Error("pdfjs_not_loaded");
    }
    const pdf = await window.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    const pageNum = Math.min(Math.max(page || 1, 1), pdf.numPages);
    const pdfPage = await pdf.getPage(pageNum);
    const viewport = pdfPage.getViewport({ scale: 1.5 });
    modalCanvas.width = viewport.width;
    modalCanvas.height = viewport.height;
    const ctx = modalCanvas.getContext("2d");
    await pdfPage.render({ canvasContext: ctx, viewport }).promise;
    drawBboxOverlay(ctx, bbox, viewport.width, viewport.height);
  }

  async function openBboxModal(recordId) {
    // record_id formats:
    //   DocumentReference/{doc_id}#page={N}&bbox={x},{y},{w},{h}&field={...}
    //   Guideline/{chunk_id}
    //   QuestionnaireResponse/{qr_id}#linkId={...}
    modalLabel.textContent = recordId;
    const ctx = modalCanvas.getContext("2d");
    modalCanvas.width = 800;
    modalCanvas.height = 1000;
    ctx.clearRect(0, 0, modalCanvas.width, modalCanvas.height);

    if (!recordId.startsWith("DocumentReference/")) {
      drawTextFallback(ctx, `(non-document citation: ${recordId})`);
      modal.showModal();
      return;
    }

    const [docPart, fragment] = recordId.split("#");
    const docId = docPart.split("/")[1];
    const fragParams = new URLSearchParams(fragment || "");
    const page = parseInt(fragParams.get("page") || "1", 10);
    const bbox = (fragParams.get("bbox") || "").split(",").map(Number);

    modal.showModal();
    drawTextFallback(ctx, `Loading ${docId} page ${page}…`);

    const previewUrl =
      `/v1/documents/${encodeURIComponent(docId)}/preview` +
      `?patient_id=${encodeURIComponent(PATIENT_ID || "")}` +
      `&physician_user_id=${encodeURIComponent(PHYSICIAN)}`;

    try {
      const r = await fetch(previewUrl);
      if (!r.ok) {
        throw new Error(`preview_${r.status}`);
      }
      const contentType = (r.headers.get("Content-Type") || "").toLowerCase();
      if (contentType.startsWith("image/")) {
        await renderImagePreview(await r.blob(), page, bbox);
      } else if (contentType.startsWith("application/pdf")) {
        await renderPdfPreview(await r.arrayBuffer(), page, bbox);
      } else {
        drawTextFallback(ctx, `(unsupported preview type: ${contentType})`);
        drawBboxOverlay(ctx, bbox, modalCanvas.width, modalCanvas.height);
      }
    } catch (err) {
      console.error("bbox modal preview failed", err);
      modalCanvas.width = 800;
      modalCanvas.height = 1000;
      drawTextFallback(
        ctx,
        `(preview unavailable for ${recordId}: ${err.message})`
      );
      drawBboxOverlay(ctx, bbox, modalCanvas.width, modalCanvas.height);
    }
  }
})();
