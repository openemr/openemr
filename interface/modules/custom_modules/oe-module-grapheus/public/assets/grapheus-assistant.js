/* Grapheus Assistant: chat, approve, undo. */
(function () {
  'use strict';
  var root = document.getElementById('grapheus');
  var API = root.dataset.api, CSRF = root.dataset.csrf;
  var $ = function (id) { return document.getElementById(id); };
  var state = null, history = [];
  var OPS = { set_global: 'Setting', upsert_facility: 'Facility', add_list_option: 'List', update_list_option: 'List', upsert_fee: 'Fee', upsert_appt_category: 'Visit type', set_form_enabled: 'Form', create_appointment: 'Appointment' };

  function api(action, body) {
    var init = { method: body ? 'POST' : 'GET', credentials: 'same-origin', headers: { 'X-CSRF-Token': CSRF } };
    if (body) { init.headers['Content-Type'] = 'application/json'; init.body = JSON.stringify(body); }
    return fetch(API + '?action=' + action, init).then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Unexpected response (' + r.status + ')' }; }); })
      .catch(function () { return { ok: false, error: 'Could not reach OpenEMR.' }; });
  }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  /** Insert HTML built from escaped values, sanitized again by OpenEMR's DOMPurify. */
  function put(el, html) {
    var table = el.tagName === 'TABLE';   // rows only parse inside a table
    var frag = window.DOMPurify.sanitize(table ? '<table>' + html + '</table>' : html, { RETURN_DOM_FRAGMENT: true });
    el.replaceChildren.apply(el, table ? Array.prototype.slice.call(frag.firstChild ? frag.firstChild.childNodes : []) : [frag]);
  }
  function msg(t, kind) { var m = $('g-msg'); if (!t) { m.className = 'alert d-none'; return; } m.className = 'alert alert-' + (kind || 'info'); m.textContent = t; }
  function bubble(html, who) { var d = document.createElement('div'); d.className = 'g-bubble ' + (who === 'me' ? 'g-me' : 'g-ai'); put(d, html); $('a-thread').appendChild(d); $('a-thread').scrollTop = 1e9; return d; }

  /** The model's light formatting (bold, bullets) as HTML, after escaping. */
  function md(t) { return esc(t).replace(/\*\*(.+?)\*\*/g, '<b>$1</b>').replace(/^\s*[-*] /gm, '• '); }

  function describe(c) {
    var a = c.args || {};
    switch (c.op) {
      case 'set_global': return 'Set <b>' + esc(a.name) + '</b> to “' + esc(a.value) + '”';
      case 'upsert_facility': return 'Facility <b>' + esc(a.name) + '</b>' + [a.street, a.city, a.state, a.facility_npi && 'NPI ' + a.facility_npi].filter(Boolean).map(function (x) { return ' · ' + esc(x); }).join('');
      case 'add_list_option': return 'Add “' + esc(a.title || a.option_id) + '” to list ' + esc(a.list_id);
      case 'update_list_option': return 'Change ' + esc(a.option_id) + ' in ' + esc(a.list_id) + (a.title ? ' to “' + esc(a.title) + '”' : '') + (a.active === false ? ' (hide)' : '');
      case 'upsert_fee': return esc(a.code_type || 'CPT4') + ' <b>' + esc(a.code) + '</b> ' + esc(a.description || '') + ' — $' + esc(a.price);
      case 'upsert_appt_category': return 'Visit type <b>' + esc(a.name) + '</b>, ' + esc(a.duration_minutes) + ' min';
      case 'set_form_enabled': return (a.enabled ? 'Turn on' : 'Turn off') + ' form <b>' + esc(a.directory) + '</b>';
      case 'create_appointment': return 'Book <b>' + esc(a.patient_name) + '</b> with ' + esc(a.provider_username) + ' on <b>' + esc(a.date) + ' ' + esc(a.time) + '</b>' + (a.category_name ? ' (' + esc(a.category_name) + ')' : '');
      default: return esc(c.op);
    }
  }

  function load() {
    api('assistant-state').then(function (j) {
      if (!j.ok) return msg(j.error, 'danger');
      state = j;
      $('a-role').textContent = 'Your access: ' + ({ admin: 'administrator — set up, daily tasks, how-to', clinician: 'daily tasks and how-to', scheduler: 'scheduling and how-to', viewer: 'how-to answers' }[j.role] || j.role);
      var connected = j.practiceConnected;
      $('a-connect').classList.toggle('d-none', connected);
      $('a-chat').classList.toggle('d-none', !connected);
      $('a-connect-text').textContent = j.role === 'admin' ? 'Connect your practice’s Grapheus account. Assistant requests are billed to it.' : 'An administrator needs to connect the practice’s Grapheus account before the Assistant can be used.';
      $('a-connect-btn').classList.toggle('d-none', j.role !== 'admin');
      $('a-admin').classList.toggle('d-none', j.role !== 'admin' || !connected);
      if (j.role === 'admin') paintLog(j.log || []);
      $('a-admins-only').checked = !!j.adminsOnly;
    });
  }

  $('a-connect-btn').onclick = function () {
    var w = window.open(state.server + '/app#connect-emr=' + encodeURIComponent(location.origin), 'grapheus-connect', 'width=520,height=680');
    if (!w) msg('Allow pop-ups for OpenEMR, then try again.', 'warning');
  };
  window.addEventListener('message', function (e) {
    if (!state || e.origin !== state.server || !e.data || e.data.type !== 'grapheus-connect') return;
    api('assistant-connect', { key: e.data.key }).then(function (j) { msg(j.ok ? 'Practice account connected.' : j.error, j.ok ? 'success' : 'danger'); load(); });
  });
  $('a-disconnect').onclick = function () { if (confirm('Disconnect the practice Grapheus account?')) api('assistant-disconnect', {}).then(load); };
  $('a-admins-only').onchange = function () { api('assistant-settings', { adminsOnly: $('a-admins-only').checked }); };

  $('a-form').onsubmit = function (e) {
    e.preventDefault();
    var text = $('a-input').value.trim();
    if (!text) return;
    $('a-input').value = '';
    bubble(esc(text), 'me');
    history.push({ role: 'user', content: text });
    var wait = bubble('<span class="text-muted">Thinking…</span>', 'ai');
    $('a-send').disabled = true;
    api('assistant-chat', { messages: history }).then(function (j) {
      $('a-send').disabled = false;
      if (!j.ok) { put(wait, '<span class="text-danger">' + esc(j.error) + '</span>'); history.pop(); return; }
      history.push({ role: 'assistant', content: j.reply + (j.changes && j.changes.length ? '\n[Proposed ' + j.changes.length + ' change(s).]' : '') });
      put(wait, md(j.reply) + (j.questions && j.questions.length ? '<ul class="mb-0 mt-2">' + j.questions.map(function (q) { return '<li>' + md(q) + '</li>'; }).join('') + '</ul>' : '') +
        (j.chargeCents != null ? '<div class="g-why mt-1">This request: $' + (Number(j.chargeCents) / 100).toFixed(2) + '</div>' : ''));
      if (j.changes && j.changes.length) plan(wait, j);
    });
  };

  function plan(container, j) {
    var groups = {};
    j.changes.forEach(function (c, i) { (groups[c.group || 'Changes'] = groups[c.group || 'Changes'] || []).push([c, i]); });
    var box = document.createElement('div');
    box.className = 'g-plan';
    put(box, Object.keys(groups).map(function (g) {
      return '<h6>' + esc(g) + '</h6>' + groups[g].map(function (p) {
        var c = p[0], i = p[1];
        return '<div class="g-change' + (c.allowed ? '' : ' g-no') + '"><input type="checkbox" data-i="' + i + '"' + (c.allowed ? ' checked' : ' disabled') + '><div>' + describe(c) +
          '<div class="g-why">' + esc(c.why) + (c.allowed ? '' : ' — needs an administrator') + '</div><div class="g-pick" data-pick="' + i + '"></div></div></div>';
      }).join('');
    }).join('') + '<button class="btn btn-sm btn-primary mt-2">Approve checked</button> <span class="small text-muted">You can undo afterwards.</span>');
    container.appendChild(box);
    // Appointments: find the patient here, in OpenEMR, and let the user choose.
    j.changes.forEach(function (c, i) {
      if (c.op !== 'create_appointment' || !c.allowed) return;
      var slot = box.querySelector('[data-pick="' + i + '"]');
      api('assistant-find-patient', { name: c.args.patient_name, dob: c.args.patient_dob || '' }).then(function (r) {
        var ps = r.patients || [];
        put(slot, ps.length ? ps.map(function (p, k) {
          return '<label class="d-block small mb-0"><input type="radio" name="pick' + i + '" value="' + esc(p.pid) + '"' + (ps.length === 1 && k === 0 ? ' checked' : '') + '> ' + esc(p.fname + ' ' + p.lname) + ' · DOB ' + esc(p.DOB) + '</label>';
        }).join('') : '<span class="text-danger small">No patient found with that name. Register them first, or say the full name.</span>');
        if (!ps.length) box.querySelector('[data-i="' + i + '"]').checked = false;
      });
    });
    box.querySelector('button').onclick = function () {
      var picked = [];
      var problem = '';
      box.querySelectorAll('input[data-i]:checked').forEach(function (cb) {
        var i = Number(cb.dataset.i), c = j.changes[i], item = { op: c.op, args: c.args };
        if (c.op === 'create_appointment') {
          var r = box.querySelector('input[name="pick' + i + '"]:checked');
          if (!r) { problem = 'Choose which patient for the appointment.'; return; }
          item.pid = Number(r.value);
        }
        picked.push(item);
      });
      if (problem) return msg(problem, 'warning');
      if (!picked.length) return;
      this.disabled = true;
      var btn = this;
      api('assistant-apply', { requestId: j.requestId, changes: picked }).then(function (r) {
        if (!r.ok) { btn.disabled = false; return msg(r.error, 'danger'); }
        var lines = r.results.map(function (x) { return (x.ok ? '✓ ' + esc(x.result) : '✗ ' + esc(x.error)); });
        bubble('<b>Done (' + r.applied + ' of ' + picked.length + ')</b>\n' + lines.join('\n'), 'ai');
        history.push({ role: 'assistant', content: 'Applied: ' + r.results.map(function (x) { return x.ok ? x.result : 'failed: ' + x.error; }).join('; ') });
        load();
      });
    };
  }

  function paintLog(rows) {
    put($('a-log'), '<tr><th>When</th><th>Who</th><th>Change</th><th></th></tr>' + rows.map(function (r) {
      var a = {}; try { a = JSON.parse(r.args) || {}; } catch (e) { /* ignore */ }
      return '<tr><td>' + esc(r.at) + '</td><td>' + esc(r.username || '') + '</td><td>' + describe({ op: r.op, args: a }) + '</td><td>' +
        (r.undone_at ? '<span class="text-muted">undone</span>' : '<button class="btn btn-sm btn-link p-0" data-undo="' + esc(r.id) + '">Undo</button>') + '</td></tr>';
    }).join(''));
    $('a-log').querySelectorAll('[data-undo]').forEach(function (b) {
      b.onclick = function () { api('assistant-undo', { logId: Number(b.dataset.undo) }).then(function (r) { msg(r.ok ? 'Undone.' : r.error, r.ok ? 'success' : 'danger'); load(); }); };
    });
  }

  load();
})();
