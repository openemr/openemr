document.addEventListener('DOMContentLoaded', function () {
  var sel = Array.prototype.find.call(document.querySelectorAll('select'), function (s) {
    return s.querySelector('option[value="hash9"]') && s.querySelector('option[value="custom"]');
  });
  var panel = document.getElementById('stmt-env-custom');
  if (!sel || !panel) { return; }
  var row = sel.closest('.row.form-group');
  if (!row) { return; }
  var hideRows = [];
  var unitsSel = null;
  var texts = [];
  var el = row.nextElementSibling;
  while (el && texts.length < 9) {
    var next = el.nextElementSibling;
    if (el.id === 'stmt-env-custom' || (el.querySelector && el.querySelector('#stmt-env-custom'))) {
      el = next;
      continue;
    }
    if (el.classList && el.classList.contains('form-group')) {
      var u = el.querySelector('select');
      if (u && u.querySelector('option[value="in"]') && u.querySelector('option[value="cm"]')) {
        unitsSel = u;
        hideRows.push(el);
      } else {
        var inp = el.querySelector('input[type="text"]');
        if (inp) {
          texts.push(inp);
          hideRows.push(el);
        }
      }
    }
    el = next;
  }
  function parseMeasure(s) {
    s = String(s).trim();
    var m = s.match(/^(\d+)\s*-\s*(\d+)\s*\/\s*(\d+)$/);
    if (m) { return +m[1] + (+m[2] / +m[3]); }
    m = s.match(/^(\d+)\s+(\d+)\s*\/\s*(\d+)$/);
    if (m) { return +m[1] + (+m[2] / +m[3]); }
    m = s.match(/^(\d+)\s*\/\s*(\d+)$/);
    if (m) { return +m[1] / +m[2]; }
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
  var slots = [
    'stmt-env-slot-height',
    'stmt-env-slot-rh', 'stmt-env-slot-rw', 'stmt-env-slot-rl', 'stmt-env-slot-rb',
    'stmt-env-slot-th', 'stmt-env-slot-tw', 'stmt-env-slot-tl', 'stmt-env-slot-tb'
  ];
  if (texts.length !== 9) { return; }
  slots.forEach(function (id, i) {
    var slot = document.getElementById(id);
    if (!slot) { return; }
    texts[i].classList.add('form-control-sm');
    slot.appendChild(texts[i]);
  });
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
