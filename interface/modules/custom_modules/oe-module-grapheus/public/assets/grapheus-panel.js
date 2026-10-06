/* Grapheus tab in an OpenEMR encounter: connect, record, review, apply. */
(function () {
  'use strict';
  var root = document.getElementById('grapheus');
  var API = root.dataset.api, CSRF = root.dataset.csrf;
  var $ = function (id) { return document.getElementById(id); };
  var state = null, current = null, pollT = null;
  var LABELS = { chief_complaint: 'Chief complaint', hpi: 'HPI', ros: 'ROS', medications_mentioned: 'Medications', allergies_mentioned: 'Allergies', history_mentioned: 'History' };

  function api(action, body, qs) {
    var init = { method: body ? 'POST' : 'GET', credentials: 'same-origin', headers: { 'X-CSRF-Token': CSRF } };
    if (body) { init.headers['Content-Type'] = 'application/json'; init.body = JSON.stringify(body); }
    return fetch(API + '?action=' + action + (qs || ''), init)
      .then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Unexpected response (' + r.status + ')' }; }); })
      .catch(function () { return { ok: false, error: 'Could not reach OpenEMR.' }; });
  }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function msg(text, kind) { var m = $('g-msg'); if (!text) { m.className = 'alert d-none'; return; } m.className = 'alert alert-' + (kind || 'info'); m.textContent = text; }
  function show(id) { ['g-connect', 'g-home', 'g-review'].forEach(function (s) { $(s).classList.toggle('d-none', s !== id); }); }
  var none = function (t) { return !t || /^none mentioned\.?$/i.test(String(t).trim()); };

  // ---------------------------------------------------------------- load
  function load() {
    msg('');
    api('state').then(function (j) {
      if (!j.ok) return msg(j.error, 'danger');
      state = j;
      $('g-patient').textContent = j.patientName || '';
      if (!j.connected) { $('g-account').textContent = ''; return show('g-connect'); }
      var a = j.account || {};
      $('g-account').innerHTML = esc(a.email || '') + (a.plan ? ' · ' + esc(a.plan) : '');
      if (a.access && !a.access.ok) msg(a.access.error, 'warning');
      show('g-home');
      $('g-record').disabled = !j.encounter || (a.access && !a.access.ok);
      list();
    });
  }

  // ---------------------------------------------------------------- connect
  $('g-connect-btn').onclick = function () {
    var w = window.open(state.server + '/app#connect-emr=' + encodeURIComponent(location.origin), 'grapheus-connect', 'width=520,height=680');
    if (!w) msg('Allow pop-ups for OpenEMR, then press Connect again.', 'warning');
  };
  window.addEventListener('message', function (e) {
    if (!state || e.origin !== state.server) return;
    var d = e.data || {};
    if (d.type !== 'grapheus-connect' || !d.key) return;
    api('connect', { key: d.key, email: d.email || '' }).then(function (j) { if (!j.ok) return msg(j.error, 'danger'); msg('Grapheus connected.', 'success'); load(); });
  });
  window.addEventListener('message', function (e) {
    if (e.origin !== location.origin || !e.data || e.data.type !== 'grapheus-finished') return;
    if (e.data.visit) openVisit(e.data.visit); else load();
  });
  $('g-disconnect').onclick = function () { if (confirm('Disconnect Grapheus for your OpenEMR user?')) api('disconnect', {}).then(load); };

  // ---------------------------------------------------------------- record
  $('g-record').onclick = function () {
    if (!$('g-consent').checked) return msg('Confirm the patient knows the visit is being recorded.', 'warning');
    var url = root.dataset.recorder + '?mode=' + encodeURIComponent($('g-mode').value) + '&ptype=' + encodeURIComponent($('g-ptype').value) + '&prep=' + (parseInt($('g-prep').value, 10) || 0);
    var w = window.open(url, 'grapheus-recorder', 'width=360,height=330');
    if (!w) msg('Allow pop-ups for OpenEMR so the recorder window can open.', 'warning');
    else msg('Recording in the Grapheus window. When you press Stop there, the draft appears here.', 'info');
  };

  // ---------------------------------------------------------------- drafts
  var STATUS = { recording: 'Recording', transcribing: 'Transcribing', drafting: 'Writing', ready: 'Ready', error: 'Problem' };
  function list() {
    api('visits').then(function (j) {
      var vs = (j.visits || []).sort(function (a, b) { return (b.thisEncounter ? 1 : 0) - (a.thisEncounter ? 1 : 0); });
      $('g-list').innerHTML = vs.length ? vs.map(function (v) {
        return '<li class="list-group-item list-group-item-action d-flex" data-id="' + esc(v.id) + '" style="cursor:pointer"><div><b>' + esc(v.label || 'Visit') + '</b>' +
          (v.thisEncounter ? ' <span class="badge badge-info">this encounter</span>' : '') + (v.appliedAt ? ' <span class="badge badge-success">added</span>' : '') +
          '<div class="small text-muted">' + new Date(v.startedAt).toLocaleString() + ' · ' + (v.mode === 'in_person' ? 'in person' : 'telehealth') + (v.faceMin != null ? ' · ' + v.faceMin + ' min' : '') + '</div></div>' +
          '<span class="ml-auto badge badge-' + (v.status === 'ready' ? 'success' : v.status === 'error' ? 'danger' : 'warning') + ' align-self-center">' + (STATUS[v.status] || v.status) + '</span></li>';
      }).join('') : '<li class="list-group-item text-muted small">No drafts in the last 48 hours.</li>';
      $('g-list').querySelectorAll('[data-id]').forEach(function (li) { li.onclick = function () { openVisit(li.dataset.id); }; });
    });
  }
  $('g-refresh').onclick = list;
  $('g-back').onclick = function () { clearTimeout(pollT); load(); };

  function openVisit(id) {
    clearTimeout(pollT);
    show('g-review'); msg('');
    $('g-r-body').classList.add('d-none');
    var poll = function () {
      api('status', null, '&visit=' + encodeURIComponent(id)).then(function (j) {
        if (!j.ok) { $('g-r-progress').classList.remove('d-none'); $('g-r-progress').textContent = j.error; return; }
        var v = current = j.visit;
        $('g-r-title').textContent = (v.label || 'Visit') + ' — ' + new Date(v.startedAt).toLocaleString();
        if (v.status === 'ready') { $('g-r-progress').classList.add('d-none'); return review(v); }
        $('g-r-progress').classList.remove('d-none');
        $('g-r-progress').textContent = v.status === 'error' ? 'Problem: ' + v.error : v.status === 'drafting' ? 'Writing the note, codes and prescriptions… (about a minute)' : 'Transcribing (' + v.transcribed + ' of ' + v.segments + ')…';
        if (v.status !== 'error') pollT = setTimeout(poll, 4000);
      });
    };
    poll();
  }

  // ---------------------------------------------------------------- review
  var TIME = { established: [[40, '99215'], [30, '99214'], [20, '99213'], [10, '99212']], new: [[60, '99205'], [45, '99204'], [30, '99203'], [15, '99202']] };
  function timeCode(total, pt) { var l = TIME[pt === 'new' ? 'new' : 'established']; for (var i = 0; i < l.length; i++) if (total >= l[i][0]) { var x = i === 0 ? Math.floor((total - l[0][0]) / 15) : 0; return l[i][1] + (x ? ' + 99417 ×' + x : ''); } return ''; }

  function review(v) {
    var r = v.result || {}, f = r.fields || {};
    $('g-r-body').classList.remove('d-none');
    var s = ['chief_complaint', 'hpi', 'ros', 'medications_mentioned', 'allergies_mentioned', 'history_mentioned']
      .filter(function (k) { return !none(f[k]); }).map(function (k) { return LABELS[k] + ': ' + f[k]; }).join('\n\n');
    $('g-s').value = s;
    $('g-o').value = f.physical_exam || '';
    var dx = (r.diagnoses || []).map(function (d) { return '- ' + d.description + (d.icd10 ? ' (' + d.icd10 + ')' : ''); }).join('\n');
    $('g-a').value = (f.assessment || '') + (dx ? '\n\nDiagnoses:\n' + dx : '');
    $('g-p').value = (f.plan || '') + (none(f.patient_instructions) ? '' : '\n\nPatient instructions: ' + f.patient_instructions);

    // time
    var face = v.faceMin || 0;
    $('g-t-face').textContent = v.faceMin != null ? face + ' min' : '–';
    $('g-t-prep').value = v.prepMin || ''; $('g-t-doc').value = '';
    var recalc = function () {
      var total = face + (parseInt($('g-t-prep').value, 10) || 0) + (parseInt($('g-t-doc').value, 10) || 0);
      $('g-t-total').textContent = total + ' min';
      var c = timeCode(total, v.patientType);
      $('g-t-code').innerHTML = c ? 'Total time supports <b>' + c + '</b> (' + esc(v.patientType) + ' patient), if it beats the MDM level.' : '';
    };
    $('g-t-prep').oninput = recalc; $('g-t-doc').oninput = recalc; recalc();

    $('g-problems').innerHTML = (r.diagnoses || []).map(function (d, i) {
      return '<div class="g-row"><input type="checkbox" data-pr="' + i + '"' + (d.icd10 ? ' checked' : '') + '><div>' + esc(d.description) + ' <b>' + esc(d.icd10 || '') + '</b>' +
        (d.icd10_suggested ? ' <span class="g-flag">code looked up — check</span>' : (!d.certain ? ' <span class="g-flag">check</span>' : '')) + '</div></div>';
    }).join('') || '<p class="small text-muted mb-0">None.</p>';
    $('g-allergies').innerHTML = (r.allergies || []).map(function (a, i) {
      return '<div class="g-row"><input type="checkbox" data-al="' + i + '" checked><div>' + esc(a.substance) + (a.reaction ? ' — ' + esc(a.reaction) : '') + (a.severity ? ' (' + esc(a.severity) + ')' : '') + (!a.certain ? ' <span class="g-flag">check</span>' : '') + '</div></div>';
    }).join('') || '<p class="small text-muted mb-0">No new allergies reported.</p>';
    $('g-rx').innerHTML = (r.prescriptions || []).map(function (x, i) {
      var flag = (x.missing && x.missing.length ? 'Not stated: ' + x.missing.join(', ') + '. ' : '') + (!x.certain ? 'Unclear in the recording. ' : '') + (x.action && x.action !== 'new' ? String(x.action).toUpperCase() + '. ' : '');
      return '<div class="g-row"><input type="checkbox" data-rx="' + i + '"' + (x.action === 'stop' ? '' : ' checked') + '><div class="g-rx-grid">' +
        '<input class="form-control form-control-sm" data-f="drug" value="' + esc(x.drug) + '" title="Drug">' +
        '<input class="form-control form-control-sm" data-f="strength" value="' + esc(x.strength) + '" title="Strength">' +
        '<input class="form-control form-control-sm" data-f="sig" value="' + esc(x.sig) + '" title="Directions">' +
        '<input class="form-control form-control-sm" data-f="quantity" value="' + esc(x.quantity) + '" placeholder="Qty" title="Quantity">' +
        '<input class="form-control form-control-sm" data-f="refills" value="' + esc(x.refills) + '" placeholder="Refills" title="Refills"></div></div>' +
        (flag ? '<div class="g-flag mb-1">' + esc(flag) + (x.rxcui ? '' : 'No RxNorm match. ') + '</div>' : '');
    }).join('') || '<p class="small text-muted mb-0">No prescriptions decided in this visit.</p>';
    $('g-basis').textContent = r.billing_basis || '';
    $('g-billing').innerHTML = (r.billing || []).map(function (b, i) {
      return '<div class="g-row"><input type="checkbox" data-bi="' + i + '"' + (b.confidence === 'low' ? '' : ' checked') + '><div><b>' + esc(b.code) + (b.modifiers ? '-' + esc(b.modifiers) : '') + '</b> ' + esc(b.description) +
        ' <span class="badge badge-' + (b.confidence === 'high' ? 'success' : b.confidence === 'low' ? 'danger' : 'warning') + '">' + esc(b.confidence) + '</span>' +
        '<div class="small">' + esc(b.rationale) + '</div><div class="small text-muted">Document: ' + esc(b.documentation_needed) + '</div></div></div>';
    }).join('') || '<p class="small text-muted mb-0">None.</p>';
    var un = r.unclear || [];
    $('g-unclear-card').classList.toggle('d-none', !un.length);
    $('g-unclear').innerHTML = un.map(function (u) { return '<li>' + esc(u.field) + ': heard “' + esc(u.as_heard) + '”' + (u.best_guess ? ' — best guess ' + esc(u.best_guess) : '') + '</li>'; }).join('');
  }

  // ---------------------------------------------------------------- apply
  $('g-apply').onclick = function () {
    if (!current) return;
    var r = current.result || {};
    var checked = function (attr) { return Array.prototype.map.call(document.querySelectorAll('[data-' + attr + ']:checked'), function (el) { return Number(el.getAttribute('data-' + attr)); }); };
    var plan = $('g-p').value;
    if ($('g-t-add').checked) {
      var face = current.faceMin || 0, prep = parseInt($('g-t-prep').value, 10) || 0, doc = parseInt($('g-t-doc').value, 10) || 0;
      plan += '\n\nI spent a total of ' + (face + prep + doc) + ' minutes on this patient’s care on the date of the encounter, including ' + face + ' minutes face-to-face' +
        (prep ? ', ' + prep + ' minutes reviewing records before the visit' : '') + (doc ? ' and ' + doc + ' minutes documenting and coordinating care' : '') + '.';
    }
    var rxRows = document.querySelectorAll('#g-rx .g-row');
    var payload = {
      visit: current.id,
      soap: $('g-add-note').checked ? { subjective: $('g-s').value, objective: $('g-o').value, assessment: $('g-a').value, plan: plan } : null,
      problems: checked('pr').map(function (i) { var d = r.diagnoses[i]; return { title: d.description, icd10: d.icd10 }; }),
      diagnoses: checked('pr').map(function (i) { return r.diagnoses[i]; }),
      allergies: checked('al').map(function (i) { return r.allergies[i]; }),
      prescriptions: checked('rx').map(function (i) {
        var x = Object.assign({}, r.prescriptions[i]);
        rxRows[i].querySelectorAll('[data-f]').forEach(function (inp) { x[inp.dataset.f] = inp.value; });
        return x;
      }),
      billing: checked('bi').map(function (i) { return r.billing[i]; }),
    };
    var n = payload.prescriptions.length;
    if (!confirm('Add the checked items to this encounter?' + (n ? '\n\n' + n + ' prescription(s) will be ENTERED, not sent.' : '') + (payload.billing.length ? '\nFee-sheet codes will be entered unbilled.' : ''))) return;
    $('g-apply').disabled = true;
    api('apply', payload).then(function (j) {
      $('g-apply').disabled = false;
      if (!j.ok) return msg(j.error, 'danger');
      var s = j.summary;
      msg('Added: ' + [s.note ? 'SOAP note' : '', s.problems.length ? s.problems.length + ' problem(s)' : '', s.allergies.length ? s.allergies.length + ' allergy(ies)' : '',
        s.prescriptions.length ? s.prescriptions.length + ' prescription(s), not sent' : '', s.billing.length ? s.billing.length + ' fee-sheet line(s), unbilled' : ''].filter(Boolean).join(', ') +
        (s.skipped.length ? '. Skipped: ' + s.skipped.join('; ') : '') + '. Opening the encounter…', 'success');
      setTimeout(function () { if (top.restoreSession) top.restoreSession(); location.href = root.dataset.encounterUrl; }, 2500);
    });
  };

  load();
})();
