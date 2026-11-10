#!/usr/bin/env node
/**
 * Writes the golden results the PHP UCUM port, unit helpers, Quantity and
 * Ratio must reproduce, by running ucum-lhc and cql-execution over a
 * deterministic set of generated inputs: every UCUM table code, prefixed
 * and powered codes, random unit expressions with annotations, parens and
 * repairs, conversions, and quantity arithmetic and comparison.
 *
 * Run with TZ=UTC (dates are added to quantities).
 *
 * Usage: TZ=UTC node golden-units.js <node_modules> <output directory> [seed] [scale]
 *
 * The reference engine is not part of OpenEMR. Install the versions the
 * fixtures were made with anywhere, e.g.
 *   npm install --prefix /tmp/cqm-reference cqm-execution@4.4.3 cqm-models@4.3.2
 * and pass /tmp/cqm-reference/node_modules as <node_modules>.
 *
 * Numbers are written as "#" and JavaScript's own string form, so the
 * comparison is exact and covers NaN and Infinity; a quantity is
 * {"q": [value, unit]}, a ratio {"r": [quantity, quantity]}, and a parsed
 * unit {"u": [code, magnitude, dimensions, function, function prefix,
 * special, arbitrary, mole exponent, equivalent exponent]}.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

'use strict';

const fs = require('fs');
const path = require('path');
const process = require('process');

const [nodeModules, outDir, seedArg, scaleArg] = process.argv.slice(2);
const SEED = Number(seedArg || 20251009);
const SCALE = Number(scaleArg || 1);
if (!nodeModules || !outDir) {
    console.error('usage: TZ=UTC node golden-units.js <node_modules> <output directory> [seed] [scale]');
    process.exit(2);
}
if (new Date().getTimezoneOffset() !== 0) {
    console.error('Run with TZ=UTC.');
    process.exit(2);
}
const lib = (p) => require(path.resolve(nodeModules, p));
const ucumUtils = lib('@lhncbc/ucum-lhc').UcumLhcUtils.getInstance();
const defs = JSON.parse(fs.readFileSync(path.resolve(nodeModules, '@lhncbc/ucum-lhc/data/ucumDefs.min.json'), 'utf8'));
const { DateTime, Date: CqlDate } = lib('cql-execution');
const { Quantity, parseQuantity, doAddition, doSubtraction } = lib('cql-execution/lib/datatypes/quantity');
const { Ratio } = lib('cql-execution/lib/datatypes/ratio');
const units = lib('cql-execution/lib/util/units');
const math = lib('cql-execution/lib/util/math');
const comparison = lib('cql-execution/lib/util/comparison');

// ucum-lhc logs every parse that throws.
console.log = () => {};

function mulberry32(seed) {
    let a = seed >>> 0;
    return () => {
        a = (a + 0x6D2B79F5) >>> 0;
        let t = a;
        t = Math.imul(t ^ (t >>> 15), t | 1);
        t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}
const rand = mulberry32(SEED);
const pick = (list) => list[Math.floor(rand() * list.length)];
const int = (lo, hi) => lo + Math.floor(rand() * (hi - lo + 1));

const unpack = (packed) => {
    const keys = packed.config.map((k) => (Array.isArray(k) ? k.join('.') : k));
    return packed.data.map((row) => Object.fromEntries(keys.map((k, i) => [k, row[i]])));
};
const TABLE = unpack(defs.units);
const CODES = TABLE.map((u) => u.csCode_);
const PREFIXES = unpack(defs.prefixes).map((p) => p.code_);
const NAMES = [...new Set(TABLE.map((u) => u.name_))].filter((n) => !/\s/.test(n));
const SIMPLE = CODES.filter((c) => !/[./]/.test(c));
const COMMON = [
    'mg', 'g', 'kg', 'ug', 'ng', 'L', 'dL', 'mL', 'uL', 'm', 'cm', 'mm', 'km', '[in_i]', '[ft_i]', '[lb_av]',
    '[oz_av]', 's', 'min', 'h', 'd', 'wk', 'mo', 'a', 'a_g', 'mo_g', 'ms', 'mol', 'mmol', 'umol', 'eq', 'meq',
    '[IU]', 'U', '%', '1', '10*3', '10*6', '10^3', 'Cel', '[degF]', 'K', '[pH]', 'mm[Hg]', 'cm[H2O]', 'Pa', 'kPa',
    'bar', 'J', 'cal', 'kcal', 'W', 'Hz', '/min', '{beats}/min', '{score}', '{cells}/uL', 'kg/m2', 'mg/dL',
    'mmol/L', 'g/L', 'mL/min', 'L/min', '[drp]', '[tsp_us]', 'B', 'dB', 'B[V]', 'Np', '[hp_X]', '[p\'diop]',
    'st', 'gon', 'deg', 'rad', 'sr', '[pi]', 'ar', '[acr_us]', 'b', 'osm', 'kat', 'mg/(24.h)', 'mL/kg/h',
];
const CQL_DATE_UNITS = ['year', 'years', 'month', 'months', 'week', 'weeks', 'day', 'days', 'hour', 'hours',
    'minute', 'minutes', 'second', 'seconds', 'millisecond', 'milliseconds'];
const VALUES = [1, 0, -1, 2.5, 37, 98.6, 1e-9, 123456.789, 1 / 3, 1e15, -0.000001, 7];

const num = (n) => '#' + String(n);

function encodeUnit(unit) {
    if (unit === null || unit === undefined) {
        return null;
    }
    if (typeof unit !== 'object') {
        return { text: String(unit) };
    }
    return { u: [String(unit.csCode_), num(unit.magnitude_), unit.dim_.dimVec_, unit.cnv_, num(unit.cnvPfx_),
        unit.isSpecial_, unit.isArbitrary_, unit.moleExp_, unit.equivalentExp_] };
}

const FIELDS = ['year', 'month', 'day', 'hour', 'minute', 'second', 'millisecond'];
function encode(value) {
    if (value === null || value === undefined) {
        return null;
    }
    if (typeof value === 'number') {
        return num(value);
    }
    if (Array.isArray(value)) {
        return value.map(encode);
    }
    if (value.isQuantity) {
        return { q: [num(value.value), value.unit ?? null] };
    }
    if (value.isRatio) {
        return { r: [encode(value.numerator), encode(value.denominator)] };
    }
    if (value.isDateTime) {
        return ['dt', ...FIELDS.map((f) => value[f] ?? ''), value.timezoneOffset ?? ''].join('|');
    }
    if (value.isDate) {
        return ['d', value.year ?? '', value.month ?? '', value.day ?? ''].join('|');
    }
    return value;
}

function attempt(fn) {
    try {
        return encode(fn());
    } catch {
        return { error: true };
    }
}

const files = {};
const record = (file, op, args, result) => (files[file] ??= []).push([op, args.map(encode), result]);

// Unit expressions: the table, prefixes, powers, and a hand-picked set of
// repairs and malformed input.
function specified(s) {
    const r = ucumUtils.getSpecifiedUnit(s, 'validate', true);
    return [r.status === 'valid', encodeUnit(r.unit)];
}
for (const code of CODES) {
    record('ucum-table', 'unit', [code], specified(code));
}
for (const name of NAMES) {
    record('ucum-table', 'unit', [name], specified(name));
}
const prefixSample = [...COMMON.filter((c) => !/[./{]/.test(c)), ...Array.from({ length: 20 * SCALE }, () => pick(SIMPLE))];
for (const prefix of PREFIXES) {
    for (const code of prefixSample) {
        const s = prefix + code;
        record('ucum-prefixed', 'unit', [s], specified(s));
        const p = s + pick(['2', '3', '-1', '-2', '+2', '0', '-0', '04']);
        record('ucum-prefixed', 'unit', [p], specified(p));
    }
}
const EDGES = [
    '', ' ', ' mg ', 'mg dL', 'mg\tdL', '2mg', 'm/2mg', '2(mg)', '(mg)2', '-2(m)', '+3(g)', 'Infinity(m)', '{a}10',
    'm.{a}10', '10{a}', '{a}m', 'm{a}', '{a}{b}', 'm{a}{b}', '{a}m{b}', '{a b}', '{', '}', '}{a}', '{a}}', '{{a}}',
    '{aé}', '()', '(m', 'm)', '((m))', '(m.s)/kg', '((m).s)/(g)', '(m)(s)', '2(m.s)', 'm/2(s)', 'm/(2s)',
    '2m/s', '10*3/uL', '10^3', '10^-3', '10*-3', '10+3', '10-3', '2-3', '2+3', 'm0', 'm-0', 'm+2', 'm--2', 'm2-',
    'cm2', 'km-1', '/s', '/(s.m)', './s', 'm//s', 'm./s', 'm.', 'm/', '.', '/', '1/', 'meter', 'gram', 'second',
    'meter/second', 'Hg', 'mmHg', 'in_i', 'ft_i', 'degF', 'IU', 'IU/L', 'iU', 'pH', 'B[10.nV]', 'mB[10.nV]',
    '[m/s2/Hz^(1/2)]', 'specialUnitOne', 'Cel2', 'mCel', 'cel', 'CEL', '[degF]2', 'mg/dl', 'MG', 'L.L', 'mL/kg/h',
    '[IU]/(24.h)', 'mmol/(24.h)', '0', '00', '007', '0.mg', '1.5', '1e3', '0x10', '2.5mg', 'mg.2', 'mg/0',
    '100000000000000000000', '1e21', 'parens_placeholder0parens_placeholder', '{braces_placeholder}', 'u', 'µg',
    'mµg', 'mm[Hg]2', 'mm[Hg]/s', '[IU]2', 'g%', 'g/100mL', 'g/(100.mL)', 'kg/m2', 'mmol/mol', 'eq/mmol',
    '%{HemoglobinA1C}', '{#}/[HPF]', '/[HPF]', '{Ehrlich\'U}/dL', 'a_g', 'mo_g', 'year', 'years', 'h-1', 'd2',
    '10*3{cells}/uL', '{cells}.10*3/uL', '[pi].rad', 'Np/s', 'B/s', '2B', 'B2', '[hp_X]', 'm[hp_X]', '[ppm]',
    'st.st', 'mo/a', 'a/mo', 'wk.d', 'K/s', 'Cel/s', 'Cel.s', 's.Cel', '2.Cel', 'Cel.2', 'Cel/2', '2/Cel',
];
for (const s of EDGES) {
    record('ucum-edges', 'unit', [s], specified(s));
}

/** A random unit expression from codes, prefixes, powers, numbers, annotations and parens. */
function randomAtom(depth) {
    const r = rand();
    if (r < 0.35) {
        return pick(COMMON).replace(/\/.*/, '') || 'g';
    }
    if (r < 0.5) {
        return pick(PREFIXES) + pick(SIMPLE);
    }
    if (r < 0.6) {
        return pick(SIMPLE) + pick(['2', '3', '-1', '-2', '+1']);
    }
    if (r < 0.67) {
        return String(pick([1, 2, 10, 100, 24, 0]));
    }
    if (r < 0.75) {
        const anno = '{' + pick(['a', 'cells', 'score', 'x1', '#', 'RBC', 'beats', '']) + '}';
        return pick(['', pick(SIMPLE), String(int(1, 12))]) + anno;
    }
    if (r < 0.8) {
        return pick(NAMES);
    }
    if (r < 0.85) {
        return String(int(1, 20)) + pick(['mg', 'h', 'L', '[IU]', 'm']);
    }
    if (depth < 2) {
        return '(' + randomExpression(depth + 1) + ')';
    }
    return pick(SIMPLE);
}
function randomExpression(depth = 0) {
    let s = (rand() < 0.1 ? '/' : '') + randomAtom(depth);
    const n = int(0, 2);
    for (let i = 0; i < n; i++) {
        s += pick(['.', '/', '/', '.']) + randomAtom(depth);
    }
    return s;
}
const expressions = new Set();
for (let i = 0; i < 2500 * SCALE; i++) {
    expressions.add(randomExpression());
}
for (const s of expressions) {
    record('ucum-random', 'unit', [s], specified(s));
}

