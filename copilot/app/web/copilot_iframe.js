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

  // W2 Phase 5 (Issue 2b hardening, 2026-05-09): map of live banner-item
  // closures keyed by doc_id. Lets handleConfirmReject mutate the very
  // ``item`` object the li.onclick closure captured, so the next click
  // sees fresh ``confirmed_at`` / ``rejected_at`` even if the followup
  // ``refreshPendingIntakes`` is slow / fails / hasn't returned yet.
  const liveItemByDocId = new Map();

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
    liveItemByDocId.clear();
    for (const item of visible) {
      const li = document.createElement("li");
      let stateTag = "";
      if (item.is_pending) {
        stateTag = " [needs review]";
      } else if (item.confirmed_at) {
        stateTag = " [confirmed]";
        li.classList.add("confirmed");
      } else if (item.rejected_at) {
        stateTag = " [rejected]";
        li.classList.add("rejected");
      }
      li.textContent = `${item.doc_type} — ${item.doc_id} (${item.uploaded_at.slice(0, 10)})${stateTag}`;
      li.dataset.docId = item.doc_id;
      li.onclick = () => onPendingIntakeClick(item, li);
      list.appendChild(li);
      // Map the live ``item`` so handleConfirmReject can mutate it
      // synchronously after a successful confirm/reject without waiting
      // for the next refreshPendingIntakes cycle.
      liveItemByDocId.set(item.doc_id, item);
    }
    // W2 Phase 5 — pre-warm extraction so a banner click is near-instant.
    // process_pending is idempotent server-side, so racing the click is safe.
    for (const item of visible) {
      if (!item.is_pending || !PATIENT_ID) continue;
      const url = `/v1/documents/${encodeURIComponent(item.doc_id)}/process`
        + `?patient_id=${encodeURIComponent(PATIENT_ID)}`
        + `&physician_user_id=${encodeURIComponent(PHYSICIAN)}`;
      fetch(url, { method: "POST" }).catch(() => {});
    }
    toggle.onclick = () => {
      const expanded = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", expanded ? "false" : "true");
      list.hidden = expanded;
    };
  }

  async function onPendingIntakeClick(item, listItemEl) {
    // Track whether this click was the trigger that flipped the row from
    // pending → extracted. Confirm/Reject footer only shows in that case.
    let wasJustExtracted = false;

    // For W2 LITE deferred-extraction items, run extraction on-demand
    // before opening the modal. Idempotent server-side — re-clicks no-op.
    if (item.is_pending && PATIENT_ID) {
      const originalText = listItemEl.textContent;
      listItemEl.textContent = `${originalText} (extracting…)`;
      listItemEl.dataset.state = "processing";
      try {
        const url = `/v1/documents/${encodeURIComponent(item.doc_id)}/process`
          + `?patient_id=${encodeURIComponent(PATIENT_ID)}`
          + `&physician_user_id=${encodeURIComponent(PHYSICIAN)}`;
        const r = await fetch(url, { method: "POST" });
        if (!r.ok) {
          listItemEl.textContent = `${originalText} (extraction failed: ${r.status})`;
          listItemEl.dataset.state = "error";
          return;
        }
        const data = await r.json();
        // Cache the freshly-computed overlay so the bbox modal can render
        // citations without a second fetch.
        overlayCache[data.doc_id] = { overlay: data.bbox_overlay };
        listItemEl.textContent = originalText;
        listItemEl.dataset.state = "idle";
        // Mark item so subsequent clicks don't re-process; matches the
        // banner's was-already-extracted item shape.
        item.is_pending = false;
        wasJustExtracted = true;
      } catch (err) {
        listItemEl.textContent = `${originalText} (extraction error)`;
        listItemEl.dataset.state = "error";
        return;
      }
    }

    // Open the bbox modal. The Confirm/Reject footer is bundled into the
    // openBboxModal flow via opts.confirmableDocId so it can NEVER leak
    // onto chat citation chip clicks — those pass no opts.
    const alreadyHandled = item.confirmed_at || item.rejected_at
      || handledDocIds.has(item.doc_id);
    const shouldShowConfirm = !alreadyHandled
      && item.is_front_desk_filed
      && (wasJustExtracted || !item.is_pending);
    const recordId = `DocumentReference/${item.doc_id}`;
    await openBboxModal(recordId, {
      confirmableDocId: shouldShowConfirm ? item.doc_id : null,
    });

    // For non-confirmable items (already handled or non-pending FHIR docs),
    // dismiss inline like the prior behavior.
    if (alreadyHandled) {
      acknowledgedDocIds.add(item.doc_id);
      listItemEl.classList.add("acknowledged");
      listItemEl.onclick = null;
    }

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
  const modalFooter = document.getElementById("bbox-modal-footer");
  const modalConfirmBtn = document.getElementById("bbox-modal-confirm");
  const modalRejectBtn = document.getElementById("bbox-modal-reject");
  const modalStatus = document.getElementById("bbox-modal-status");

  // W2 Plan B: docId currently shown in the modal (when applicable). When
  // not null, the Confirm / Reject buttons act on this id.
  let modalActiveDocId = null;
  // Per-iframe-load cache of confirmed/rejected state for items the user
  // already acted on this session — used to suppress already-handled
  // banner items immediately rather than waiting for a refresh.
  const handledDocIds = new Set();

  modalClose.onclick = () => modal.close();
  modal.addEventListener("cancel", (e) => { e.preventDefault(); modal.close(); });
  modal.addEventListener("close", () => {
    // Reset footer + zoom state when the dialog closes so the next open
    // starts clean.
    modalActiveDocId = null;
    modalFooter.hidden = true;
    modalConfirmBtn.disabled = false;
    modalRejectBtn.disabled = false;
    modalStatus.textContent = "";
    docPreviewState = null;
    if (modalToolbar) modalToolbar.hidden = true;
    // Tell the parent OpenEMR page to shrink the iframe rail back to its
    // resting 400px width. The rail listener in copilot-rail-fragment.php
    // toggles ``body.copilot-doc-open`` based on this message type.
    try {
      window.parent?.postMessage({ type: "copilot-doc-modal-close" }, "*");
    } catch (_) { /* parent unreachable in standalone preview — ignore */ }
  });

  // W2 modal viewer (2026-05-08): zoom toolbar elements + per-doc state
  // for re-rendering at different scales.
  const modalToolbar = document.getElementById("bbox-modal-toolbar");
  const zoomInBtn = document.getElementById("bbox-modal-zoom-in");
  const zoomOutBtn = document.getElementById("bbox-modal-zoom-out");
  const fitWidthBtn = document.getElementById("bbox-modal-fit-width");
  const fitPageBtn = document.getElementById("bbox-modal-fit-page");
  // Caches the currently-open doc's source so the zoom buttons can
  // re-render without re-fetching. Cleared on modal close.
  // Shape: { kind: "pdf"|"image", pdfPage?, img?, bbox, rawText, scale,
  //          baseWidth, baseHeight }
  let docPreviewState = null;

  function _wrapperSize() {
    const wrapper = document.getElementById("bbox-modal-canvas-wrapper");
    if (!wrapper) return { w: 800, h: 1000 };
    return { w: wrapper.clientWidth || 800, h: wrapper.clientHeight || 1000 };
  }

  function _fitWidthScale() {
    if (!docPreviewState) return 1.5;
    const { w } = _wrapperSize();
    // Leave a small inner margin so the canvas doesn't touch the scrollbar.
    const target = Math.max(200, w - 24);
    return Math.max(0.4, Math.min(4, target / docPreviewState.baseWidth));
  }

  function _fitPageScale() {
    if (!docPreviewState) return 1.5;
    const { w, h } = _wrapperSize();
    const sw = (w - 24) / docPreviewState.baseWidth;
    const sh = (h - 24) / docPreviewState.baseHeight;
    return Math.max(0.4, Math.min(4, Math.min(sw, sh)));
  }

  async function _renderAtCurrentScale() {
    if (!docPreviewState) return;
    const ctx = modalCanvas.getContext("2d");
    const scale = docPreviewState.scale;
    if (docPreviewState.kind === "pdf") {
      const { pdfPage, bbox, rawText } = docPreviewState;
      const viewport = pdfPage.getViewport({ scale });
      modalCanvas.width = viewport.width;
      modalCanvas.height = viewport.height;
      await pdfPage.render({ canvasContext: ctx, viewport }).promise;
      const snapped = rawText
        ? await _snapBboxToText(pdfPage, viewport, rawText, bbox)
        : null;
      drawBboxOverlay(ctx, snapped || bbox, viewport.width, viewport.height);
    } else if (docPreviewState.kind === "image") {
      const { img, bbox } = docPreviewState;
      modalCanvas.width = Math.round(img.naturalWidth * scale);
      modalCanvas.height = Math.round(img.naturalHeight * scale);
      ctx.drawImage(img, 0, 0, modalCanvas.width, modalCanvas.height);
      drawBboxOverlay(ctx, bbox, modalCanvas.width, modalCanvas.height);
    }
  }

  zoomInBtn.onclick = async () => {
    if (!docPreviewState) return;
    docPreviewState.scale = Math.min(4, docPreviewState.scale * 1.25);
    await _renderAtCurrentScale();
  };
  zoomOutBtn.onclick = async () => {
    if (!docPreviewState) return;
    docPreviewState.scale = Math.max(0.4, docPreviewState.scale / 1.25);
    await _renderAtCurrentScale();
  };
  fitWidthBtn.onclick = async () => {
    if (!docPreviewState) return;
    docPreviewState.scale = _fitWidthScale();
    await _renderAtCurrentScale();
  };
  fitPageBtn.onclick = async () => {
    if (!docPreviewState) return;
    docPreviewState.scale = _fitPageScale();
    await _renderAtCurrentScale();
  };

  function showConfirmFooter(docId) {
    modalActiveDocId = docId;
    modalConfirmBtn.disabled = false;
    modalRejectBtn.disabled = false;
    modalStatus.textContent = "Filed by front desk — confirm to save to chart.";
    modalFooter.hidden = false;
  }

  async function handleConfirmReject(action) {
    if (!modalActiveDocId || !PATIENT_ID) return;
    modalConfirmBtn.disabled = true;
    modalRejectBtn.disabled = true;
    modalStatus.textContent = action === "confirm" ? "Saving to chart…" : "Rejecting…";
    const url = `/v1/documents/${encodeURIComponent(modalActiveDocId)}/${action}`
      + `?patient_id=${encodeURIComponent(PATIENT_ID)}`
      + `&physician_user_id=${encodeURIComponent(PHYSICIAN)}`;
    try {
      const r = await fetch(url, { method: "POST" });
      if (!r.ok) {
        modalStatus.textContent = `${action === "confirm" ? "Confirm" : "Reject"} failed: ${r.status}`;
        modalConfirmBtn.disabled = false;
        modalRejectBtn.disabled = false;
        return;
      }
      // W2 Phase 5 (Issue 2b hardening, 2026-05-09): parse the response so
      // we can mutate the LIVE banner-item closure with the canonical
      // confirmed_at / rejected_at the server just persisted. Without
      // this, the OLD li.onclick closure still captures item.confirmed_at
      // = undefined, and a second click would re-show the Confirm/Reject
      // footer (because alreadyHandled would only be true via
      // handledDocIds, which resets across iframe reloads).
      const data = await r.json().catch(() => ({}));
      const liveItem = liveItemByDocId.get(modalActiveDocId);
      if (action === "confirm") {
        modalStatus.textContent = "Confirmed.";
        if (liveItem) liveItem.confirmed_at = data.confirmed_at || "confirmed";
      } else {
        modalStatus.textContent = "Rejected.";
        if (liveItem) liveItem.rejected_at = data.rejected_at || "rejected";
      }
      handledDocIds.add(modalActiveDocId);
      // Refresh the banner so the state tag updates without a full reload.
      if (sessionId !== null) {
        refreshPendingIntakes(sessionId).catch(() => {});
      }
    } catch (err) {
      modalStatus.textContent = `${action === "confirm" ? "Confirm" : "Reject"} error: ${err.message}`;
      modalConfirmBtn.disabled = false;
      modalRejectBtn.disabled = false;
    }
  }

  modalConfirmBtn.onclick = () => handleConfirmReject("confirm");
  modalRejectBtn.onclick = () => handleConfirmReject("reject");

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
    if (data.is_pending) {
      // W2 LITE deferred-extraction path — front-desk upload. The doc is
      // filed; the physician will trigger extraction by clicking the
      // pending-intake banner on chart open.
      const dedup = data.was_dedup_hit ? " (already filed earlier)" : "";
      appendMessage(
        "system",
        `Filed pending intake for ${file.name}${dedup}. Physician will review.`
      );
      // Refresh the banner so the new pending row shows up immediately
      // when the front-desk user opens the rail again on this chart.
      if (sessionId !== null) {
        refreshPendingIntakes(sessionId).catch(() => {});
      }
      return;
    }
    const dedup = data.was_dedup_hit ? " (deduped — already extracted)" : "";
    appendMessage(
      "system",
      `Extracted ${data.bbox_overlay.length} fact(s) from ${file.name}${dedup}.`
    );
    // Cache the overlay so citation chips can open the modal without a fetch.
    overlayCache[data.doc_id] = { overlay: data.bbox_overlay };
  }

  const overlayCache = {};
  const evidenceCache = {};

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
    const ev = data.response?.evidence_records || {};
    for (const [rid, rec] of Object.entries(ev)) {
      evidenceCache[rid] = rec;
    }
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
      docPreviewState = {
        kind: "image",
        img,
        bbox,
        rawText: null,
        scale: 1,
        baseWidth: img.naturalWidth,
        baseHeight: img.naturalHeight,
      };
      // Default to fit-width on open so the user sees the whole image
      // without horizontal scrolling. Clamps to [0.4, 4] inside _fitWidthScale.
      docPreviewState.scale = _fitWidthScale();
      await _renderAtCurrentScale();
    } finally {
      URL.revokeObjectURL(url);
    }
  }

  async function renderPdfPreview(arrayBuffer, page, bbox, rawText) {
    if (!window.pdfjsLib) {
      throw new Error("pdfjs_not_loaded");
    }
    const pdf = await window.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    const pageNum = Math.min(Math.max(page || 1, 1), pdf.numPages);
    const pdfPage = await pdf.getPage(pageNum);
    // Establish unscaled page dimensions so fit-width / fit-page can scale
    // relative to the modal's actual viewport size.
    const baseViewport = pdfPage.getViewport({ scale: 1 });
    docPreviewState = {
      kind: "pdf",
      pdfPage,
      bbox,
      rawText,
      scale: 1.5,  // overridden on the next line
      baseWidth: baseViewport.width,
      baseHeight: baseViewport.height,
    };
    docPreviewState.scale = _fitWidthScale();
    await _renderAtCurrentScale();
  }

  function _stripPunct(s) {
    // Strip trailing ,.;: so "shellfish," matches "shellfish". pdf.js often
    // glues punctuation to the preceding token, so naive equality misses
    // what is visually the same word.
    return (s || "").replace(/[,.;:]+$/, "");
  }

  function _padBbox([x, y, w, h], padFactor = 0.18, padFloor = 0.004) {
    // Lift the red stroke off the text glyphs. padY is proportional to
    // row height; padX is half (rows read wider than tall, so horizontal
    // slack is more visually noisy). Clamp to [0, 1].
    const padY = Math.max(h * padFactor, padFloor);
    const padX = Math.max(h * (padFactor / 2), padFloor / 2);
    const x0 = Math.max(0, x - padX);
    const y0 = Math.max(0, y - padY);
    const x1 = Math.min(1, x + w + padX);
    const y1 = Math.min(1, y + h + padY);
    return [x0, y0, x1 - x0, y1 - y0];
  }

  function _rowUnion(winnerRect, items, viewport) {
    // Expand winnerRect (a [x,y,w,h] in normalized coords) to cover the
    // full row — for lab values, gives clinical context (test name +
    // value + units) and moves the red stroke off the digits.
    const wy = winnerRect[1];
    const wh = winnerRect[3];
    const winCy = wy + wh / 2;
    const tol = wh * 0.7;
    let xMin = winnerRect[0];
    let yMin = wy;
    let xMax = winnerRect[0] + winnerRect[2];
    let yMax = wy + wh;
    for (const item of items) {
      const s = (item.str || "").trim();
      if (!s) continue;
      const rect = _itemToNormalizedRect(item, viewport);
      const cy = rect[1] + rect[3] / 2;
      if (Math.abs(cy - winCy) > tol) continue;
      if (rect[0] < xMin) xMin = rect[0];
      if (rect[1] < yMin) yMin = rect[1];
      if (rect[0] + rect[2] > xMax) xMax = rect[0] + rect[2];
      if (rect[1] + rect[3] > yMax) yMax = rect[1] + rect[3];
    }
    return [xMin, yMin, xMax - xMin, yMax - yMin];
  }

  function _itemToNormalizedRect(item, viewport) {
    const tx = item.transform;
    const x = tx[4];
    const y = tx[5];
    const w = item.width || tx[0];
    const h = item.height || tx[3];
    const [vx1, vy1, vx2, vy2] = viewport.convertToViewportRectangle(
      [x, y, x + w, y + h]
    );
    const minX = Math.min(vx1, vx2);
    const maxX = Math.max(vx1, vx2);
    const minY = Math.min(vy1, vy2);
    const maxY = Math.max(vy1, vy2);
    return [
      minX / viewport.width,
      minY / viewport.height,
      (maxX - minX) / viewport.width,
      (maxY - minY) / viewport.height,
    ];
  }

  async function _snapBboxToText(pdfPage, viewport, rawText, fallbackBbox) {
    // Snap the bbox overlay to the PDF text layer. Returns a normalized
    // [x,y,w,h] rect or null. Caller uses fallbackBbox on null.
    //
    // Strategy:
    //   1. Numeric-token branch (lab values, vitals): pick the OCR/PDF
    //      item containing the number, disambiguate by vertical proximity
    //      to the VLM bbox. Then expand to the full row for clinical
    //      context (label + value + units), and pad so the stroke does
    //      not overlap the digits.
    //   2. Whole-string fallback (single-word intake answers, allergies):
    //      exact text-item match, padded.
    //   3. Multi-token fallback (free-text intake answers, "John Doe",
    //      "shellfish, peanuts"): tokenize, strip trailing punctuation,
    //      find each token in the same y-row as the VLM bbox (tolerance
    //      = 1.0 row height), require >= ceil(60%) of tokens to match,
    //      union the matched rects. Reject the union if it's wider than
    //      1.5x the VLM bbox (sanity guard against drift).
    // We DO NOT use loose substring matching against multi-word raw_text
    // because PDF.js usually splits rows into many items and the FIRST
    // match (e.g. "LDL") would land miles from the actual value.
    const target = (rawText || "").trim();
    if (!target) return null;
    const numMatch = target.match(/-?\d+(?:\.\d+)?/);
    const numToken = numMatch ? numMatch[0] : null;
    const fbValid = Array.isArray(fallbackBbox) && fallbackBbox.length === 4;
    const fbCenterY = fbValid ? fallbackBbox[1] + fallbackBbox[3] / 2 : null;
    const fbHeight = fbValid ? fallbackBbox[3] : 0;
    const fbWidth = fbValid ? fallbackBbox[2] : 1;
    try {
      const tc = await pdfPage.getTextContent();
      if (numToken) {
        const candidates = [];
        for (const item of tc.items) {
          const s = (item.str || "").trim();
          if (!s.includes(numToken)) continue;
          const rect = _itemToNormalizedRect(item, viewport);
          const itemCenterY = rect[1] + rect[3] / 2;
          const dy = fbCenterY !== null ? Math.abs(itemCenterY - fbCenterY) : 0;
          candidates.push({ rect, dy, exact: s === numToken });
        }
        if (candidates.length > 0) {
          // Prefer exact-token matches over substring; then the y-closest one.
          candidates.sort((a, b) => {
            if (a.exact !== b.exact) return a.exact ? -1 : 1;
            return a.dy - b.dy;
          });
          return _padBbox(_rowUnion(candidates[0].rect, tc.items, viewport));
        }
      }
      // Exact whole-string match fallback (single-word intake answers,
      // allergy strings).
      for (const item of tc.items) {
        const s = (item.str || "").trim();
        if (s && s === target) {
          return _padBbox(_itemToNormalizedRect(item, viewport));
        }
      }
      // Multi-token fallback (free-text intake answers).
      const tokens = target
        .split(/\s+/)
        .map(_stripPunct)
        .filter((t) => t.length > 0);
      if (tokens.length >= 2 && fbCenterY !== null) {
        const required = Math.max(2, Math.ceil(tokens.length * 0.6));
        const matchedRects = [];
        const matchedTokens = new Set();
        for (const tok of tokens) {
          let best = null;
          for (const item of tc.items) {
            const s = _stripPunct((item.str || "").trim());
            if (s !== tok) continue;
            const rect = _itemToNormalizedRect(item, viewport);
            const itemCenterY = rect[1] + rect[3] / 2;
            const rowTol = Math.max(fbHeight, rect[3]) * 1.0;
            const dy = Math.abs(itemCenterY - fbCenterY);
            if (dy > rowTol) continue;
            if (best === null || dy < best.dy) best = { rect, dy };
          }
          if (best !== null) {
            matchedRects.push(best.rect);
            matchedTokens.add(tok);
          }
        }
        if (matchedTokens.size >= required) {
          let xMin = Infinity;
          let yMin = Infinity;
          let xMax = -Infinity;
          let yMax = -Infinity;
          for (const r of matchedRects) {
            if (r[0] < xMin) xMin = r[0];
            if (r[1] < yMin) yMin = r[1];
            if (r[0] + r[2] > xMax) xMax = r[0] + r[2];
            if (r[1] + r[3] > yMax) yMax = r[1] + r[3];
          }
          // Drift guard: a union that's much wider than the VLM hint is
          // usually picking up unrelated tokens. Bail to fallback.
          if (xMax - xMin <= fbWidth * 1.5) {
            return _padBbox([xMin, yMin, xMax - xMin, yMax - yMin]);
          }
        }
      }
    } catch (e) {
      console.warn("text-snap failed; using VLM bbox", e);
    }
    return null;
  }

  const modalCard = document.getElementById("bbox-modal-card");

  function _showCanvasMode() {
    modalCard.hidden = true;
    modalCard.replaceChildren();
    modalCanvas.hidden = false;
  }

  function _showCardMode() {
    modalCanvas.hidden = true;
    modalCard.hidden = false;
    modalCard.replaceChildren();
  }

  async function openBboxModal(recordId, opts = {}) {
    // record_id formats:
    //   DocumentReference/{doc_id}#page={N}&bbox={x},{y},{w},{h}&field={...}
    //   Observation/{id}, MedicationRequest/{id}, AllergyIntolerance/{id},
    //   Condition/{id}, Encounter/{id}, Patient/{id}
    //   Guideline/{chunk_id}
    //   QuestionnaireResponse/{qr_id}#linkId={...}
    //
    // opts.confirmableDocId (optional): when set, the Confirm/Reject footer
    // is shown for that doc_id AFTER the modal finishes opening. Only
    // onPendingIntakeClick passes this — chip clicks pass nothing, which
    // makes it architecturally impossible for chat citations to surface
    // the footer. This supersedes the W2 Phase 5 defensive reset that
    // was racy with onPendingIntakeClick's separate showConfirmFooter
    // call.
    modalFooter.hidden = true;
    modalStatus.textContent = "";
    modalLabel.textContent = recordId;
    if (recordId.startsWith("DocumentReference/")) {
      _showCanvasMode();
      await openDocumentModal(recordId);
      if (opts.confirmableDocId) {
        showConfirmFooter(opts.confirmableDocId);
      }
      return;
    }
    _showCardMode();
    return openEvidenceCardModal(recordId, evidenceCache[recordId]);
  }

  async function openDocumentModal(recordId) {
    const ctx = modalCanvas.getContext("2d");
    modalCanvas.width = 800;
    modalCanvas.height = 1000;
    ctx.clearRect(0, 0, modalCanvas.width, modalCanvas.height);

    const [docPart, fragment] = recordId.split("#");
    const docId = docPart.split("/")[1];
    const fragParams = new URLSearchParams(fragment || "");
    const page = parseInt(fragParams.get("page") || "1", 10);
    const bbox = (fragParams.get("bbox") || "").split(",").map(Number);
    const rawText = decodeURIComponent(fragParams.get("q") || "");

    // W2 modal viewer: zoom toolbar applies only to canvas-mode (PDF/PNG).
    if (modalToolbar) modalToolbar.hidden = false;
    // Tell the parent OpenEMR page to widen the iframe rail so the modal
    // has room to render the doc at a useful size. Listener lives in
    // copilot-rail-fragment.php.
    try {
      window.parent?.postMessage({ type: "copilot-doc-modal-open" }, "*");
    } catch (_) { /* parent unreachable in standalone preview — ignore */ }
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
        await renderPdfPreview(await r.arrayBuffer(), page, bbox, rawText);
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

  function openEvidenceCardModal(recordId, ev) {
    // Card mode (non-DocumentReference): no PDF/PNG to render, so the
    // zoom toolbar stays hidden. We still widen the rail because the
    // structured card reads better with more horizontal room.
    if (modalToolbar) modalToolbar.hidden = true;
    try {
      window.parent?.postMessage({ type: "copilot-doc-modal-open" }, "*");
    } catch (_) { /* parent unreachable in standalone preview — ignore */ }
    modal.showModal();
    if (!ev) {
      const empty = document.createElement("div");
      empty.className = "card-empty";
      empty.textContent =
        "Evidence record not available in cache. " +
        "Resume the chat to re-fetch.";
      modalCard.appendChild(empty);
      _appendRecordIdFooter(recordId);
      return;
    }
    const kind = ev.kind || "unknown";
    const data = ev.data || {};
    const renderers = {
      observation: renderObservation,
      medication: renderMedication,
      allergy: renderAllergy,
      condition: renderCondition,
      encounter: renderEncounter,
      patient: renderPatient,
      guideline: renderGuideline,
      questionnaire: renderQuestionnaire,
    };
    const fn = renderers[kind] || renderUnknown;
    fn(recordId, data);
    _appendRecordIdFooter(recordId);
  }

  function _appendRecordIdFooter(recordId) {
    const rid = document.createElement("div");
    rid.className = "card-rid";
    rid.textContent = recordId;
    modalCard.appendChild(rid);
  }

  function _row(dl, label, value) {
    if (value === undefined || value === null || value === "") return;
    const dt = document.createElement("dt");
    dt.textContent = label;
    const dd = document.createElement("dd");
    dd.textContent = String(value);
    dl.appendChild(dt);
    dl.appendChild(dd);
  }

  function _heading(text) {
    const h = document.createElement("h3");
    h.textContent = text;
    modalCard.appendChild(h);
  }

  function renderObservation(_rid, d) {
    _heading(d.test_name || d.display || "Observation");
    const dl = document.createElement("dl");
    const value = d.value !== undefined && d.unit ? `${d.value} ${d.unit}` : d.value;
    _row(dl, "Value", value);
    _row(dl, "Reference", d.reference_range);
    _row(dl, "Abnormal", d.abnormal_flag);
    _row(dl, "Date", d.collection_date || d.effective_datetime);
    _row(dl, "LOINC", d.loinc_code);
    modalCard.appendChild(dl);
  }

  function renderMedication(_rid, d) {
    _heading(d.drug_name || d.name || d.display || "Medication");
    const dl = document.createElement("dl");
    _row(dl, "Dose", d.dosage_text || d.dose);
    _row(dl, "Frequency", d.frequency);
    _row(dl, "Status", d.status || d.clinical_status);
    _row(dl, "Authored", d.authored_on || d.start);
    _row(dl, "RxNorm", d.rxnorm_code);
    modalCard.appendChild(dl);
  }

  function renderAllergy(_rid, d) {
    _heading(d.display || d.coded_substance || d.verbatim_substance || "Allergy");
    const dl = document.createElement("dl");
    if (d.verbatim_substance && d.verbatim_substance !== d.display) {
      _row(dl, "Verbatim", d.verbatim_substance);
    }
    const reaction = Array.isArray(d.reaction) ? d.reaction.join(", ") : d.reaction;
    _row(dl, "Reaction", reaction);
    _row(dl, "Severity", d.severity);
    _row(dl, "Criticality", d.criticality);
    _row(dl, "Status", d.clinical_status);
    if (d.ambiguity_note) _row(dl, "Note", d.ambiguity_note);
    modalCard.appendChild(dl);
  }

  function renderCondition(_rid, d) {
    _heading(d.display || d.condition || "Condition");
    const dl = document.createElement("dl");
    _row(dl, "Onset", d.onset_datetime || d.onset);
    _row(dl, "Status", d.clinical_status || d.status);
    _row(dl, "Verification", d.verification_status);
    modalCard.appendChild(dl);
  }

  function renderEncounter(_rid, d) {
    _heading(d.type_display || "Encounter");
    const dl = document.createElement("dl");
    _row(dl, "Reason", d.reason_text || d.reason);
    _row(dl, "Start", d.start);
    _row(dl, "End", d.end);
    _row(dl, "Status", d.status);
    modalCard.appendChild(dl);
  }

  function renderPatient(_rid, d) {
    _heading(`Patient ${d.id || ""}`.trim());
    const dl = document.createElement("dl");
    _row(dl, "Age", d.age);
    _row(dl, "Gender", d.gender);
    if (d.chief_concern) _row(dl, "Chief concern", d.chief_concern);
    _row(dl, "Active", d.active);
    _row(dl, "Deceased", d.deceased);
    modalCard.appendChild(dl);
  }

  function renderGuideline(_rid, d) {
    _heading(d.source || d.guideline_title || "Guideline");
    const dl = document.createElement("dl");
    _row(dl, "Section", d.section || d.heading);
    _row(dl, "Chunk", d.chunk_id);
    _row(dl, "Last updated", d.last_updated);
    if (d.url) {
      const dt = document.createElement("dt");
      dt.textContent = "Link";
      const dd = document.createElement("dd");
      const a = document.createElement("a");
      a.href = d.url;
      a.target = "_blank";
      a.rel = "noreferrer";
      a.textContent = d.url;
      dd.appendChild(a);
      dl.appendChild(dt);
      dl.appendChild(dd);
    }
    modalCard.appendChild(dl);
    if (d.text || d.chunk_text) {
      const pre = document.createElement("pre");
      pre.textContent = d.text || d.chunk_text;
      modalCard.appendChild(pre);
    }
  }

  function renderQuestionnaire(rid, d) {
    _heading("Questionnaire answer");
    const dl = document.createElement("dl");
    const linkId = (rid.split("#linkId=")[1] || d.link_id || "").trim();
    if (linkId) _row(dl, "Question", linkId);
    _row(dl, "Answer", d.value || d.raw_text || d.answer);
    _row(dl, "Status", d.status);
    modalCard.appendChild(dl);
  }

  function renderUnknown(_rid, d) {
    _heading("Evidence");
    const dl = document.createElement("dl");
    for (const [k, v] of Object.entries(d)) {
      if (k === "record_id" || k === "subject_pseudonym") continue;
      const display = Array.isArray(v) ? v.join(", ")
                    : typeof v === "object" && v !== null ? JSON.stringify(v)
                    : v;
      _row(dl, k, display);
    }
    modalCard.appendChild(dl);
  }
})();
