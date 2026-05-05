// app/web/copilot_iframe.js
(() => {
  const params = new URLSearchParams(window.location.search);
  const PATIENT_ID = params.get("patient_id");
  const PHYSICIAN = params.get("physician_user_id") || "admin";

  // Session bootstrap — minted lazily on the first chat submit.
  let sessionId = null;

  async function ensureSession() {
    if (sessionId !== null) return sessionId;
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
    return sessionId;
  }
  document.getElementById("patient-banner").textContent =
    PATIENT_ID ? `Patient: ${PATIENT_ID}` : "(no patient context)";

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

  async function openBboxModal(recordId) {
    // record_id formats:
    //   DocumentReference/{doc_id}#page={N}&bbox={x},{y},{w},{h}&field={...}
    //   Guideline/{chunk_id}
    //   QuestionnaireResponse/{qr_id}#linkId={...}
    if (!recordId.startsWith("DocumentReference/")) {
      modalLabel.textContent = recordId;
      const ctx = modalCanvas.getContext("2d");
      ctx.clearRect(0, 0, modalCanvas.width, modalCanvas.height);
      ctx.fillStyle = "#222"; ctx.font = "14px sans-serif";
      ctx.fillText(`(non-document citation: ${recordId})`, 20, 30);
      modal.showModal();
      return;
    }
    const [docPart, fragment] = recordId.split("#");
    const docId = docPart.split("/")[1];
    const params = new URLSearchParams(fragment || "");
    const page = parseInt(params.get("page") || "1", 10);
    const bbox = (params.get("bbox") || "").split(",").map(Number);

    modalLabel.textContent = recordId;
    const ctx = modalCanvas.getContext("2d");
    ctx.clearRect(0, 0, modalCanvas.width, modalCanvas.height);
    ctx.fillStyle = "#fafafa";
    ctx.fillRect(0, 0, modalCanvas.width, modalCanvas.height);
    ctx.fillStyle = "#222"; ctx.font = "14px sans-serif";
    ctx.fillText(
      `Document ${docId} page ${page} — bbox (${bbox.join(", ")})`,
      20, 30
    );
    // For MVP we don't render the actual PDF page in the canvas; we draw the
    // bbox overlay on a neutral background. Full PDF.js rendering is in the
    // post-MVP plan.
    if (bbox.length === 4) {
      const [x, y, w, h] = bbox;
      ctx.strokeStyle = "rgba(220, 60, 60, 0.9)";
      ctx.lineWidth = 3;
      ctx.strokeRect(
        x * modalCanvas.width,
        y * modalCanvas.height,
        w * modalCanvas.width,
        h * modalCanvas.height
      );
    }
    modal.showModal();
  }
})();