// Conversions between units of matching dimension most of the time.
const pool = [...COMMON, ...[...expressions].filter((s) => ucumUtils.getSpecifiedUnit(s, 'validate', false).unit)
    .slice(0, 300 * SCALE)];
const byDimension = {};
for (const s of pool) {
    const unit = ucumUtils.getSpecifiedUnit(s, 'validate', false).unit;
    if (unit && unit.dim_) {
        (byDimension[unit.dim_.dimVec_.join(',')] ??= []).push(s);
    }
}
const dimensionGroups = Object.values(byDimension).filter((g) => g.length > 1);
function convertResult(from, value, to) {
    try {
        const r = ucumUtils.convertUnitTo(from, value, to);
        return r.status === 'succeeded' ? num(r.toVal) : null;
    } catch {
        return { error: true };
    }
}
for (let i = 0; i < 2500 * SCALE; i++) {
    const [from, to] = rand() < 0.75
        ? [pick(pick(dimensionGroups)), pick(pick(dimensionGroups))]
        : [pick(pool), pick(pool)];
    const group = rand() < 0.75 ? pick(dimensionGroups) : null;
    const [a, b] = group ? [pick(group), pick(group)] : [from, to];
    const value = pick(VALUES);
    record('conversions', 'convertUnitTo', [a, value, b], convertResult(a, value, b));
    record('conversions', 'convertUnit', [value, a, b], attempt(() => units.convertUnit(value, a, b)));
}
for (const [a, b] of [['Cel', '[degF]'], ['[degF]', 'K'], ['K', 'Cel'], ['mCel', 'Cel'], ['[pH]', 'mol/L'],
    ['mol/L', '[pH]'], ['B', 'dB'], ['B[V]', 'V'], ['[hp_X]', '1'], ['Np', '1'], ['%', '1'], ['[ppm]', '%'],
    ['mmol/L', 'mg/dL'], ['meq/L', 'mmol/L'], ['eq', 'mol'], ['[IU]', 'U'], ['a', 'mo'], ['a_g', 'd'],
    ['mo_g', 'd'], ['wk', 'h'], ['{a}10', 'mg'], ['mg', '{a}10'], ['meter', 'cm'], ['[p\'diop]', '%'],
    ['[%payload]', '%'], ['%', '[%payload]'], ['2(mg)', 'mg'], ['-2(m)', 'm'], ['B[10.nV]', 'B[V]']]) {
    for (const value of VALUES) {
        record('conversions', 'convertUnitTo', [a, value, b], convertResult(a, value, b));
    }
}

