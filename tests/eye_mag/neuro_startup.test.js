const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.join(__dirname, '../../interface/forms/eye_mag');
const source = fs.readFileSync(path.join(root, 'js/neuro_patterns.js'), 'utf8');
const base = fs.readFileSync(path.join(root, 'js/eye_base.php'), 'utf8');
const start = base.indexOf('var prismGuidanceUpdater = null;');
const end = base.indexOf('$("#NEURO_ACT_zone").on', start);
const startup = base.slice(start, end).replace(/<\?php echo xlj\('([^']*)'\); \?>/g, (_, text) => JSON.stringify(text));

for (const moduleValue of [undefined, null, {exports: {otherLibrary: true}}]) {
    test('browser exports window API with module=' + JSON.stringify(moduleValue), () => {
        const events = [];
        const window = {Event: class {constructor(type) { this.type = type; }}, dispatchEvent: event => events.push(event.type)};
        const context = {window, module: moduleValue};
        vm.runInNewContext(source, context);
        assert.equal(typeof window.EyeNeuroPatterns.bind, 'function');
        assert.deepEqual(events, ['eye-neuro-patterns-ready']);
        if (moduleValue) assert.deepEqual(moduleValue.exports, {otherLibrary: true});
    });
}
test('Node/CommonJS export remains available without a browser', () => {
    const context = {module: {exports: {}}};
    vm.runInNewContext(source, context);
    assert.equal(typeof context.module.exports.bind, 'function');
});
function fixture(api) {
    const events = {};
    let message = '';
    let binds = 0;
    let updates = 0;
    const node = {empty() { message = ''; return this; }, append() { return this; }, text(value) {message = value; return this;}};
    const context = {window: {EyeNeuroPatterns: api, addEventListener: (name, callback) => {events[name] = callback;}}, $: () => node};
    const working = {bind() {binds++; message = ''; updates++; return () => {updates++;};}};
    return {context, events, working, message: () => message, binds: () => binds, updates: () => updates};
}
test('missing dependency shows a warning and initialization continues', () => {
    const f = fixture();
    vm.runInNewContext(startup + '\nwindow.remainingFormInitialized = true;', f.context);
    assert.equal(f.context.window.remainingFormInitialized, true);
    assert.match(f.message(), /guidance is unavailable/);
    assert.doesNotThrow(() => f.context.updatePrismPatternGuidance());
});
test('malformed API does not abort initialization', () => {
    const f = fixture({});
    assert.doesNotThrow(() => vm.runInNewContext(startup, f.context));
    assert.match(f.message(), /guidance is unavailable/);
});
test('late dependency recovers once and preserves subsequent refresh calls', () => {
    const f = fixture();
    vm.runInNewContext(startup, f.context);
    f.context.window.EyeNeuroPatterns = f.working;
    f.events['eye-neuro-patterns-ready']();
    assert.equal(f.binds(), 1);
    assert.equal(f.message(), '');
    f.events['eye-neuro-patterns-ready']();
    f.context.updatePrismPatternGuidance();
    assert.equal(f.binds(), 1);
    assert.equal(f.updates(), 3);
});
test('already-loaded dependency binds immediately, only once', () => {
    const f = fixture();
    f.context.window.EyeNeuroPatterns = f.working;
    vm.runInNewContext(startup, f.context);
    assert.equal(f.binds(), 1);
    f.context.updatePrismPatternGuidance();
    assert.equal(f.binds(), 1);
    assert.equal(f.updates(), 2);
});
