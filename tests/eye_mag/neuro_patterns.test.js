'use strict';
const assert = require('node:assert/strict');
const test = require('node:test');
const fs = require('node:fs');
const path = require('node:path');
const patterns = require('../../interface/forms/eye_mag/js/neuro_patterns.js');
const formRoot = path.resolve(__dirname, '../../interface/forms/eye_mag');
const has = (values, code, eye) => patterns.analyze(values).some(f => f.code === code && (!eye || f.eye === eye));
function act(entries, context = 'CCDIST') {
    return Object.fromEntries(Object.entries(entries).map(([position, value]) => ['ACT' + position + context, value]));
}
const leftIV = act({5: '8 LHT', 4: '16 LHT', 6: '2 LHT', 10: '2 LHT', 11: '18 LHT'});
const rightIV = act({5: '8 RHT', 4: '2 RHT', 6: '16 RHT', 10: '18 RHT', 11: '2 RHT'});

for (const [raw, h, v] of [
    ['12 LHT', null, -12], ['LHT 12', null, -12], ['12LHT', null, -12],
    ['4 RH(T)', null, 4], ['4 Lhypo(T)', null, 4], ['4 RhypoT', null, -4],
    ['2.5 OSHT', null, -2.5], ['.5 RIGHT HT', null, .5],
    ['15 XT / 4 LHT', 15, -4], ['4 RHT, 12 E(T)', -12, 4],
    ['12 RET', -12, null], ['3 LX(T)', 3, null], ['XT 10 AND RHT 4', 10, 4],
    ['10 XT Δ', 10, null], ['10 ET PD', -10, null], ['Ortho', 0, 0], ['0', 0, 0],
    ['0 HT', null, 0]
]) {
    test('parse ' + raw, () => {
        const result = patterns.parse(raw);
        assert.equal(result.valid, true);
        assert.equal(result.h || 0, h === null ? 0 : h);
        assert.equal(result.h === null, h === null);
        assert.equal(result.v || 0, v === null ? 0 : v);
        assert.equal(result.v === null, v === null);
    });
}
test('blank is unknown, not Ortho; absent axis remains unknown', () => {
    assert.equal(patterns.parse(' '), null);
    assert.equal(patterns.parse(null), null);
    assert.equal(patterns.parse('15 XT').v, null);
    assert.equal(patterns.parse('2 LHT').h, null);
});
for (const raw of ['12 HT', '12', 'XT', '12 XT 3 ET', '12 LHT / 3 RHT',
    '12 XT?', 'Ortho / 3 RHT', '12 XT /', '12 XT and', '12 OUHT', '-3 XT',
    '3 LHTabc', '10 XT <img src=x>', 'NaN ET']) {
    test('reject ambiguous or incomplete ' + raw, () => assert.equal(patterns.parse(raw).valid, false));
}
test('left and right IV patterns are mirrored correctly', () => {
    assert.ok(has(leftIV, 'ivPattern', 'OS'));
    assert.ok(!has(leftIV, 'ivPattern', 'OD'));
    assert.ok(has(rightIV, 'ivPattern', 'OD'));
    const equivalent = Object.fromEntries(Object.entries(leftIV).map(([k, v]) => [k, v.replace('LHT', 'RhypoT')]));
    assert.ok(has(equivalent, 'ivPattern', 'OS'));
});
test('three-step uses side-to-side differences, not an extra primary threshold', () => {
    assert.ok(has({...leftIV, ACT5CCDIST: '25 LHT'}, 'ivPattern', 'OS'));
});
test('IV requires both gaze and both head-tilt comparisons', () => {
    for (const field of ['ACT4CCDIST', 'ACT6CCDIST', 'ACT10CCDIST', 'ACT11CCDIST']) {
        const values = {...leftIV, [field]: ''};
        assert.ok(!has(values, 'ivPattern'));
        assert.ok(patterns.analyze(values).find(f => f.code === 'ivIncomplete').missing.includes(field));
    }
    assert.ok(!has({...leftIV, ACT11CCDIST: '1 LHT'}, 'ivPattern'));
    assert.ok(!has({...leftIV, ACT4CCDIST: '2 LHT'}, 'ivPattern'));
});
test('explicit Ortho can be compared, blank or horizontal-only cannot', () => {
    assert.ok(has({...leftIV, ACT10CCDIST: 'Ortho'}, 'ivPattern', 'OS'));
    assert.ok(!has({...leftIV, ACT10CCDIST: '10 XT'}, 'ivPattern'));
    assert.ok(has({...leftIV, ACT5CCDIST: ''}, 'verticalPrimaryMissing'));
});
test('alternating hyperdeviation is not labelled unilateral IV', () => {
    const values = {...leftIV, ACT10CCDIST: '2 RHT'};
    assert.ok(has(values, 'alternatingVertical'));
    assert.ok(!has(values, 'ivPattern'));
});
test('do not mix correction, distance, or SPCT with gaze ACT', () => {
    assert.ok(!has({...leftIV, ACT11CCDIST: '', ACT11SCDIST: '18 LHT'}, 'ivPattern'));
    assert.ok(!has({...leftIV, ACT11CCDIST: '', ACT11CCNEAR: '18 LHT'}, 'ivPattern'));
    assert.ok(!has({...leftIV, ACT5CCDIST: '', SPCT_CCDIST: '8 LHT'}, 'ivPattern'));
});
for (const context of patterns.contexts) {
    test('independent IV/V analysis in ' + context, () => {
        assert.ok(has(act({5:'8 LHT',4:'16 LHT',6:'2 LHT',10:'2 LHT',11:'18 LHT'}, context), 'ivPattern', 'OS'));
        assert.ok(has(act({2:'25 XT',8:'10 XT'}, context), 'vPattern'));
    });
}
for (const [up, down, code] of [['25 XT','10 XT','vPattern'], ['5 ET','20 ET','vPattern'],
    ['5 XT','15 XT','aPattern'], ['20 ET','10 ET','aPattern'], ['5 XT','10 ET','vPattern']]) {
    test('signed A/V ' + up + ' / ' + down, () => assert.ok(has(act({2:up,8:down}), code)));
}
test('A/V thresholds, absent axis and contexts', () => {
    assert.ok(!has(act({2:'24.9 XT',8:'10 XT'}), 'vPattern'));
    assert.ok(!has(act({2:'10 XT',8:'19.9 XT'}), 'aPattern'));
    assert.ok(!has(act({2:'25 XT',8:'2 LHT'}), 'vPattern'));
    assert.ok(!has({ACT2CCDIST:'25 XT',ACT8SCDIST:'10 XT'}, 'vPattern'));
});
test('single up/down or primary eso entries prompt required measurements', () => {
    assert.ok(has(act({2:'25 XT'}), 'avIncomplete'));
    assert.ok(has(act({5:'15 ET'}), 'horizontalGazeIncomplete'));
    assert.ok(!has(act({5:'15 ET',4:'25 ET',6:'5 ET'}), 'horizontalGazeIncomplete'));
});
const rightVI = {...act({5:'15 ET',4:'30 ET',6:'5 ET'}), MOTILITY_RR:'2'};
const leftVI = {...act({5:'15 ET',4:'5 ET',6:'30 ET'}), MOTILITY_LL:'2'};
test('VI needs ipsilateral gaze increase and abduction limitation', () => {
    assert.ok(has(rightVI, 'viPattern', 'OD'));
    assert.ok(has(leftVI, 'viPattern', 'OS'));
    assert.ok(!has(rightVI, 'viPattern', 'OS'));
    assert.ok(!has({...rightVI, MOTILITY_RR:''}, 'viPattern'));
    assert.ok(has({...rightVI, MOTILITY_RR:''}, 'viCheckMotility'));
    assert.ok(has({...rightVI, MOTILITY_RR:'0'}, 'viNoLimitation'));
    assert.ok(!has({...rightVI, ACT4CCDIST:'10 ET'}, 'viPattern'));
});
test('VI with other limitations is mixed, not isolated', () => {
    assert.ok(has({...rightVI, MOTILITY_RL:'2'}, 'viMixed'));
    assert.ok(!has({...rightVI, MOTILITY_RL:'2'}, 'viPattern'));
});
const rightIII = {MOTILITY_RL:'3', MOTILITY_RR:'0', MOTILITY_RS:'2', MOTILITY_RI:'2'};
test('III identifies combined deficits with spared abduction in either eye', () => {
    assert.ok(has(rightIII, 'iiiPattern', 'OD'));
    assert.ok(has({MOTILITY_LR:'3',MOTILITY_LL:'0',MOTILITY_LS:'2',MOTILITY_LI:'2'}, 'iiiPattern', 'OS'));
    assert.ok(!has({...rightIII, MOTILITY_RI:'0'}, 'iiiPattern'));
    assert.ok(has({...rightIII, MOTILITY_RR:''}, 'iiiIncomplete'));
    assert.ok(has({...rightIII, MOTILITY_RR:'3'}, 'globalRestriction'));
});
test('H chart paired upper/lower limbs can supply III pattern', () => {
    const h = {MOTILITY_RL:'3', MOTILITY_RR:'0', MOTILITY_RRSO:'2', MOTILITY_RLSO:'2', MOTILITY_RRIO:'2', MOTILITY_RLIO:'2'};
    assert.ok(has(h, 'iiiPattern', 'OD'));
    assert.ok(!has({...h,MOTILITY_RLIO:''}, 'iiiPattern'));
});
test('conflicting Normal flag suppresses nerve-palsy suggestions', () => {
    assert.ok(has({...rightIII, MOTILITYNORMAL:true}, 'normalConflict'));
    assert.ok(!has({...rightIII, MOTILITYNORMAL:true}, 'iiiPattern'));
    assert.ok(!has({...rightVI, MOTILITYNORMAL:true}, 'viPattern'));
});
test('IOOA is only a relative up-adduction screening cue', () => {
    assert.ok(has(act({5:'2 RHT',3:'12 RHT',1:'3 RHT',6:'4 RHT'}), 'ioScreen', 'OD'));
    assert.ok(has(act({5:'2 LHT',1:'12 LHT',3:'3 LHT',4:'4 LHT'}), 'ioScreen', 'OS'));
    assert.ok(!has(act({5:'2 RHT',3:'12 RHT',1:'3 RHT',6:''}), 'ioScreen'));
    assert.ok(!has(act({2:'25 XT',8:'10 XT'}), 'ioScreen'));
    assert.ok(!has({MOTILITY_RLSO:'-3'}, 'ioScreen'));
});
test('SPCT comparison is axis-aware, rejects contradictions', () => {
    const values = {ACT5CCDIST:'15 XT / 6 RHT',SPCT_CCDIST:'10 XT / 2 RHT'};
    const differences = patterns.analyze(values).filter(f => f.code === 'dissociated');
    assert.deepEqual(differences.map(f => f.data.delta), [5,4]);
    assert.ok(has({...values,SPCT_CCDIST:'20 XT'}, 'spctConflict'));
    assert.ok(has({...values,SPCT_CCDIST:'10 ET'}, 'spctConflict'));
});
test('distance/near descriptive patterns retained without diagnosing high AC/A', () => {
    assert.ok(has({ACT5CCDIST:'10 XT',ACT5CCNEAR:'20 XT'}, 'nearExo'));
    assert.ok(has({ACT5CCDIST:'20 XT',ACT5CCNEAR:'10 XT'}, 'distanceExo'));
    assert.ok(has({ACT5CCDIST:'10 XT',ACT5CCNEAR:'15 XT'}, 'basicExo'));
    assert.ok(has({ACT5CCDIST:'10 ET',ACT5CCNEAR:'20 ET'}, 'nearEso'));
    assert.ok(has({ACT5CCDIST:'20 ET',ACT5CCNEAR:'10 ET'}, 'distanceEso'));
    assert.ok(!has({ACT5CCDIST:'10 XT',ACT5SCNEAR:'20 XT'}, 'nearExo'));
});
test('unparseable off-axis entries are reported, not silently ignored', () => {
    assert.ok(has({ACT9CCDIST:'12 HT'}, 'unparsed'));
    assert.deepEqual(patterns.analyze({}), []);
});

