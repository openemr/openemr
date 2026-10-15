#!/usr/bin/env node
/**
 * Writes the golden results the PHP Interval type must reproduce, by
 * running cql-execution's Interval and successor/predecessor helpers over a
 * deterministic set of generated intervals: Integer, Decimal (whole and
 * fractional), Quantity, Date and DateTime points, closed, open and null
 * bounds, and every interval operation against intervals, points and
 * nulls.
 *
 * Run with TZ=UTC: cql-execution reads the machine timezone where the PHP
 * port uses UTC (CqlDateTime::LOCAL_OFFSET).
 *
 * Usage: TZ=UTC node golden-intervals.js <node_modules> <output directory> [seed] [scale]
 *
 * The reference engine is not part of OpenEMR. Install the versions the
 * fixtures were made with anywhere, e.g.
 *   npm install --prefix /tmp/cqm-reference cqm-execution@4.4.3 cqm-models@4.3.2
 * and pass /tmp/cqm-reference/node_modules as <node_modules>.
 *
 * Numbers are written as "#" and JavaScript's own string form; a quantity
 * is {"q": [value, unit]}, an uncertainty {"u": [low, high]}, an interval
 * {"i": [low, high, lowClosed, highClosed, defaultPointType]}, and dates as
 * in golden.js.
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
const SEED = Number(seedArg || 20251010);
const SCALE = Number(scaleArg || 1);
if (!nodeModules || !outDir) {
    console.error('usage: TZ=UTC node golden-intervals.js <node_modules> <output directory> [seed] [scale]');
    process.exit(2);
}
if (new Date().getTimezoneOffset() !== 0) {
    console.error('Run with TZ=UTC.');
    process.exit(2);
}
const lib = (p) => require(path.resolve(nodeModules, p));
const { DateTime, Date: CqlDate } = lib('cql-execution');
const { Interval } = lib('cql-execution/lib/datatypes/interval');
const { Quantity } = lib('cql-execution/lib/datatypes/quantity');
const { Uncertainty } = lib('cql-execution/lib/datatypes/uncertainty');
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

const FIELDS = ['year', 'month', 'day', 'hour', 'minute', 'second', 'millisecond'];
const TYPES = {
    Integer: '{urn:hl7-org:elm-types:r1}Integer',
    Decimal: '{urn:hl7-org:elm-types:r1}Decimal',
    Date: '{urn:hl7-org:elm-types:r1}Date',
    DateTime: '{urn:hl7-org:elm-types:r1}DateTime',
    Quantity: '{urn:hl7-org:elm-types:r1}Quantity',
};

const num = (n) => '#' + String(n);
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
    if (value.isInterval) {
        return { i: [encode(value.low), encode(value.high), value.lowClosed, value.highClosed, value.defaultPointType ?? null] };
    }
    if (value.isUncertainty) {
        return { u: [encode(value.low), encode(value.high)] };
    }
    if (value.isQuantity) {
        return { q: [num(value.value), value.unit ?? null] };
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

// Points of each kind, drawn from small ranges so intervals overlap, meet and nest often.
const points = {
    Integer: () => pick([int(-3, 12), int(-3, 12), 0, 2147483647, -2147483648]),
    Decimal: () => pick([int(-3, 12) + pick([0.5, 0.25, 0.1, 0]), int(0, 10), 2.00000001, 1e19]),
    Quantity: () => new Quantity(int(0, 12) + pick([0, 0, 0.5]), pick(['mg', 'mg', 'mg', 'g', 'kg', 'mL', 'h', 'd', 'a'])),
    Date: () => {
        const year = pick([2024, 2025]);
        const month = int(1, 12);
        const precision = pick([1, 2, 3, 3, 3]);
        return new CqlDate(year, precision >= 2 ? month : null, precision >= 3 ? pick([1, 15, 28, 31].filter((d) => d <= 28 || month !== 2).filter((d) => d <= 30 || [1, 3, 5, 7, 8, 10, 12].includes(month))) : null);
    },
    DateTime: () => {
        const values = [pick([2024, 2025]), int(1, 12), pick([1, 15, 28]), int(0, 23), pick([0, 30, 59]), pick([0, 59]), pick([0, 999])];
        const precision = pick([1, 2, 3, 4, 5, 6, 7, 7, 7, 3]);
        return new DateTime(...values.map((v, i) => (i < precision ? v : null)), pick([0, 0, 0, -5, 5.5]));
    },
};
const KINDS = Object.keys(points);

function randomInterval(kind) {
    let low = rand() < 0.15 ? null : points[kind]();
    let high = rand() < 0.15 ? null : points[kind]();
    if (low != null && high != null && rand() < 0.8 && comparison.greaterThan(low, high)) {
        [low, high] = [high, low];
    }
    if (rand() < 0.1 && low != null) {
        high = low;
    }
    const lowClosed = rand() < 0.65;
    const highClosed = rand() < 0.65;
    return new Interval(low, high, lowClosed, highClosed, rand() < 0.5 ? TYPES[kind] : undefined);
}

const intervals = {};
for (const kind of KINDS) {
    intervals[kind] = Array.from({ length: 50 * SCALE }, () => randomInterval(kind));
    intervals[kind].push(new Interval(null, null, true, true, TYPES[kind]), new Interval(null, null, false, false, TYPES[kind]),
        new Interval(null, null, true, true), new Interval(null, null, false, true, TYPES[kind]));
}

const PRECISIONS = {
    Integer: [null], Decimal: [null], Quantity: [null],
    Date: [null, 'year', 'month', 'day'],
    DateTime: [null, 'year', 'month', 'day', 'hour', 'minute', 'second', 'millisecond'],
};
const PAIR_OPS = ['properlyIncludes', 'includes', 'includedIn', 'overlaps', 'overlapsAfter', 'overlapsBefore', 'sameAs',
    'sameOrBefore', 'sameOrAfter', 'after', 'before', 'meets', 'meetsAfter', 'meetsBefore', 'starts', 'ends'];
const POINT_OPS = ['contains', 'includes', 'includedIn', 'overlaps', 'overlapsAfter', 'overlapsBefore', 'after', 'before'];

for (const kind of KINDS) {
    const file = 'interval-' + kind.toLowerCase();
    const list = intervals[kind];
    for (let i = 0; i < 70 * SCALE; i++) {
        const a = pick(list);
        const b = rand() < 0.1 ? a : (rand() < 0.05 ? pick(intervals[pick(KINDS)]) : pick(list));
        const p = pick(PRECISIONS[kind]);
        for (const op of PAIR_OPS) {
            record(file, op, [a, b, p], attempt(() => a[op](b, p ?? undefined) ?? null));
        }
        record(file, 'union', [a, b], attempt(() => a.union(b) ?? null));
        record(file, 'intersect', [a, b], attempt(() => a.intersect(b) ?? null));
        record(file, 'except', [a, b], attempt(() => a.except(b) ?? null));
        record(file, 'equals', [a, b], attempt(() => a.equals(b) ?? null));
        record(file, 'comparison.equals', [a, b], attempt(() => comparison.equals(a, b) ?? null));
        record(file, 'comparison.equivalent', [a, b], attempt(() => comparison.equivalent(a, b) ?? null));
        const point = rand() < 0.1 ? null : points[kind]();
        for (const op of POINT_OPS) {
            record(file, op, [a, point, p], attempt(() => a[op](point, p ?? undefined) ?? null));
        }
    }
    for (const a of list) {
        for (const op of ['start', 'end', 'toClosed', 'width', 'size', 'getPointSize', 'toString', 'copy']) {
            record(file, op, [a], attempt(() => a[op]() ?? null));
        }
        record(file, 'pointType', [a], attempt(() => a.pointType ?? null));
        record(file, 'except', [a, null], attempt(() => a.except(null) ?? null));
        record(file, 'overlaps', [a, null], attempt(() => a.overlaps(null) ?? null));
        record(file, 'contains', [a, a], attempt(() => a.contains(a) ?? null));
    }
}

// The successor, predecessor and limits of points.
const limitPoints = [...KINDS.flatMap((kind) => Array.from({ length: 25 * SCALE }, () => points[kind]())),
    2147483647, -2147483648, 1e20, -1e20, 1.5, 0, null, 'text', true,
    new Uncertainty(1, 5), new Uncertainty(2147483646, 2147483647), new Uncertainty(-2147483648, 0),
    new DateTime(9999, 12, 31, 23, 59, 59, 999, 0), new DateTime(1, 1, 1, 0, 0, 0, 0, 0), new CqlDate(9999, 12, 31),
    new CqlDate(1, 1, 1), new DateTime(9999, 12, 31, null, null, null, null, 0), new Quantity(2147483647, 'mg')];
for (const v of limitPoints) {
    for (const op of ['successor', 'predecessor', 'maxValueForInstance', 'minValueForInstance']) {
        record('limits', op, [v], attempt(() => math[op](v) ?? null));
    }
}
for (const type of [...Object.values(TYPES), 'other', null]) {
    record('limits', 'maxValueForType', [type], attempt(() => math.maxValueForType(type) ?? null));
    record('limits', 'minValueForType', [type], attempt(() => math.minValueForType(type) ?? null));
    const q = new Quantity(3, 'mg');
    record('limits', 'maxValueForType', [type, q], attempt(() => math.maxValueForType(type, q) ?? null));
    record('limits', 'minValueForType', [type, q], attempt(() => math.minValueForType(type, q) ?? null));
}

fs.mkdirSync(outDir, { recursive: true });
for (const [name, cases] of Object.entries(files)) {
    const file = path.join(outDir, `${name}.json`);
    fs.writeFileSync(file, JSON.stringify({ generator: 'golden-intervals.js', seed: SEED, scale: SCALE, cases }) + '\n');
    console.error(`${file}: ${cases.length} cases, ${fs.statSync(file).size} bytes`);
}