// cql-execution's unit helpers.
const helperUnits = [...COMMON, ...CQL_DATE_UNITS, '', null, 'm2', 'm3', 'cm2', 'g.m', 'm.g', 'm/s', 'm/s2',
    'kg.m/s2', 'g/mL', '1/s', 'm-1', '[IU]/L', 'mg2', 'a2', '10*3', 'Cel2', '{a}10', 'g-', '-'];
for (const u of helperUnits) {
    record('helpers', 'checkUnit', [u], attempt(() => units.checkUnit(u).valid));
    record('helpers', 'convertToCQLDateUnit', [u], attempt(() => units.convertToCQLDateUnit(u) ?? null));
}
for (let i = 0; i < 1000 * SCALE; i++) {
    const a = pick(helperUnits);
    const b = rand() < 0.2 ? a : pick(helperUnits);
    const [v1, v2] = [pick(VALUES), pick(VALUES)];
    record('helpers', 'normalizeUnitsWhenPossible', [v1, a, v2, b], attempt(() => units.normalizeUnitsWhenPossible(v1, a, v2, b)));
    record('helpers', 'compareUnits', [a, b], attempt(() => units.compareUnits(a, b)));
    record('helpers', 'getProductOfUnits', [a, b], attempt(() => units.getProductOfUnits(a, b)));
    record('helpers', 'getQuotientOfUnits', [a, b], attempt(() => units.getQuotientOfUnits(a, b)));
}
const numbers = [...VALUES, 0.1 + 0.2, 1e21, 1e-7, 123e-20, -1.5, 2.5, -2.5, 0.5, -0.5, 1.005, 2 ** 31, -(2 ** 31) - 1,
    1e20, 1e20 * 1.0000001, NaN, Infinity, -Infinity, 5e-324, 1.7976931348623157e308, 0.000001234, 123456789012345680000];
