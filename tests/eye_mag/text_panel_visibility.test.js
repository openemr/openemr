'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.resolve(__dirname, '../../interface/forms/eye_mag/js/eye_base.php'), 'utf8');
const zones = ['HPI', 'PMH', 'EXT', 'ANTSEG', 'NEURO', 'RETINA', 'SDRETINA', 'IMPPLAN'];

function fixture() {
    const nodes = new Map();
    function $(selector) {
        if (!nodes.has(selector)) nodes.set(selector, {
            classes: new Set(), value: '',
            addClass(value) { this.classes.add(value); return this; },
            removeClass(value) { this.classes.delete(value); return this; },
            val(value) { if (value === undefined) return this.value; this.value = value; return this; }
        });
        return nodes.get(selector);
    }
    const context = {$, HPI_sync_heights() {}, update_PREFS() {},
        hide_QP() { $('#QP_IMPPLAN').addClass('nodisplay'); },
        hide_DRAW() { $('#Draw_IMPPLAN').addClass('nodisplay'); },
        hide_PRIORS() { $('#PRIORS_IMPPLAN_left_text').addClass('nodisplay'); }
    };
    vm.createContext(context);
    for (const name of ['show_right', 'hide_right', 'show_left', 'show_TEXT']) {
        const code = source.match(new RegExp('^function ' + name + '\\([^]*?^}', 'm'));
        assert.ok(code, name + ' must exist');
        vm.runInContext(code[0], context);
    }
    return {context, $};
}

test('hide_right hides every right panel, including Impression/Plan', () => {
    const {context, $} = fixture();
    context.hide_right();
    for (const zone of zones) assert.ok($('#' + zone + '_right').classes.has('nodisplay'), zone);
});
for (const persist of [true, false]) {
    test('TEXT keeps the Imp/Plan accordion as the only right panel, click/reload persist=' + persist, () => {
        const {context, $} = fixture();
        context.show_right();
        $('#IMPPLAN_left').addClass('nodisplay');
        $('#PREFS_EXAM').val('DRAW');
        context.show_TEXT(persist);
        for (const zone of zones.filter(zone => zone !== 'IMPPLAN')) {
            assert.ok($('#' + zone + '_right').classes.has('nodisplay'), zone);
        }
        assert.ok(!$('#IMPPLAN_right').classes.has('nodisplay'));
        assert.ok(!$('#IMPPLAN_left').classes.has('nodisplay'));
        assert.ok(!$('#QP_IMPPLAN').classes.has('nodisplay'));
        assert.ok($('#Draw_IMPPLAN').classes.has('nodisplay'));
        assert.ok($('#PRIORS_IMPPLAN_left_text').classes.has('nodisplay'));
        assert.equal($('#PREFS_EXAM').val(), 'TEXT');
    });
}
test('show_right can restore all panels after TEXT mode', () => {
    const {context, $} = fixture();
    context.show_TEXT();
    context.show_right();
    for (const zone of zones) assert.ok(!$('#' + zone + '_right').classes.has('nodisplay'), zone);
});