// Minimal DOM adapter exercises bind/render/event contracts without a browser or
// new runtime dependency. It deliberately offers text(), not html(), for content.
test('live binding rereads ACT, SPCT and motility; clearing removes old guidance', () => {
    let values = {...leftIV};
    const registrations = [], timers = [];
    class Element {
        constructor() { this.children = []; this.content = ''; }
        text(text) { this.content = text; return this; }
        append(child) { this.children.push(child); return this; }
        empty() { this.children = []; this.content = ''; return this; }
        filter() { return this; }
        attr() { return this; }
        each(callback) { for (const id of Object.keys(values)) callback.call({id}); return this; }
        on(events, selector, callback) { registrations.push({events,selector,callback}); return this; }
        prop() { return !!values.MOTILITYNORMAL; }
    }
    const target = new Element();
    global.window = {eyeNeuroPatternStrings:{ivPattern:'IV pattern {eye}',OS:'left eye'}, setTimeout: fn => timers.push(fn)};
    global.document = {};
    const $ = selector => {
        if (selector && selector.id) return {val: () => values[selector.id]};
        if (selector === '#NEURO_PATTERN_ANALYSIS .neuro-pattern-guidance') return target;
        return new Element();
    };
    const rendered = () => {
        const flatten = node => node.content + ' ' + node.children.map(flatten).join(' ');
        return flatten(target);
    };
    try {
        const update = patterns.bind($);
        assert.match(rendered(), /IV pattern left eye/);
        const input = registrations.find(r => r.events.includes('input.'));
        assert.match(input.selector, /#NEURO_1 textarea.ACT/);
        assert.match(input.selector, /SPCT_/);
        assert.match(input.selector, /MOTILITY_/);
        values.ACT11CCDIST = '';
        input.callback();
        assert.doesNotMatch(rendered(), /IV pattern left eye/);
        assert.match(rendered(), /ivIncomplete/);
        values = {...leftIV};
        update(); // copy-forward entry point
        assert.match(rendered(), /IV pattern left eye/);
        values = {...rightIII};
        registrations.find(r => r.events.startsWith('click.')).callback();
        timers.shift()();
        assert.match(rendered(), /iiiPattern/);
        values = {ACT5CCDIST:'<script>alert(1)</script>'};
        input.callback();
        assert.match(rendered(), /unparsed/);
    } finally { delete global.window; delete global.document; }
});
test('integration loads dependencies first and keeps Record/copy-forward refresh', () => {
    const view = fs.readFileSync(path.join(formRoot,'view.php'),'utf8');
    const base = fs.readFileSync(path.join(formRoot,'js/eye_base.php'),'utf8');
    assert.ok(view.indexOf("require __DIR__ . '/php/neuro_pattern_strings.php'") < view.indexOf('/js/neuro_patterns.js?v='));
    assert.ok(view.indexOf('/js/neuro_patterns.js?v=') < view.indexOf('/js/eye_base.php?enc='));
    assert.match(base, /window\.EyeNeuroPatterns\.bind\(\$\)/);
    assert.match(base, /if \(zone === "NEURO"\) \{ updatePrismPatternGuidance\(\); \}/);
    assert.match(base, /\$\("#ACT"\+number\+zone\)\.val\(strab\).*trigger\('input'\)/);
});

// Additional clinical pattern coverage.
const find = (values, code, eye) => patterns.analyze(values).find(f => f.code === code && (!eye || f.eye === eye));
test('parser records tropia, intermittent and phoria types', () => {
    assert.equal(patterns.parse('8 X(T)').hType, 'I');
    assert.equal(patterns.parse('12 XP').hType, 'P');
    assert.equal(patterns.parse('15 XT').hType, 'T');
    assert.equal(patterns.parse('10 RHT').vType, 'T');
    assert.equal(patterns.parse('Ortho').hType, 'O');
});
// Synthetic example: an intermittent exotropia with a small right hyper.
const rosa = {...act({5: '8 X(T)\n1RHT', 4: '10 X(T)', 6: '6 X(T)', 2: '15 XT', 8: '5 X(T)'}, 'SCDIST'),
    ...act({5: 'ORTHO', 4: '5 X(T)', 6: 'ORTHO'}, 'CCDIST')};
test('regression: primary summary leads, three-step reports partial steps', () => {
    const primary = find(rosa, 'primaryDeviation');
    assert.equal(primary.context, 'SCDIST');
    assert.equal(primary.data.hKind, 'kind_XI');
    assert.equal(primary.data.vKind, 'kind_RHT');
    assert.equal(primary.data.vSize, 1);
    const iv = find(rosa, 'ivIncomplete', 'OD');
    assert.equal(iv.data.step2, 'stepPending');
    assert.ok(iv.missing.includes('ACT4SCDIST')); // entered, but no vertical component
    assert.ok(has(rosa, 'correctionChange'));
    assert.ok(!has(rosa, 'vPattern'));
    assert.ok(has(rosa, 'primaryOrtho'));
});
test('comitant vertical is named, with fusional amplitudes as evidence', () => {
    const values = {...act({5: '3 RHT', 4: '3 RHT', 6: '4 RHT', 10: '3 RHT', 11: '2 RHT'}), VERTFUSAMPS: '8'};
    assert.ok(has(values, 'comitantVertical', 'OD'));
    assert.ok(!has(values, 'ivPattern'));
    assert.ok(find(values, 'comitantVertical').evidence.some(e => e.field === 'VERTFUSAMPS'));
    assert.ok(has(leftIV, 'ivPattern', 'OS')); // large differences still localize
});
test('bilateral IV pattern is recognized instead of generic alternation', () => {
    const values = act({5: '2 RHT', 6: '8 RHT', 4: '6 LHT', 10: '8 RHT', 11: '6 LHT'});
    assert.ok(has(values, 'bilateralIV'));
    assert.ok(!has(values, 'alternatingVertical'));
});
test('accommodative esotropia patterns compare sc with cc at the same distance', () => {
    const sc = {ACT5SCDIST: '30 ET', ACT5SCNEAR: '35 ET'};
    assert.ok(has({...sc, ACT5CCDIST: '4 ET', ACT5CCNEAR: '6 ET'}, 'refractiveAccommodative'));
    assert.ok(has({...sc, ACT5CCDIST: '15 ET', ACT5CCNEAR: '6 ET'}, 'partiallyAccommodative'));
    assert.ok(has({...sc, ACT5CCDIST: '28 ET', ACT5CCNEAR: '33 ET'}, 'nonAccommodativeEso'));
    assert.ok(has({ACT5CCDIST: '4 ET', ACT5CCNEAR: '20 ET'}, 'accommodativeNear'));
    assert.ok(!has({ACT5CCDIST: '10 ET', ACT5CCNEAR: '20 ET'}, 'accommodativeNear'));
    assert.ok(has({ACT5SCDIST: '20 ET', ACT5CCDIST: '12 XT'}, 'correctionReverses'));
    assert.ok(!has({ACT5SCDIST: '30 ET', ACT5CCNEAR: '4 ET'}, 'refractiveAccommodative'));
});
test('exodeviation change with correction', () => {
    assert.ok(has({ACT5SCDIST: '10 XT', ACT5CCDIST: '22 XT'}, 'exoLargerWithCorrection'));
    assert.ok(has({ACT5SCDIST: '25 XT', ACT5CCDIST: '10 XT'}, 'exoSmallerWithCorrection'));
});
test('lateral incomitance, side-gaze reduction and comitance', () => {
    assert.equal(find(act({4: '20 XT', 5: '15 XT', 6: '10 XT'}), 'lateralIncomitance').data.larger, 'rightGaze');
    assert.ok(!has(rightVI, 'lateralIncomitance')); // the VI interpretation already covers it
    assert.ok(has(act({4: '8 XT', 5: '20 XT', 6: '9 XT'}), 'sideGazeReduction'));
    assert.ok(has(act({2: '20 XT', 4: '20 XT', 5: '21 XT', 6: '19 XT', 8: '22 XT'}), 'comitantHorizontal'));
    assert.ok(has(act({5: 'Ortho', 4: 'Ortho', 6: 'Ortho'}), 'orthoAll'));
    assert.ok(!has(act({5: 'Ortho', 4: 'Ortho', 6: 'Ortho'}), 'primaryOrtho'));
});
test('Y, lambda and X pattern features', () => {
    assert.ok(has(act({2: '30 XT', 5: '15 XT', 8: '14 XT'}), 'yFeature'));
    assert.ok(has(act({2: '15 XT', 5: '15 XT', 8: '27 XT'}), 'lambdaFeature'));
    assert.ok(has(act({2: '30 XT', 5: '15 XT', 8: '28 XT'}), 'xPattern'));
});
test('SO overaction screen mirrors the IO screen', () => {
    assert.ok(has(act({5: '2 LHT', 9: '12 LHT', 7: '3 LHT', 6: '4 LHT'}), 'soScreen', 'OD'));
    assert.ok(!has(act({5: '2 LHT', 9: '12 LHT', 7: '3 LHT'}), 'soScreen'));
});
test('duction patterns: Duane, adduction deficit, Brown, bilateral abduction', () => {
    assert.ok(has({MOTILITY_RR: '3', ACT5CCDIST: 'Ortho'}, 'duaneScreen', 'OD'));
    assert.ok(!has(rightVI, 'duaneScreen')); // 15 ET primary fits VI better
    assert.ok(has({MOTILITY_RR: '2', MOTILITY_RL: '2'}, 'duane3Screen', 'OD'));
    assert.ok(has({MOTILITY_LR: '2'}, 'adductionDeficit', 'OS'));
    assert.ok(has({MOTILITY_RLSO: '2', MOTILITY_RRSO: '0'}, 'brownScreen', 'OD'));
    assert.ok(has({MOTILITY_LS: '2'}, 'elevationDeficit', 'OS'));
    assert.ok(has({MOTILITY_RLIO: '2', MOTILITY_RRIO: '0'}, 'depressionInAdduction', 'OD'));
    assert.ok(has({MOTILITY_RS: '2', MOTILITY_RI: '2'}, 'verticalRestriction', 'OD'));
    assert.ok(has({MOTILITY_RR: '2', MOTILITY_LL: '2', ACT5CCDIST: '20 ET'}, 'bilateralAbduction'));
    assert.ok(!has({...rightIII}, 'adductionDeficit')); // III takes precedence
    assert.ok(!has({MOTILITY_RR: '3', ACT5CCDIST: 'Ortho', MOTILITYNORMAL: true}, 'duaneScreen'));
});
test('sensory: monofixation and NPC', () => {
    assert.ok(has({ACT5CCDIST: '6 ET', STEREO_FLY: 'Yes', STEREO_CIRCLES: '5'}, 'monofixation'));
    assert.ok(!has({ACT5CCDIST: '6 E(T)', STEREO_FLY: 'Yes', STEREO_CIRCLES: '5'}, 'monofixation'));
    assert.ok(has({ACT5CCDIST: '6 ET'}, 'smallTropiaStereo'));
    assert.ok(has({NPC: '12 cm'}, 'npcRemote'));
    assert.ok(!has({NPC: '4'}, 'npcRemote'));
    assert.ok(has({ACT5CCDIST: '10 ET', ACT5CCNEAR: '15 ET'}, 'basicEso'));
});
