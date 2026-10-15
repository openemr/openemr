#!/usr/bin/env node
/**
 * Writes the golden results the PHP CQL types must reproduce, by running
 * cql-execution's own DateTime, Date, Uncertainty and comparison code over a
 * deterministic set of generated inputs.
 *
 * Run with TZ=UTC: cql-execution reads the machine timezone where the PHP
 * port uses UTC (CqlDateTime::LOCAL_OFFSET).
 *
 * Usage: TZ=UTC node golden.js <node_modules> <output directory> [seed] [scale]
 *
 * The reference engine is not part of OpenEMR. Install the versions the
 * fixtures were made with anywhere, e.g.
 *   npm install --prefix /tmp/cqm-reference cqm-execution@4.4.3 cqm-models@4.3.2
 * and pass /tmp/cqm-reference/node_modules as <node_modules>.
 *
 * Writes one file per group of operations, each small enough for the
 * repository's large-file check. A date/time is written compactly as
 * "dt|year|month|day|hour|minute|second|millisecond|offset" and a date as
 * "d|year|month|day", with empty fields where the value has no such part.
 *
 * The committed fixture uses the defaults; a different seed and a larger
 * scale make a stress run for checking changes to the PHP types.
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
const SEED = Number(seedArg || 20251008);
const SCALE = Number(scaleArg || 1);
if (!nodeModules || !outDir) {
    console.error('usage: TZ=UTC node golden.js <node_modules> <output directory> [seed] [scale]');
    process.exit(2);
}
if (new Date().getTimezoneOffset() !== 0) {
    console.error('Run with TZ=UTC.');
    process.exit(2);
}
const cql = require(path.resolve(nodeModules, 'cql-execution'));
const { DateTime, Date: CqlDate } = cql;
const { Uncertainty } = require(path.resolve(nodeModules, 'cql-execution/lib/datatypes/uncertainty'));
const comparison = require(path.resolve(nodeModules, 'cql-execution/lib/util/comparison'));

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

const UNITS = ['year', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond'];
const FIELDS = ['year', 'month', 'day', 'hour', 'minute', 'second', 'millisecond'];
const PRECISIONS = [null, ...FIELDS];
const OFFSETS = [0, 0, 0, -5, 5.5, -3.5, 14, -12, 1];
const DAYS_IN_MONTH = (y, m) => new Date(Date.UTC(y, m, 0)).getUTCDate();

/** A DateTime with random precision, biased toward month ends and leap days. */
function randomDateTime() {
    const year = pick([2023, 2024, 2025, 2025, 2026, 2000, 1999, 2100]);
    const month = int(1, 12);
    const dim = DAYS_IN_MONTH(year, month);
    const day = rand() < 0.4 ? pick([1, dim, dim - 1, 28, 29].filter((d) => d <= dim)) : int(1, dim);
    const values = [year, month, day, rand() < 0.3 ? pick([0, 23]) : int(0, 23), int(0, 59), int(0, 59), int(0, 999)];
    const precision = pick([1, 2, 3, 4, 5, 6, 7, 7, 7, 3]);
    const fields = values.map((v, i) => (i < precision ? v : null));
    return new DateTime(...fields, pick(OFFSETS));
}

function randomDate() {
    const year = pick([2023, 2024, 2025, 2000, 2100]);
    const month = int(1, 12);
    const dim = DAYS_IN_MONTH(year, month);
    const day = rand() < 0.4 ? pick([1, dim, 28, 29].filter((d) => d <= dim)) : int(1, dim);
    const precision = pick([1, 2, 3, 3, 3]);
    return new CqlDate(year, precision >= 2 ? month : null, precision >= 3 ? day : null);
}

