'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.resolve(__dirname, '../../interface/forms/eye_mag/js/canvasdraw.js'), 'utf8');
const functions = source.slice(source.indexOf('function drawImage('), source.indexOf('\nfunction Draw('))
    + source.slice(source.indexOf('function cReload('), source.indexOf('\nfunction cReplace('));

function fixture(zone = 'EXT', instantLoad = false) {
    const canvas = {width: zone === 'SDRETINA' ? 1000 : 450, height: zone === 'SDRETINA' ? 500 : 250};
    const values = new Map([['#url_' + zone, '/old.jpg'], ['#base_url_' + zone, '/base.jpg']]);
    const handlers = new WeakMap();
    const images = [];
    const draws = [];
    const labels = [];
    const events = [];
    const sequence = [];
    let dirty = 0;

    function Image() {
        this.complete = false;
        this.naturalWidth = canvas.width;
        this.naturalHeight = canvas.height;
        images.push(this);
    }
    Object.defineProperty(Image.prototype, 'src', {
        get() { return this.source; },
        set(value) {
            this.source = value;
            assert.equal(typeof handlers.get(this), 'function', 'load handler must exist before the request begins');
            if (instantLoad) {
                this.complete = true;
                handlers.get(this)();
            }
        }
    });
    function $(subject) {
        return {
            val(value) {
                if (arguments.length) {
                    values.set(subject, value);
                    return this;
                }
                return values.get(subject);
            },
            on(event, callback) { handlers.set(subject, callback); return this; },
            trigger(event) { events.push(event); sequence.push('event'); return this; }
        };
    }
    const drawingContext = {
        save() {}, restore() {}, fillRect() {},
        drawImage(...args) { draws.push(args); sequence.push('draw'); },
        fillText(...args) { labels.push(args); }
    };
    const context = {
        $, Image, image: {}, ctx: {[zone]: drawingContext},
        eyeCanvasRestoreGeneration: {}, eyeCanvasRestoring: {}, eyeCanvasRestoreError: {}, eyeCanvasReady: {}, eyeCanvasFinishLoading: {},
        document: {getElementById() { return canvas; }},
        cPush() { sequence.push('history'); },
        markEyeCanvasDirty() { dirty++; },
        resetEyeDrawingInk() {}
    };
    vm.createContext(context);
    vm.runInContext(functions, context);
    return {
        context, canvas, values, images, draws, labels, events, sequence,
        get dirty() { return dirty; },
        load(image) { image.complete = true; handlers.get(image)(); }
    };
}

function preparedImage(width = 450, height = 250, source = '/prepared.jpg') {
    return {complete: true, naturalWidth: width, naturalHeight: height, get src() { return source; }};
}

test('a preloaded background renders synchronously without constructing or reloading an image', () => {
    const f = fixture();
    const image = preparedImage();
    assert.equal(f.context.drawImage('EXT', image), true);
    assert.equal(f.context.image.EXT, image);
    assert.equal(f.draws[0][0], image);
    assert.deepEqual(f.sequence, ['draw', 'history', 'event']);
    assert.deepEqual(f.events, ['eye:image-loaded']);
    assert.equal(f.images.length, 0);
});

test('an earlier image load cannot paint or trigger a save after a newer request owns the canvas', () => {
    const f = fixture();
    f.context.drawImage('EXT');
    const old = f.images[0];
    f.values.set('#url_EXT', '/new.jpg');
    f.context.drawImage('EXT');
    const current = f.images[1];
    f.load(old);
    assert.equal(f.draws.length, 0);
    assert.equal(f.events.length, 0);
    f.load(current);
    assert.equal(f.draws[0][0], current);
    assert.equal(f.draws[0][0].src, '/new.jpg');
    assert.deepEqual(f.events, ['eye:image-loaded']);
});

test('a prepared image also invalidates an already pending older load', () => {
    const f = fixture();
    f.context.drawImage('EXT');
    const old = f.images[0];
    const current = preparedImage();
    f.context.drawImage('EXT', current);
    f.load(old);
    assert.equal(f.draws.length, 1);
    assert.equal(f.draws[0][0], current);
    assert.equal(f.events.length, 1);
});

test('an immediately cached image load is observed because its handler is installed first', () => {
    const f = fixture('EXT', true);
    assert.equal(f.context.drawImage('EXT'), true);
    assert.equal(f.draws[0][0], f.images[0]);
    assert.deepEqual(f.events, ['eye:image-loaded']);
});

test('incomplete or dimensionless prepared images preserve the current image and canvas', () => {
    const f = fixture();
    const current = preparedImage();
    f.context.drawImage('EXT', current);
    for (const invalid of [
        {complete: false, naturalWidth: 450, naturalHeight: 250},
        {complete: true, naturalWidth: 0, naturalHeight: 250},
        {complete: true, naturalWidth: 450, naturalHeight: 0},
        {complete: true}
    ]) {
        assert.equal(f.context.drawImage('EXT', invalid), false);
        assert.equal(f.context.image.EXT, current);
    }
    assert.equal(f.draws.length, 1);
    assert.equal(f.events.length, 1);
});

test('New forwards its prepared image synchronously, and rejects a failed preload before changing state', () => {
    const f = fixture();
    const image = preparedImage();
    assert.equal(f.context.cReload('EXT', image), true);
    assert.equal(f.values.get('#url_EXT'), '/base.jpg');
    assert.equal(f.context.image.EXT, image);
    assert.equal(f.draws[0][0], image);
    assert.equal(f.images.length, 0);
    assert.equal(f.dirty, 1);
    f.values.set('#url_EXT', '/unchanged.jpg');
    assert.equal(f.context.cReload('EXT', {complete: false}), false);
    assert.equal(f.values.get('#url_EXT'), '/unchanged.jpg');
    assert.equal(f.dirty, 1);
    assert.equal(f.draws.length, 1);
});

test('a stock image with different dimensions fits inside the canvas without stretching', () => {
    const f = fixture();
    const image = preparedImage(1000, 500);
    f.context.drawImage('EXT', image);
    assert.deepEqual(f.draws[0], [image, 0, 12.5, 450, 225]);
});

test('the prepared ROP background retains its cropped retinal diagram and eye labels', () => {
    const f = fixture('SDRETINA');
    f.values.set('#current_background_SDRETINA', 'retina');
    const image = preparedImage(450, 250, '/OU_RETINA_BASE.jpg');
    f.context.drawImage('SDRETINA', image);
    assert.equal(f.draws[0][0], image);
    assert.deepEqual(f.draws[0].slice(1, 5), [0, 0, 450, 202]);
    assert.deepEqual(f.labels.map(label => label[0]), ['OD', 'OS']);
    assert.equal(f.draws[0][7], 1000);
    assert.ok(f.draws[0][8] < 500);
});
