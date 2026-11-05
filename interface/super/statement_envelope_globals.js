document.addEventListener('DOMContentLoaded', function () {
  var fieldByKey = function (key) {
    return document.querySelector('[data-global="' + key + '"]');
  };
  var sel = fieldByKey('statement_envelope');
  var panel = document.getElementById('stmt-env-custom');
  if (!sel || !panel) { return; }
  var row = sel.closest('.row.form-group');
  if (!row) { return; }
  var fieldIds = [
    ['statement_env_height', 'stmt-env-slot-height'],
    ['statement_env_return_height', 'stmt-env-slot-rh'],
    ['statement_env_return_width', 'stmt-env-slot-rw'],
    ['statement_env_return_left', 'stmt-env-slot-rl'],
    ['statement_env_return_bottom', 'stmt-env-slot-rb'],
    ['statement_env_to_height', 'stmt-env-slot-th'],
    ['statement_env_to_width', 'stmt-env-slot-tw'],
    ['statement_env_to_left', 'stmt-env-slot-tl'],
    ['statement_env_to_bottom', 'stmt-env-slot-tb']
  ];
  var hideRows = [];
  var texts = [];
  fieldIds.forEach(function (pair) {
    var inp = fieldByKey(pair[0]);
    var slot = document.getElementById(pair[1]);
    if (!inp || !slot) { return; }
    var fieldRow = inp.closest('.row.form-group') || inp.closest('.form-group');
    if (fieldRow) { hideRows.push(fieldRow); }
    texts.push(inp);
    inp.classList.add('form-control-sm');
    slot.appendChild(inp);
  });
  var unitsSel = fieldByKey('statement_env_units');
  if (unitsSel) {
    var unitsRow = unitsSel.closest('.row.form-group') || unitsSel.closest('.form-group');
    if (unitsRow) { hideRows.push(unitsRow); }
  }
  function parseMeasure(s) {
    s = String(s).trim();
    var m = s.match(/^(\d+)\s*-\s*(\d+)\s*\/\s*(\d+)$/);
    if (m) {
      var mixedDen = +m[3];
      return mixedDen === 0 ? null : +m[1] + (+m[2] / mixedDen);
    }
    m = s.match(/^(\d+)\s+(\d+)\s*\/\s*(\d+)$/);
    if (m) {
      var spacedDen = +m[3];
      return spacedDen === 0 ? null : +m[1] + (+m[2] / spacedDen);
    }
    m = s.match(/^(\d+)\s*\/\s*(\d+)$/);
    if (m) {
      var den = +m[2];
      return den === 0 ? null : +m[1] / den;
    }
    if (s !== '' && isFinite(s)) { return +s; }
    return null;
  }
  function toCm(inches) { return +(inches * 127 / 50).toFixed(4); }
  function toIn(cm) { return +(cm * 50 / 127).toFixed(6); }
  function convertFields(from, to) {
    if (from === to) { return; }
    texts.forEach(function (inp) {
      var n = parseMeasure(inp.value);
      if (n === null) { return; }
      inp.value = (to === 'cm') ? String(toCm(n)) : String(toIn(n));
    });
  }
  if (texts.length !== 9) { return; }
  hideRows.forEach(function (r) { r.style.display = 'none'; });
  var helpRow = panel.closest('.row.form-group');
  row.after(panel);
  if (helpRow && helpRow !== row) { helpRow.style.display = 'none'; }
  var startUnit = (unitsSel && unitsSel.value === 'cm') ? 'cm' : 'in';
  var radio = panel.querySelector('input[name="stmt_env_units_ui"][value="' + startUnit + '"]');
  if (radio) { radio.checked = true; }
  panel.querySelectorAll('input[name="stmt_env_units_ui"]').forEach(function (r) {
    r.addEventListener('change', function () {
      var next = r.value === 'cm' ? 'cm' : 'in';
      var prev = (unitsSel && unitsSel.value === 'cm') ? 'cm' : 'in';
      convertFields(prev, next);
      if (unitsSel) { unitsSel.value = next; }
    });
  });
  function sync() {
    var show = sel.value === 'custom';
    panel.style.display = show ? '' : 'none';
    hideRows.forEach(function (r) { r.style.display = 'none'; });
  }
  sel.addEventListener('change', sync);
  sync();
});
