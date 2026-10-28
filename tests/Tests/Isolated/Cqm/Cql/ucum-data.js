#!/usr/bin/env node
/**
 * Writes src/Cqm/Cql/Ucum/UcumData.php, the PHP copy of the prefix and unit
 * tables that ucum-lhc (the UCUM library cql-execution uses) ships in
 * data/ucumDefs.min.json. Only the fields that parsing and conversion read
 * are kept.
 *
 * Usage: node ucum-data.js <node_modules> <output file>
 *
 * The reference engine is not part of OpenEMR. Install the versions the
 * fixtures were made with anywhere, e.g.
 *   npm install --prefix /tmp/cqm-reference cqm-execution@4.4.3 cqm-models@4.3.2
 * and pass /tmp/cqm-reference/node_modules as <node_modules>.
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

const [nodeModules, outFile] = process.argv.slice(2);
if (!nodeModules || !outFile) {
    console.error('usage: node ucum-data.js <node_modules> <output file>');
    process.exit(2);
}
const root = path.resolve(nodeModules, '@lhncbc/ucum-lhc');
const version = require(path.join(root, 'package.json')).version;
const defs = require(path.join(root, 'data/ucumDefs.min.json'));

function unpack(packed) {
    const keys = packed.config.map((k) => (Array.isArray(k) ? k.join('.') : k));
    return packed.data.map((row) => Object.fromEntries(keys.map((k, i) => [k, row[i]])));
}

/** A PHP literal for a JSON-safe value; numbers keep JavaScript's shortest form. */
function php(value) {
    if (value === null || value === undefined) {
        return 'null';
    }
    if (typeof value === 'boolean') {
        return value ? 'true' : 'false';
    }
    if (typeof value === 'number') {
        return String(value);
    }
    if (Array.isArray(value)) {
        return '[' + value.map(php).join(', ') + ']';
    }
    return "'" + String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
}

/** Floats stay floats in PHP, so whole magnitudes get a decimal point. */
function phpFloat(value) {
    const s = String(value);
    return /[.e]/.test(s) ? s : s + '.0';
}

const prefixes = unpack(defs.prefixes).map((p) =>
    `        [${php(p.code_)}, ${phpFloat(p.value_)}, ${p.exp_ === null ? 'null' : Number(p.exp_)}],`);
const units = unpack(defs.units).map((u) => '        [' + [
    php(u.csCode_),
    php(u.name_),
    phpFloat(u.magnitude_),
    php(u['dim_.dimVec_']),
    php(u.cnv_),
    phpFloat(u.cnvPfx_),
    php(u.isSpecial_),
    php(u.isArbitrary_),
    php(u.moleExp_),
    php(u.equivalentExp_),
    php(u.source_ === 'LOINC'),
].join(', ') + '],');

const notice = fs.readFileSync(path.join(root, 'LICENSE.md'), 'utf8')
    .replace(/&#174;/g, '(R)').replace(/&#169;/g, '(c)')
    .split('\n').slice(2).map((l) => (' * ' + l).trimEnd()).join('\n');

fs.writeFileSync(outFile, `<?php

/**
 * The UCUM prefix and unit tables of ucum-lhc ${version}, generated from its
 * data/ucumDefs.min.json by tests/Tests/Isolated/Cqm/Cql/ucum-data.js. Do not
 * edit by hand.
 *
 * ucum-lhc is distributed under the following notice, which applies to this
 * file and to the classes ported from ucum-lhc in this directory:
 *
${notice.replace(/(\n \*)+$/, '')}
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\\Cqm\\Cql\\Ucum;

final class UcumData
{
    /**
     * Prefix code, value, power of ten (null for the binary prefixes).
     *
     * @var list<array{string, float, ?int}>
     */
    public const PREFIXES = [
${prefixes.join('\n')}
    ];

    /**
     * Code, name, magnitude, dimension vector, conversion function, conversion
     * prefix, special, arbitrary, mole exponent, equivalent exponent, and
     * whether the unit came from LOINC rather than UCUM itself.
     *
     * @var list<array{string, string, float, list<int>, ?string, float, bool, bool, int, int, bool}>
     */
    public const UNITS = [
${units.join('\n')}
    ];
}
`);
