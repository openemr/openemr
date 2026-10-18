#!/usr/bin/env node
/**
 * Writes src/Cqm/Cql/Qdm/QdmSchema.php, the attribute types of every QDM
 * data element, entity and component in cqm-models (the QDM model
 * cqm-execution calculates with), read from its mongoose schemas.
 *
 * Each attribute is written as a kind: a type name (Code, DateTime,
 * Interval, Quantity, Any, AnyEntity, Date, String, Number, ObjectId), a
 * list of one ("[Code]"), a nested schema by its name ("@FacilityLocation"
 * or "[@Component]"), or a string default ("=encounter").
 *
 * Usage: node qdm-schema.js <node_modules> <output file>
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
    console.error('usage: node qdm-schema.js <node_modules> <output file>');
    process.exit(2);
}
const root = path.resolve(nodeModules, 'cqm-models');
const version = require(path.join(root, 'package.json')).version;
const all = require(path.join(root, 'app/assets/javascripts/AllDataElements'));

/** A nested schema is named by its _type default, e.g. QDM::FacilityLocation. */
function schemaName(schema) {
    const type = schema.paths._type;
    return type ? type.defaultValue.replace(/^QDM::/, '') : null;
}

const nested = {};
function kinds(schema) {
    const out = {};
    for (const [name, p] of Object.entries(schema.paths)) {
        let kind;
        if (p.$isMongooseDocumentArray) {
            const sub = schemaName(p.schema);
            nested[sub] = kinds(p.schema);
            kind = `[@${sub}]`;
        } else if (p.instance === 'Embedded') {
            const sub = schemaName(p.schema);
            nested[sub] = kinds(p.schema);
            kind = `@${sub}`;
        } else if (p.instance === 'Array') {
            kind = `[${p.caster ? p.caster.instance : 'Mixed'}]`;
        } else if (typeof p.defaultValue === 'string') {
            kind = `=${p.defaultValue}`;
        } else {
            kind = p.instance;
        }
        out[name] = kind;
    }
    return out;
}

const types = {};
for (const name of Object.keys(all).sort()) {
    if (typeof all[name] !== 'function' || /Schema$/.test(name) || name === 'QDMPatient') {
        continue;
    }
    types[name] = kinds(new all[name]({}).schema);
}

const php = (value) => "'" + String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
const entry = (name, attrs) => `        ${php(name)} => [${Object.entries(attrs).map(([k, v]) => `${php(k)} => ${php(v)}`).join(', ')}],`;
const block = (map) => Object.keys(map).sort().map((name) => entry(name, map[name])).join('\n');

fs.writeFileSync(outFile, `<?php

/**
 * The attribute types of the QDM data elements, entities and components of
 * cqm-models ${version}, generated from its mongoose schemas by
 * tests/Tests/Isolated/Cqm/Cql/qdm-schema.js. Do not edit by hand.
 *
 * Each attribute is a type name, a list of one ("[Code]"), a nested schema
 * ("@FacilityLocation", "[@Component]"), or a string default ("=encounter").
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\\Cqm\\Cql\\Qdm;

final class QdmSchema
{
    /**
     * Data elements and entities, by type name without the QDM:: prefix.
     *
     * @var array<string, array<string, string>>
     */
    public const TYPES = [
${block(types)}
    ];

    /**
     * Schemas nested in a data element: components, facility locations, identifiers.
     *
     * @var array<string, array<string, string>>
     */
    public const NESTED = [
${block(nested)}
    ];
}
`);
