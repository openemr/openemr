'use strict';
const assert = require('node:assert/strict');
const path = require('node:path');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const form = path.resolve(__dirname, '../../interface/forms/eye_mag');
const konva = process.env.EYE_TEST_KONVA || path.resolve(form, '../../../public/assets/konva/konva.js');
const imageUrl = '/sites/fixture/images/eye_mag/providers/9/EXT/library/0123456789abcdef.png';
const deferred = () => { let resolve; const promise = new Promise(r => { resolve = r; }); return {promise, resolve}; };

(async () => {
    const browser = await chromium.launch({headless: true, ...(process.env.EYE_TEST_CHROMIUM ? {executablePath: process.env.EYE_TEST_CHROMIUM} : {})});
    try {
        const maker = await browser.newPage();
        const png = Buffer.from(await maker.evaluate(() => {
            const canvas = document.createElement('canvas');
            canvas.width = 40; canvas.height = 20;
            const context = canvas.getContext('2d');
            context.fillStyle = '#ff0000'; context.fillRect(10, 5, 20, 10);
            return canvas.toDataURL('image/png').split(',')[1];
        }), 'base64');
        await maker.close();
        const pages = [];
        async function fixture(initial = '[]', gate = null, fail = false) {
            const page = await browser.newPage();
            pages.push(page);
            const errors = [], requests = [], requested = deferred();
            page.on('pageerror', error => errors.push(error.message));
            await page.route('**/*', async route => {
                const url = new URL(route.request().url());
                if (url.pathname === '/fixture') {
                    return route.fulfill({contentType: 'text/html', body: `<!doctype html>
                        <div class="eye-drawing-stage" data-structured-background="1">
                        <canvas id="myCanvas_EXT" width="450" height="250"></canvas><div id="stage_EXT" class="eye-konva-stage" data-zone="EXT"></div></div>
                        <input id="EXT_KONVA_JSON"><input id="selColor_EXT" value="#18324f">
                        <div id="EXT_KONVA_TOOLS" data-tool="select"><button data-konva-action="transparency">Transparency</button><button data-konva-add="ellipse">Ellipse</button></div>
                        <div class="eye-drawing-stage" data-structured-background="1">
                        <canvas id="myCanvas_NEURO" width="450" height="250"></canvas><div id="stage_NEURO" class="eye-konva-stage" data-zone="NEURO"></div>
                        <svg id="NEURO_structured_overlay" xmlns="http://www.w3.org/2000/svg"><rect width="8" height="8" fill="#00ff00"/></svg></div>
                        <input id="NEURO_KONVA_JSON" value="[]"><div id="NEURO_KONVA_TOOLS" data-tool="select"></div>`});
                }
                requests.push(url.pathname);
                if (url.pathname === imageUrl || url.pathname === '/openemr' + imageUrl.replace('/EXT/', '/HPI/')) {
                    requested.resolve();
                    if (gate) await gate.promise;
                    if (fail) return route.abort();
                    return route.fulfill({contentType: 'image/png', body: png});
                }
                return route.abort();
            });
            await page.goto('http://eye.test/fixture');
            await page.evaluate(json => {
                document.getElementById('EXT_KONVA_JSON').value = json;
                window.fixtureSaves = [];
                window.submit_canvas = zone => fixtureSaves.push(zone);
                window.restoreSession = () => {};
                window.fixturePixel = (canvas, x, y) => Array.from(canvas.getContext('2d').getImageData(x, y, 1, 1).data);
                for (const zone of ['EXT', 'NEURO']) {
                    const context = document.getElementById('myCanvas_' + zone).getContext('2d');
                    context.fillStyle = '#0000ff'; context.fillRect(0, 0, 450, 250);
                }
            }, initial);
            await page.addScriptTag({path: path.join(form, 'js/jquery-1-10-2/jquery.min.js')});
            await page.addScriptTag({path: konva});
            await page.addScriptTag({path: path.join(form, 'js/konva_overlay.js')});
            await page.waitForFunction(() => window.eyeDrawingOverlays && eyeDrawingOverlays.EXT);
            return {page, errors, requests, requested};
        }

        const first = await fixture();
        const result = await first.page.evaluate(async url => {
            const api = eyeDrawingOverlays.EXT;
            const image = await api.insertImage({url, key: 'drawing-0123456789abcdef', name: 'Red mark'});
            window.insertedImage = image;
            const clear = await api.exportReusable({selectedOnly: true});
            const before = {size: [clear.width, clear.height], corner: fixturePixel(clear, 0, 0), center: fixturePixel(clear, 20, 10)};
            $('#EXT_KONVA_TOOLS [data-konva-action="transparency"]').click();
            const opaque = await api.exportReusable({selectedOnly: true});
            const opaquePixel = fixturePixel(opaque, 0, 0);
            const opacities = [image.opacity(), image.findOne('.eye-image-content').opacity()];
            image.position({x: 31, y: 42}); image.scale({x: 1.5, y: 2}); image.rotation(30); image.fire('transformend');
            const bounds = image.getClientRect({relativeTo: image.getLayer()});
            const selected = await api.exportReusable({selectedOnly: true});
            const expectedSize = [Math.ceil(bounds.x + bounds.width) - Math.floor(bounds.x), Math.ceil(bounds.y + bounds.height) - Math.floor(bounds.y)];
            const whole = await api.exportReusable();
            const composite = await api.exportCompositeCanvas();
            const neuro = await eyeDrawingOverlays.NEURO.exportCompositeCanvas();
            const neuroClear = await eyeDrawingOverlays.NEURO.exportReusable();
            return {before, opaquePixel, opacities, crop: [selected.width, selected.height], expectedSize,
                full: [whole.width, whole.height], corner: fixturePixel(whole, 0, 0),
                composite: fixturePixel(composite, 0, 0), neuro: fixturePixel(neuro, 2, 2), neuroClear: fixturePixel(neuroClear, 2, 2),
                json: api.getJSON(), selected: api.hasSelected(), content: api.hasContent()};
        }, imageUrl);
        assert.deepEqual(result.before, {size: [40, 20], corner: [0, 0, 0, 0], center: [255, 0, 0, 255]});
        assert.deepEqual(result.opaquePixel, [255, 255, 255, 255]);
        assert.deepEqual(result.opacities, [1, 1]);
        assert.deepEqual(result.crop, result.expectedSize);
        assert.deepEqual(result.full, [450, 250]);
        assert.deepEqual(result.corner, [0, 0, 0, 0]);
        assert.deepEqual(result.composite, [0, 0, 255, 255]);
        assert.deepEqual(result.neuro, [0, 255, 0, 255]);
        assert.deepEqual(result.neuroClear, [0, 0, 0, 0]);
        assert.equal(result.selected, true); assert.equal(result.content, true);
        const attrs = JSON.parse(result.json)[0].attrs;
        assert.equal(attrs.eyeImageUrl, imageUrl); assert.equal(attrs.eyeImageOpaque, true);
        assert.equal(attrs.eyeImageName, 'Red mark'); assert.equal(attrs.rotation, 30);
        assert.equal(attrs.scaleX, 1.5); assert.equal(attrs.scaleY, 2);
        assert.equal(result.json.includes('data:image'), false);
        console.log('PASS real Konva image insertion, white backing, transforms, transparent/cropped exports and Neuro composite');

        const restoredGate = deferred();
        const restored = await fixture(result.json, restoredGate);
        await restored.requested.promise;
        await restored.page.evaluate(() => {
            window.exportComplete = false;
            window.reopenExport = eyeDrawingOverlays.EXT.exportReusable().then(canvas => {
                window.exportComplete = true;
                return canvas.toDataURL();
            });
        });
        assert.equal(await restored.page.evaluate(() => exportComplete), false);
        restoredGate.resolve();
        await restored.page.evaluate(() => reopenExport);
        const reopened = await restored.page.evaluate(async () => {
            await eyeDrawingOverlays.EXT.ready();
            const api = eyeDrawingOverlays.EXT;
            const image = Konva.stages.find(stage => stage.container().id === 'stage_EXT').findOne('.eye-overlay-object');
            image.fire('click', {target: image}, true);
            const canvas = await api.exportReusable({selectedOnly: true});
            return {json: api.getJSON(), selected: api.hasSelected(), size: [canvas.width, canvas.height]};
        });
        assert.deepEqual(JSON.parse(reopened.json), JSON.parse(result.json));
        assert.equal(reopened.selected, true); assert.deepEqual(reopened.size, result.crop);
        console.log('PASS reopening restores URL, white backing and exact transforms; export waits for delayed image hydration');

        const cancelledGate = deferred();
        const cancelled = await fixture('[]', cancelledGate);
        await cancelled.page.evaluate(url => { window.insertion = eyeDrawingOverlays.EXT.insertImage({url}); }, imageUrl);
        await cancelled.requested.promise;
        const cleared = await cancelled.page.evaluate(async () => {
            const api = eyeDrawingOverlays.EXT; api.clear();
            const insertion = await window.insertion;
            await api.ready();
            const canvas = await api.exportReusable();
            return {cancelled: insertion === null, json: api.getJSON(), selected: api.hasSelected(), content: api.hasContent(), pixel: fixturePixel(canvas, 225, 125)};
        });
        assert.deepEqual(cleared, {cancelled: true, json: '[]', selected: false, content: false, pixel: [0, 0, 0, 0]});
        cancelledGate.resolve();
        assert.equal(await cancelled.page.evaluate(() => eyeDrawingOverlays.EXT.getJSON()), '[]');
        console.log('PASS clearing a pending insertion cancels hydration and does not resurrect the image');

        const failed = await fixture(result.json, null, true);
        const failure = await failed.page.evaluate(async () => {
            try { await eyeDrawingOverlays.EXT.exportComposite(); return false; }
            catch (error) { return error.message; }
        });
        assert.match(failure, /could not be opened/);
        const invalid = await fixture();
        for (const url of ['https://remote.test/image.png', 'data:image/png;base64,AAAA', '/controller.php?document&retrieve=1', imageUrl + '?cache=1', '/sites/fixture/images/eye_mag/providers/9/EXT/library/short.png']) {
            const rejected = await invalid.page.evaluate(async url => {
                try { await eyeDrawingOverlays.EXT.insertImage({url}); return false; }
                catch (error) { return true; }
            }, url);
            assert.equal(rejected, true, 'rejects unstable source ' + url);
        }
        assert.equal(await invalid.page.evaluate(() => eyeDrawingOverlays.EXT.getJSON()), '[]');
        assert.deepEqual(invalid.requests, []);
        console.log('PASS failed hydration blocks export; unstable, document and remote sources rejected before requests');

        await first.page.evaluate(() => {
            const modal = document.createElement('dialog');
            modal.className = 'eye-image-modal';
            modal.innerHTML = '<button>Close</button>';
            document.body.appendChild(modal); modal.showModal(); modal.querySelector('button').focus();
        });
        await first.page.keyboard.press('Backspace');
        await first.page.keyboard.press('Control+z');
        assert.equal(await first.page.evaluate(() => eyeDrawingOverlays.EXT.getJSON()), result.json);
        await first.page.evaluate(() => document.querySelector('.eye-image-modal').close());
        console.log('PASS native image modal prevents Delete/undo changing the selected drawing behind it');

        const clearedMultiple = await first.page.evaluate(async url => {
            const api = eyeDrawingOverlays.EXT;
            await api.insertImage({url});
            const before = JSON.parse(api.getJSON()).length;
            api.clear();
            return {before, json: api.getJSON()};
        }, imageUrl);
        assert.deepEqual(clearedMultiple, {before: 2, json: '[]'});
        console.log('PASS New/Clear removes every image without skipping children during destruction');

        const prefixed = await fixture();
        const prefixedUrl = '/openemr' + imageUrl.replace('/EXT/', '/HPI/');
        assert.equal(await prefixed.page.evaluate(async url => {
            const api = eyeDrawingOverlays.EXT;
            await api.insertImage({url});
            return JSON.parse(api.getJSON())[0].attrs.eyeImageUrl;
        }, prefixedUrl), prefixedUrl);
        assert.deepEqual(prefixed.errors, []);
        console.log('PASS subdirectory OpenEMR installations and all server-supported drawing zones accepted');

        const history = await fixture();
        const historyResult = await history.page.evaluate(async url => {
            const api = eyeDrawingOverlays.EXT;
            await api.insertImage({url});
            if (api.undo) {
                api.undo(); const undone = api.getJSON();
                api.redo(); await api.ready();
                return {hasHistory: true, undone, restored: JSON.parse(api.getJSON())[0].attrs.eyeImageUrl};
            }
            return {hasHistory: false};
        }, imageUrl);
        if (historyResult.hasHistory) {
            assert.equal(historyResult.undone, '[]'); assert.equal(historyResult.restored, imageUrl);
            console.log('PASS 841 first undo removes insertion and redo restores its hydrated image');
        } else {
            console.log('PASS 830 retains its existing API without adding undo/redo');
        }
        for (const fixturePage of [first, restored, cancelled, failed, invalid, history]) assert.deepEqual(fixturePage.errors, []);
        await Promise.all(pages.map(page => page.close()));
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