/** Encodes a value the PHP test rebuilds. */
function encode(value) {
    if (value === null || value === undefined) {
        return null;
    }
    if (value.isDateTime) {
        return ['dt', ...FIELDS.map((f) => value[f] ?? ''), value.timezoneOffset ?? ''].join('|');
    }
    if (value.isDate) {
        return ['d', value.year ?? '', value.month ?? '', value.day ?? ''].join('|');
    }
    if (value.isUncertainty) {
        return { u: [encode(value.low), encode(value.high)] };
    }
    return value;
}

/** Runs a call, recording an error the same way the PHP side does. */
function attempt(fn) {
    try {
        return encode(fn());
    } catch {
        return { error: true };
    }
}

const cases = [];
const record = (op, args, result) => cases.push([op, args.map(encode), result]);

const dateTimes = Array.from({ length: 85 * SCALE }, randomDateTime);
const dates = Array.from({ length: 60 * SCALE }, randomDate);

// Pairwise orderings, equality and differences.
for (let i = 0; i < 120 * SCALE; i++) {
    const a = pick(dateTimes);
    const b = rand() < 0.15 ? a.copy() : pick(dateTimes);
    for (const p of PRECISIONS) {
        for (const op of ['sameAs', 'sameOrBefore', 'sameOrAfter', 'before', 'after']) {
            record(op, [a, b, p], attempt(() => a[op](b, p ?? undefined)));
        }
    }
    record('equals', [a, b], attempt(() => a.equals(b)));
    record('equivalent', [a, b], attempt(() => a.equivalent(b)));
    for (const unit of UNITS) {
        record('differenceBetween', [a, b, unit], attempt(() => a.differenceBetween(b, unit)));
        record('durationBetween', [a, b, unit], attempt(() => a.durationBetween(b, unit)));
    }
}

// Arithmetic, conversion and neighbours of single values.
for (const a of dateTimes) {
    for (const unit of UNITS) {
        for (const amount of [1, -1, 13, -25, 0, 1.5, -0.25, 400]) {
            record('add', [a, amount, unit], attempt(() => a.add(amount, unit)));
        }
    }
    for (const o of [0, -5, 5.5, 14, -12, null]) {
        record('convertToTimezoneOffset', [a, o], attempt(() => a.convertToTimezoneOffset(o)));
    }
    record('successor', [a], attempt(() => a.successor()));
    record('predecessor', [a], attempt(() => a.predecessor()));
    record('toString', [a], attempt(() => a.toString()));
    record('getPrecision', [a], attempt(() => a.getPrecision()));
    for (const p of FIELDS) {
        record('reducedPrecision', [a, p], attempt(() => a.reducedPrecision(p)));
    }
}

// Dates, alone and against DateTimes.
for (let i = 0; i < 80 * SCALE; i++) {
    const a = pick(dates);
    const b = rand() < 0.3 ? pick(dateTimes) : pick(dates);
    for (const p of [null, 'year', 'month', 'day']) {
        for (const op of ['sameAs', 'sameOrBefore', 'sameOrAfter', 'before', 'after']) {
            record(op, [a, b, p], attempt(() => a[op](b, p ?? undefined)));
        }
    }
    record('equals', [a, b], attempt(() => a.equals(b)));
    record('equivalent', [a, b], attempt(() => a.equivalent(b)));
    for (const unit of ['year', 'month', 'week', 'day']) {
        record('differenceBetween', [a, b, unit], attempt(() => a.differenceBetween(b, unit)));
        record('durationBetween', [a, b, unit], attempt(() => a.durationBetween(b, unit)));
    }
}
for (const a of dates) {
    for (const unit of ['year', 'month', 'week', 'day']) {
        for (const amount of [1, -1, 13, -25, 400, 1.5]) {
            record('add', [a, amount, unit], attempt(() => a.add(amount, unit)));
        }
    }
    record('successor', [a], attempt(() => a.successor()));
    record('predecessor', [a], attempt(() => a.predecessor()));
    record('toString', [a], attempt(() => a.toString()));
}

