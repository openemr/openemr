'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.resolve(__dirname, '../../interface/forms/eye_mag/js/eye_base.php'), 'utf8');
const start = source.indexOf('function submit_canvas(');
const end = source.indexOf('// Provider defaults', start);
assert.ok(start >= 0 && end > start, 'loads the actual canvas save function');
const saveFunction = source.slice(start, end).replace(/<\?php[\s\S]*?\?>/g, JSON.stringify('The drawing could not be saved.'));
const deferred = () => { let resolve; const promise = new Promise(r => { resolve = r; }); return {promise, resolve}; };
const flush = () => new Promise(resolve => setImmediate(resolve));
const overlayJson = JSON.stringify([{className: 'Group', attrs: {eyeImageUrl: '/sites/fixture/images/eye_mag/providers/9/EXT/library/0123456789abcdef.png'}}]);

function fixture({readiness = () => Promise.resolve(), exported = () => Promise.resolve('composite-jpeg')} = {}) {
    const calls = {persist: [], alerts: [], exports: [], readiness: [], raster: []};
    const canvas = {toDataURL(...args) { calls.raster.push(args); return 'base-raster-only'; }};
    const context = {
        window: {
            eyeDrawingInkReady(zone) { calls.readiness.push(zone); return readiness(); },
            eyeDrawingOverlays: {EXT: {
                exportComposite(base) { calls.exports.push(base); return exported(); },
                getJSON() { return overlayJson; }
            }},
            alert(message) { calls.alerts.push(message); }
        },
        document: {getElementById(id) { return id === 'myCanvas_EXT' ? canvas : null; }},
        isEyeCanvasDirty: () => true,
        persist_canvas(...args) { calls.persist.push(args); }
    };
    vm.createContext(context);
    vm.runInContext(saveFunction, context);
    return {submit: () => context.submit_canvas('EXT', true), calls, canvas};
}

test('failed image hydration reports the export error without saving a base-only raster', async () => {
    const {submit, calls} = fixture({exported: () => Promise.reject(new Error('The saved drawing image could not be opened.'))});
    submit();
    await flush();
    assert.equal(calls.exports.length, 1);
    assert.deepEqual(calls.persist, []);
    assert.deepEqual(calls.raster, []);
    assert.deepEqual(calls.alerts, ['The saved drawing image could not be opened.']);
});

test('failed raster readiness reports an error before any export or persistence', async () => {
    const {submit, calls} = fixture({readiness: () => Promise.reject(new Error('The drawing history could not be restored.'))});
    submit();
    await flush();
    assert.deepEqual(calls.readiness, ['EXT']);
    assert.deepEqual(calls.exports, []);
    assert.deepEqual(calls.persist, []);
    assert.deepEqual(calls.raster, []);
    assert.deepEqual(calls.alerts, ['The drawing history could not be restored.']);
});

test('pending raster readiness delays export and persistence until the restored drawing is ready', async () => {
    const ready = deferred();
    const image = deferred();
    const {submit, calls, canvas} = fixture({readiness: () => ready.promise, exported: () => image.promise});
    submit();
    await flush();
    assert.deepEqual(calls.readiness, ['EXT']);
    assert.deepEqual(calls.exports, []);
    assert.deepEqual(calls.persist, []);
    ready.resolve();
    await flush();
    assert.deepEqual(calls.exports, [canvas]);
    assert.deepEqual(calls.persist, []);
    image.resolve('restored-composite');
    await flush();
    assert.deepEqual(calls.persist, [['EXT', 'restored-composite', overlayJson]]);
    assert.deepEqual(calls.readiness, ['EXT'], 'internal continuation checks readiness once');
    assert.deepEqual(calls.alerts, []);
});

test('a successful save persists the exported composite and its editable image JSON', async () => {
    const {submit, calls, canvas} = fixture({exported: () => Promise.resolve('complete-composite-jpeg')});
    submit();
    await flush();
    assert.deepEqual(calls.exports, [canvas]);
    assert.deepEqual(calls.persist, [['EXT', 'complete-composite-jpeg', overlayJson]]);
    assert.deepEqual(calls.raster, []);
    assert.deepEqual(calls.alerts, []);
});
