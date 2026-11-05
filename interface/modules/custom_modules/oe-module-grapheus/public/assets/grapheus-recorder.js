/* Grapheus recorder window: microphone, 5-minute segments, pause, silence auto-stop, 90-minute cap. */
(function () {
  'use strict';
  var root = document.getElementById('rec');
  var API = root.dataset.api, CSRF = root.dataset.csrf;
  var $ = function (id) { return document.getElementById(id); };
  var SEG_MS = 5 * 60 * 1000, WARN = 180, STOP = 300, MAX_MIN = 90;
  var R = null;

  function api(action, body, qs, raw, type) {
    var init = { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': CSRF } };
    if (raw) { init.body = raw; init.headers['Content-Type'] = type; } else { init.headers['Content-Type'] = 'application/json'; init.body = JSON.stringify(body || {}); }
    return fetch(API + '?action=' + action + (qs || ''), init).then(function (r) { return r.json().catch(function () { return { ok: false, status: r.status }; }); });
  }
  function say(t) { $('msg').textContent = t; }
  function clock(s) { s = Math.max(0, Math.floor(s)); var h = Math.floor(s / 3600), m = Math.floor(s / 60) % 60; return (h ? h + ':' + String(m).padStart(2, '0') : m) + ':' + String(s % 60).padStart(2, '0'); }
  function mime() { var o = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4']; for (var i = 0; i < o.length; i++) if (MediaRecorder.isTypeSupported(o[i])) return o[i]; return ''; }

  function upload(blob, n) {
    if (!blob.size) return;
    R.uploads = R.uploads.then(function attempt(_, tries) {
      tries = tries || 0;
      return api('segment', null, '&visit=' + encodeURIComponent(R.id) + '&seq=' + n, blob, blob.type).then(function (j) {
        if (!j.ok && tries < 3) return new Promise(function (ok) { setTimeout(ok, 2000 * (tries + 1)); }).then(function () { return attempt(null, tries + 1); });
        if (!j.ok) R.failures++;
      }, function () { if (tries < 3) return new Promise(function (ok) { setTimeout(ok, 2000 * (tries + 1)); }).then(function () { return attempt(null, tries + 1); }); R.failures++; });
    });
  }
  function segment() {
    if (!R || R.stopping || R.pausedAt) return;
    var chunks = [], n = R.seq++, m = mime();
    var rec = new MediaRecorder(R.stream, m ? { mimeType: m, audioBitsPerSecond: 32000 } : undefined);
    rec.ondataavailable = function (e) { if (e.data && e.data.size) chunks.push(e.data); };
    rec.onstop = function () { upload(new Blob(chunks, { type: (rec.mimeType || m || 'audio/webm').split(';')[0] }), n); };
    rec.start(1000);
    R.cur = rec;
    clearTimeout(R.rotate);
    R.rotate = setTimeout(function () { if (R && R.cur === rec && rec.state !== 'inactive') { rec.stop(); segment(); } }, SEG_MS);
  }

  function start() {
    navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true } }).then(function (stream) {
      return api('start', { mode: root.dataset.mode, patientType: root.dataset.ptype, prepMin: Number(root.dataset.prep) || 0 }).then(function (j) {
        if (!j.ok) { stream.getTracks().forEach(function (t) { t.stop(); }); $('state').textContent = 'Not started'; return say(j.error || 'Could not start.'); }
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        var an = ctx.createAnalyser(); an.fftSize = 1024; ctx.createMediaStreamSource(stream).connect(an);
        R = { id: j.id, stream: stream, ctx: ctx, an: an, buf: new Float32Array(1024), seq: 0, uploads: Promise.resolve(), failures: 0, startedAt: Date.now(), pausedMs: 0, pausedAt: 0, silent: 0 };
        segment();
        $('pause').disabled = false; $('stop').disabled = false; $('state').textContent = 'Recording';
        R.timer = setInterval(tick, 500);
        window.addEventListener('beforeunload', guard);
      });
    }).catch(function (e) { $('state').textContent = 'Microphone blocked'; say('Allow the microphone for this site in the browser, then reopen the recorder. (' + (e.message || e.name) + ')'); });
  }
  function guard(e) { if (R && !R.stopping) { e.preventDefault(); e.returnValue = ''; } }

  function tick() {
    var now = Date.now(), secs = (now - R.startedAt - R.pausedMs - (R.pausedAt ? now - R.pausedAt : 0)) / 1000;
    $('timer').textContent = clock(secs);
    R.an.getFloatTimeDomainData(R.buf);
    var sum = 0; for (var i = 0; i < R.buf.length; i++) sum += R.buf[i] * R.buf[i];
    var rms = Math.sqrt(sum / R.buf.length);
    $('level').style.width = Math.min(100, Math.round(rms * 600)) + '%';
    if (R.pausedAt) return;
    R.silent = rms > 0.012 ? 0 : R.silent + 0.5;
    if (R.silent >= WARN) say('No one has spoken for ' + Math.floor(R.silent / 60) + ' minutes. Recording stops at 5.'); else if (R.silent === 0) say('Keep this window open. It stops by itself after 5 silent minutes or 90 minutes.');
    if (R.silent >= STOP) stop('auto: 5 minutes of silence');
    if (secs >= MAX_MIN * 60) stop('auto: 90-minute limit');
  }

  $('pause').onclick = function () {
    if (!R || R.stopping) return;
    if (!R.pausedAt) { R.pausedAt = Date.now(); clearTimeout(R.rotate); if (R.cur && R.cur.state !== 'inactive') R.cur.stop(); $('pause').textContent = 'Resume'; $('state').textContent = 'Paused'; $('dot').classList.add('paused'); }
    else { R.pausedMs += Date.now() - R.pausedAt; R.pausedAt = 0; R.silent = 0; segment(); $('pause').textContent = 'Pause'; $('state').textContent = 'Recording'; $('dot').classList.remove('paused'); }
  };
  $('stop').onclick = function () { stop('stopped'); };

  function stop(reason) {
    if (!R || R.stopping) return;
    R.stopping = true;
    clearInterval(R.timer); clearTimeout(R.rotate);
    if (R.pausedAt) { R.pausedMs += Date.now() - R.pausedAt; R.pausedAt = 0; }
    $('pause').disabled = true; $('stop').disabled = true; $('state').textContent = 'Saving…';
    var done = function () {
      R.stream.getTracks().forEach(function (t) { t.stop(); });
      try { R.ctx.close(); } catch (e) { /* closed */ }
      R.uploads.then(function () {
        return api('finish', { visit: R.id, pausedSecs: Math.round(R.pausedMs / 1000), prepMin: Number(root.dataset.prep) || 0, tz: Intl.DateTimeFormat().resolvedOptions().timeZone, reason: reason, patientType: root.dataset.ptype });
      }).then(function (j) {
        window.removeEventListener('beforeunload', guard);
        $('state').textContent = j.ok ? 'Done' : 'Problem';
        $('dot').classList.add('paused');
        say(j.ok ? 'The draft is being written. Review it in the encounter’s Grapheus tab. You can close this window.' + (R.failures ? ' (' + R.failures + ' part(s) did not upload.)' : '') : (j.error || 'Could not finish.'));
        try { if (window.opener) window.opener.postMessage({ type: 'grapheus-finished', visit: j.ok ? R.id : null }, location.origin); } catch (e) { /* opener gone */ }
      });
    };
    if (R.cur && R.cur.state !== 'inactive') { R.cur.addEventListener('stop', function () { setTimeout(done, 50); }, { once: true }); R.cur.stop(); } else done();
  }

  start();
})();