// Parsing, valid and invalid.
const strings = [
    '2025', '2025-02', '2025-02-28', '2024-02-29', '2025-02-29', '2025-13', '2025-00-10', '2025-04-31',
    '2025-06-14T', '2025-06-14TZ', '2025-06-14T-04:00', '2025-06-14T08', '2025-06-14T08Z', '2025-06-14T08-04:00',
    '2025-06-14T08:30', '2025-06-14T08:30Z', '2025-06-14T08:30+05:30', '2025-06-14T08:30:59',
    '2025-06-14T08:30:59Z', '2025-06-14T08:30:59-04:00', '2025-06-14T08:30:59.1', '2025-06-14T08:30:59.12',
    '2025-06-14T08:30:59.123', '2025-06-14T08:30:59.12345Z', '2025-06-14T08:30:59.123+00:00',
    '2025-06-14T24:00', '2025-06-14T23:60', '2025-06-14T08:30:61', '2025-06-14T08:30+0530', '2025-06-14T08:30+05',
    '2025-06-14T08:30:59.5-04', 'abc', '20250614', '2025-6-14', ' 2025-06-14', '2025-06-14 08:30',
];
for (const s of strings) {
    record('parseDateTime', [s], attempt(() => DateTime.parse(s)));
    record('parseDate', [s], attempt(() => CqlDate.parse(s)));
}

// cqm-models' conversion of patient data strings.
for (const s of ['2025-06-14T02:53:07.183+00:00', '2025-06-14T02:53:07+00:00', '2025-06-14T02:53:07.183-05:00',
    '2025-06-14', '2025-06-14T02:53:07.183Z', '2024-02-29T23:59:59.999+01:00']) {
    record('fromQdmString', [s], attempt(() => DateTime.fromJSDate(new Date(s), 0)));
}

// Uncertainties of numbers.
const ranges = [[1, 1], [1, 3], [2, 2], [3, 1], [0, 5], [null, 4], [4, null], [5, 9]];
for (const [l1, h1] of ranges) {
    for (const [l2, h2] of ranges) {
        const a = new Uncertainty(l1, h1);
        const b = new Uncertainty(l2, h2);
        for (const op of ['lessThan', 'greaterThan', 'lessThanOrEquals', 'greaterThanOrEquals', 'equals']) {
            record('uncertainty.' + op, [[l1, h1], [l2, h2]], attempt(() => a[op](b)));
        }
        record('uncertainty.isPoint', [[l1, h1]], attempt(() => a.isPoint()));
    }
}

// Generic comparison over primitives and lists.
const primitives = [null, 1, 1.0, 2, 2.5, '1', 'a', 'A', 'á', 'b', 'a b', 'a\tb', '10', '9', true, false, [1, 2], [1, null], [1, 2, 3], [], ['a'], ['A']];
for (const a of primitives) {
    for (const b of primitives) {
        for (const op of ['equals', 'equivalent', 'lessThan', 'lessThanOrEquals', 'greaterThan', 'greaterThanOrEquals']) {
            record('comparison.' + op, [a, b], attempt(() => comparison[op](a, b)));
        }
    }
}

const GROUPS = {
    ordering: ['sameAs', 'sameOrBefore', 'sameOrAfter', 'before', 'after'],
    difference: ['differenceBetween', 'durationBetween', 'equals', 'equivalent'],
    arithmetic: ['add', 'successor', 'predecessor'],
};
const groupOf = (op) => Object.keys(GROUPS).find((g) => GROUPS[g].includes(op)) ?? 'other';
const engine = `cql-execution ${require(path.resolve(nodeModules, 'cql-execution/package.json')).version}`;
fs.mkdirSync(outDir, { recursive: true });
for (const group of [...Object.keys(GROUPS), 'other']) {
    const groupCases = cases.filter((c) => groupOf(c[0]) === group);
    fs.writeFileSync(path.join(outDir, `${group}.json`), `${JSON.stringify({ engine, cases: groupCases })}\n`);
    console.log(`${group}: ${groupCases.length} cases`);
}
