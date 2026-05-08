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

    // Open the existing bbox modal pointed at this doc's parent record.
    // The user can then click into specific fields if extractions exist.
    const recordId = `DocumentReference/${item.doc_id}`;
    openBboxModal(recordId);

    // W2 Plan B: when the click triggered the extraction (or the item
    // was already extracted but not yet confirmed/rejected), surface
    // the Confirm/Reject footer so the physician can save to chart.
    const alreadyHandled = item.confirmed_at || item.rejected_at
      || handledDocIds.has(item.doc_id);
    if (!alreadyHandled && (wasJustExtracted || !item.is_pending)) {
      showConfirmFooter(item.doc_id);
    }

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
    // Reset footer state when the dialog closes so the next open starts clean.
    modalActiveDocId = null;
    modalFooter.hidden = true;
    modalConfirmBtn.disabled = false;
    modalRejectBtn.disabled = false;
    modalStatus.textContent = "";
  });

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
      const data = await r.json();
      if (action === "confirm") {
        if (data.openemr_doc_id) {
          modalStatus.textContent = `Saved to chart (OpenEMR doc id ${data.openemr_doc_id}).`;
        } else if (data.openemr_write_error) {
          modalStatus.textContent = `Confirmed locally; OpenEMR write failed: ${data.openemr_write_error}`;
        } else {
          modalStatus.textContent = "Confirmed.";
        }
      } else {
        modalStatus.textContent = "Rejected.";
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
      modalCanvas.width = img.naturalWidth;
      modalCanvas.height = img.naturalHeight;
      const ctx = modalCanvas.getContext("2d");
      ctx.drawImage(img, 0, 0);
      drawBboxOverlay(ctx, bbox, modalCanvas.width, modalCanvas.height);
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
    const viewport = pdfPage.getViewport({ scale: 1.5 });
    modalCanvas.width = viewport.width;
    modalCanvas.height = viewport.height;
    const ctx = modalCanvas.getContext("2d");
    await pdfPage.render({ canvasContext: ctx, viewport }).promise;
    const snapped = rawText
      ? await _snapBboxToText(pdfPage, viewport, rawText, bbox)
      : null;
    drawBboxOverlay(ctx, snapped || bbox, viewport.width, viewport.height);
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
    //   1. If rawText contains a numeric token (lab values, vitals), prefer
    //      to snap to that token specifically — and disambiguate when the
    //      number appears in multiple rows by picking the candidate whose
    //      vertical center is closest to the VLM-emitted bbox.
    //   2. Otherwise fall back to an exact-string match against a single
    //      text-item (intake answers, allergies).
    // We DO NOT use loose substring matching against multi-word raw_text
    // because PDF.js usually splits rows into many items and the FIRST
    // match (e.g. "LDL") would land miles from the actual value.
    const target = (rawText || "").trim();
    if (!target) return null;
    const numMatch = target.match(/-?\d+(?:\.\d+)?/);
    const numToken = numMatch ? numMatch[0] : null;
    const fbCenterY =
      Array.isArray(fallbackBbox) && fallbackBbox.length === 4
        ? fallbackBbox[1] + fallbackBbox[3] / 2
        : null;
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
          return candidates[0].rect;
        }
      }
      // Exact whole-string match fallback (intake answers, allergy strings).
      for (const item of tc.items) {
        const s = (item.str || "").trim();
        if (s && s === target) {
          return _itemToNormalizedRect(item, viewport);
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

  async function openBboxModal(recordId) {
    // record_id formats:
    //   DocumentReference/{doc_id}#page={N}&bbox={x},{y},{w},{h}&field={...}
    //   Observation/{id}, MedicationRequest/{id}, AllergyIntolerance/{id},
    //   Condition/{id}, Encounter/{id}, Patient/{id}
    //   Guideline/{chunk_id}
    //   QuestionnaireResponse/{qr_id}#linkId={...}
    modalLabel.textContent = recordId;
    if (recordId.startsWith("DocumentReference/")) {
      _showCanvasMode();
      return openDocumentModal(recordId);
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
