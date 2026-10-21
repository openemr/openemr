(function () {
  var cfg = window.lbfStatementsToolbar;
  if (!cfg || !Array.isArray(cfg.items)) {
    return;
  }
  var label = cfg.label || '';
  cfg.items.forEach(function (item) {
    var holder = document.getElementById(item.holder);
    if (!holder) {
      return;
    }
    var bar = holder.querySelector('.form_header_controls');
    if (!bar || bar.querySelector('.lbf-stmt-btn')) {
      return;
    }
    var a = document.createElement('a');
    a.className = 'btn btn-text btn-sm lbf-stmt-btn';
    a.href = item.url;
    a.title = label;
    a.textContent = label;
    a.addEventListener('click', function (e) {
      if (top.restoreSession) {
        top.restoreSession();
      }
      if (typeof top.navigateTab === 'function') {
        e.preventDefault();
        top.navigateTab(item.url, 'mod', function () {
          if (typeof top.activateTabByName === 'function') {
            top.activateTabByName('mod', true);
          }
        });
      }
    });
    bar.appendChild(a);
  });
})();
