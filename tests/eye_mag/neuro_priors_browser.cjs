const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
    const html = execFileSync('php', [path.join(__dirname, 'neuro_priors.php'), '--html'], {encoding: 'utf8'});
    const browser = await chromium.launch({headless: true});
    try {
        const page = await browser.newPage({viewport: {width: 900, height: 1200}});
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/*', route => route.abort());
        await page.setContent(html);
        const results = [];
        for (const width of [560, 430, 400, 300, 220]) {
            await page.locator('.fixture').evaluate((element, size) => element.style.width = size + 'px', width);
            const result = await page.evaluate(() => {
                const root = document.querySelector('.eye-neuro-prior');
                const bounds = root.getBoundingClientRect();
                const escaping = [...root.querySelectorAll('input,textarea,svg,section')].filter(element => {
                    const box = element.getBoundingClientRect();
                    return box.width && (box.left < bounds.left - 1 || box.right > bounds.right + 1);
                }).map(element => element.id || element.className || element.tagName);
                return {width: bounds.width, escaping, scroll: root.scrollWidth, client: root.clientWidth,
                    charts: [...root.querySelectorAll('svg')].map(element => element.getBoundingClientRect().width)};
            });
            assert.deepEqual(result.escaping, [], 'Historical controls stay inside their panel at ' + width + 'px');
            assert.ok(result.scroll <= result.client + 1, 'Prior panel has no horizontal overflow at ' + width + 'px');
            assert.equal(result.charts.length, 2);
            result.charts.forEach(size => assert.ok(Math.abs(size - 74) <= 1, 'H diagram retains current exam dimensions'));
            results.push(result);
            if (process.env.EYE_BROWSER_ARTIFACTS) {
                fs.mkdirSync(process.env.EYE_BROWSER_ARTIFACTS, {recursive: true});
                await page.locator('.fixture').screenshot({path: path.join(process.env.EYE_BROWSER_ARTIFACTS, 'neuro-priors-' + width + '.png')});
            }
        }
        assert.deepEqual(errors, []);
        console.log(JSON.stringify({passed: true, results}));
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