for (let i = 0; i < 400 * SCALE; i++) {
    numbers.push((rand() - 0.5) * 10 ** int(-12, 22), int(-1000, 1000) / pick([1, 10, 100, 1000, 3, 7]));
}
for (const n of numbers) {
    record('helpers', 'decimalAdjust', [n], attempt(() => math.decimalAdjust('round', n, -8)));
    record('helpers', 'numberToString', [n], String(n));
    record('helpers', 'overflowsOrUnderflows', [n], attempt(() => math.overflowsOrUnderflows(n)));
}

// Quantities and ratios.
const quantityUnits = [...COMMON, ...CQL_DATE_UNITS, '', null, 'm2', 'g.m', 'm/s', '1/s', 'mg/kg', '{a}10'];
function randomQuantity() {
    return new Quantity(pick(VALUES), pick(quantityUnits));
}
const quantities = [];
while (quantities.length < 150 * SCALE) {
    try {
        quantities.push(randomQuantity());
    } catch {
        // invalid unit; skip
    }
}
for (const u of quantityUnits) {
    for (const v of [1, NaN, 1e21, -1e21, 1e20]) {
        record('quantity', 'new', [v, u], attempt(() => new Quantity(v, u)));
    }
}
for (const s of ['5', "5 'mg'", "-2.5 'mg/dL'", "+3 'mm[Hg]'", "7'days'", "1.5   'cm'", "x 'mg'", "12 'foo'",
    '1e3', "3. 'g'", "'mg'", '', "|4 'g'", "1 ' mg '", "100000000000000000000000 'g'"]) {
    record('quantity', 'parse', [s], attempt(() => parseQuantity(s) ?? null));
}
for (let i = 0; i < 300 * SCALE; i++) {
    const a = pick(quantities);
    const b = rand() < 0.4 ? new Quantity(a.value * pick([1, 2, 0.5, 1000, 0.001]), pick(quantityUnits.filter((u) => u !== '{a}10'))) : pick(quantities);
    for (const op of ['equals', 'equivalent', 'lessThan', 'lessThanOrEquals', 'greaterThan', 'greaterThanOrEquals']) {
        record('quantity', 'comparison.' + op, [a, b], attempt(() => comparison[op](a, b) ?? null));
    }
    const n = pick([b, b, pick(VALUES), null]);
    record('quantity', 'dividedBy', [a, n], attempt(() => a.dividedBy(n) ?? null));
    record('quantity', 'multiplyBy', [a, n], attempt(() => a.multiplyBy(n) ?? null));
    record('quantity', 'add', [a, b], attempt(() => doAddition(a, b) ?? null));
    record('quantity', 'subtract', [a, b], attempt(() => doSubtraction(a, b) ?? null));
    const to = pick(quantityUnits);
    record('quantity', 'quantityConvertUnit', [a, to], attempt(() => a.convertUnit(to)));
    record('quantity', 'toString', [a], a.toString());
    const r1 = new Ratio(a, b);
    const r2 = new Ratio(pick(quantities), pick(quantities));
    const r3 = rand() < 0.5 ? r2 : new Ratio(new Quantity(a.value * 2, a.unit), new Quantity(b.value * 2, b.unit));
    record('quantity', 'comparison.equals', [r1, r3], attempt(() => comparison.equals(r1, r3) ?? null));
    record('quantity', 'comparison.equivalent', [r1, r3], attempt(() => comparison.equivalent(r1, r3) ?? null));
    record('quantity', 'ratioToString', [r1], r1.toString());
}
const dates = [new DateTime(2024, 1, 31, 10, 30, 0, 0, 0), new DateTime(2025, 2, 28, null, null, null, null, 0),
    new DateTime(2024, 2, 29, 23, 59, 59, 999, -5), new CqlDate(2024, 2, 29), new CqlDate(2025, 6, null),
    new CqlDate(2023, 12, 31), new DateTime(2025, null, null, null, null, null, null, 0)];
for (const d of dates) {
    for (const u of [...CQL_DATE_UNITS, 'a', 'mo', 'wk', 'd', 'h', 'min', 's', 'ms', 'a_g', 'mg', '1', '']) {
        for (const v of [1, -1, 1.5, 13, 0]) {
            const q = new Quantity(v, u);
            record('quantity-dates', 'add', [d, q], attempt(() => doAddition(d, q) ?? null));
            record('quantity-dates', 'subtract', [d, q], attempt(() => doSubtraction(d, q) ?? null));
        }
    }
}

fs.mkdirSync(outDir, { recursive: true });
for (const [name, cases] of Object.entries(files)) {
    const file = path.join(outDir, `${name}.json`);
    fs.writeFileSync(file, JSON.stringify({ generator: 'golden-units.js', seed: SEED, scale: SCALE, cases }) + '\n');
    console.error(`${file}: ${cases.length} cases, ${fs.statSync(file).size} bytes`);
}
