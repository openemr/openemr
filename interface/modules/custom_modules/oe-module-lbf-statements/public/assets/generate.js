(function () {
    const input = document.getElementById('patient_search');
    const pid = document.getElementById('pid');
    const list = document.getElementById('patient_results');
    const form = document.getElementById('lbf-stmt-pick');
    if (!input || !pid || !list || !form) {
        return;
    }
    const searchUrl = input.getAttribute('data-search-url') || '';
    const formId = input.getAttribute('data-form-id') || '';
    let timer = null;
    let lastItems = [];
    let picking = false;

    function hide() {
        list.style.display = 'none';
        list.classList.remove('show');
        list.innerHTML = '';
    }

    function showList() {
        list.style.display = 'block';
        list.classList.add('show');
    }

    function pick(item) {
        picking = true;
        pid.value = String(item.pid);
        input.value = item.name;
        const inst = document.getElementById('instance_id');
        if (inst) {
            inst.value = '0';
        }
        hide();
        form.submit();
    }

    function matchTypedName(items) {
        const q = input.value.trim().toLowerCase();
        if (!q || !items.length) {
            return null;
        }
        const exact = [];
        const prefix = [];
        items.forEach(function (item) {
            const name = String(item.name || '').toLowerCase();
            if (name === q) {
                exact.push(item);
            } else if (name.indexOf(q) === 0) {
                prefix.push(item);
            }
        });
        if (exact.length === 1) {
            return exact[0];
        }
        if (items.length === 1) {
            return items[0];
        }
        if (prefix.length === 1 && exact.length === 0) {
            return prefix[0];
        }
        return null;
    }

    function applyTypedName() {
        if (picking || (pid.value && pid.value !== '0')) {
            return;
        }
        const q = input.value.trim();
        if (!q) {
            return;
        }
        const local = matchTypedName(lastItems);
        if (local) {
            pick(local);
            return;
        }
        const url = searchUrl + '?ajax=patients&form_id=' + encodeURIComponent(formId)
            + '&q=' + encodeURIComponent(q);
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (items) {
                lastItems = Array.isArray(items) ? items : [];
                const hit = matchTypedName(lastItems);
                if (hit) {
                    pick(hit);
                }
            })
            .catch(function () { /* leave the field as typed */ });
    }

    function render(items) {
        lastItems = items;
        list.innerHTML = '';
        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'list-group-item text-muted';
            empty.textContent = 'No matching patients';
            list.appendChild(empty);
            showList();
            return;
        }
        items.forEach(function (item) {
            const li = document.createElement('button');
            li.type = 'button';
            li.className = 'list-group-item list-group-item-action';
            li.textContent = item.name + ' (' + item.pid + ')';
            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                pick(item);
            });
            list.appendChild(li);
        });
        showList();
    }

    input.addEventListener('input', function () {
        pid.value = '0';
        const q = input.value.trim();
        if (timer) {
            clearTimeout(timer);
        }
        if (q.length < 1) {
            hide();
            return;
        }
        timer = setTimeout(function () {
            const url = searchUrl + '?ajax=patients&form_id=' + encodeURIComponent(formId)
                + '&q=' + encodeURIComponent(q);
            fetch(url, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function () {
                    render([]);
                });
        }, 100);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyTypedName();
        }
    });

    input.addEventListener('blur', function () {
        window.setTimeout(function () {
            if (picking) {
                return;
            }
            applyTypedName();
        }, 120);
    });

    document.addEventListener('click', function (e) {
        if (!list.contains(e.target) && e.target !== input) {
            hide();
        }
    });
})();
