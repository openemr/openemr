'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../../interface/forms/eye_mag');
const source = fs.readFileSync(path.join(root, 'js/eye_base.php'), 'utf8');
const start = source.indexOf('function show_PRIOR_CANVAS_section(');
const end = source.indexOf('/**', source.indexOf('function replace_CANVAS(', start));
const functions = source.slice(start, end).replace(/<\?php[\s\S]*?\?>/g, 'Translated');

for (const zone of ['HPI','PMH','EXT','ANTSEG','RETINA','NEURO','IMPPLAN','SDRETINA']) {
    test(zone + ': prior/current/prior/copy keeps navigation and preserves current canvas', () => {
        const nodes = new Map();
        const copies = [];
        function $(id) {
            if (!nodes.has(id)) nodes.set(id, {
                classes: new Set(), value: '', content: '',
                addClass(c) { this.classes.add(c); return this; },
                removeClass(c) { this.classes.delete(c); return this; },
                html(h) { this.content = h; return this; },
                val(v) { if (v === undefined) return this.value; this.value = v; return this; }
            });
            return nodes.get(id);
        }
        $('#pid').val('1');
        $('#url_' + zone).val('current-image');
        $('#' + zone + '_KONVA_JSON').val('current-overlay');
        $('#SELECT_CANVAS_' + zone).val('123');
        const context = {$, URLSearchParams, base:'', cReplace: z => copies.push(z)};
        vm.createContext(context);
        vm.runInContext(functions, context);
        for (const documentId of ['123', '99']) {
            context.show_PRIOR_CANVAS_section(zone, documentId);
            assert.ok($('#Draw_' + zone).classes.has('is-viewing-prior'));
            assert.ok($('#' + zone + '_canvas').classes.has('nodisplay'));
            assert.ok(!$('#' + zone + '_olddrawing').classes.has('nodisplay'));
            assert.match($('#' + zone + '_olddrawing').content, new RegExp('document_id=' + documentId));
            assert.ok(!$('#SELECT_CANVAS_' + zone).classes.has('nodisplay'));
        }
        context.show_CURRENT_CANVAS_section(zone);
        assert.ok(!$('#Draw_' + zone).classes.has('is-viewing-prior'));
        assert.ok(!$('#' + zone + '_canvas').classes.has('nodisplay'));
        assert.ok($('#' + zone + '_olddrawing').classes.has('nodisplay'));
        assert.equal($('#url_' + zone).val(), 'current-image');
        assert.equal($('#' + zone + '_KONVA_JSON').val(), 'current-overlay');
        assert.deepEqual(copies, []);
        context.show_PRIOR_CANVAS_section(zone, '99');
        context.replace_CANVAS(zone, 'selected-prior');
        assert.deepEqual(copies, [zone]);
        assert.equal($('#url_' + zone).val(), 'selected-prior');
        assert.equal($('#SELECT_CANVAS_' + zone).val(), 'current');
        assert.ok(!$('#Draw_' + zone).classes.has('is-viewing-prior'));
        assert.ok(!$('#' + zone + '_canvas').classes.has('nodisplay'));
    });
}
test('selector returns to current through the shared non-destructive path', () => {
    const handler = source.slice(source.indexOf('$("[name=\'CANVAS_selector\']").on'), source.indexOf("$('#IMP').on(\"blur\""));
    assert.match(handler, /var zone = this.id.match/);
    assert.match(handler, /show_CURRENT_CANVAS_section\(zone\)/);
    assert.doesNotMatch(handler, /cReplace|drawImage|cReload|submit_canvas/);
    const css = fs.readFileSync(path.join(root, 'css/style.css'), 'utf8');
    assert.match(css, /\.Draw_class\.is-viewing-prior \.eye-konva-tools\s*\{\s*visibility: hidden;/);
});
