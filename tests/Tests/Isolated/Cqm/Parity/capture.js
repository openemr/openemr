#!/usr/bin/env node
/**
 * Captures cqm-execution results as parity fixtures for the PHP CQM engine.
 *
 * For every measure of a reporting year this generates a deterministic set of
 * QDM 5.6 patients from the measure's own data criteria and value sets, runs
 * them through cqm-execution with the options OpenEMR sends, and writes the
 * patients and results to one JSON fixture per measure.
 *
 * Measures whose populations random patients rarely reach also get seed
 * patients, written by hand in seeds/<year>/<measure>.json. Each seed names
 * the measure's data criteria and value sets; seeds are always kept and also
 * serve as bases for the generated variations.
 *
 * The engine is called directly and awaited. The oe-cqm-service HTTP wrapper
 * calls the async Calculator.calculate() without awaiting it and so answers
 * every request with {}, which cannot serve as a reference.
 *
 * Usage, from a checkout whose ccdaservice has node_modules installed:
 *   node capture.js <ccdaservice/node_modules> <json_measures dir> <year> <out dir> [patients per measure] [measure...]
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

const [nodeModules, measuresDir, year, outDir, perMeasureArg, ...only] = process.argv.slice(2);
if (!nodeModules || !measuresDir || !year || !outDir) {
    console.error('usage: capture.js <node_modules> <json_measures dir> <year> <out dir> [patients per measure] [measure...]');
    process.exit(2);
}
const Calculator = require(path.resolve(nodeModules, 'cqm-execution')).Calculator;
const KEEP = Number(perMeasureArg || 30);
// Candidates generated per measure; the fixture keeps the KEEP of them that
// together reach the most distinct population and statement outcomes.
const POOL = 200;
// Further candidates built by changing ones that reached a denominator,
// over a few generations.
const GENERATIONS = 3;
const PER_GENERATION = 100;

const PERIOD_START = Date.UTC(Number(year), 0, 1);
const PERIOD_END = Date.UTC(Number(year), 11, 31, 23, 59, 59);
const DAY = 86400000;

// The options CqmCalculator::calculateMeasure() sends.
const OPTIONS = {
    doPretty: true,
    includeClauseResults: true,
    requestDocument: true,
    effectiveDate: `${year}0101000000`,
    effectiveDateEnd: null,
};
// What CqmCalculator puts in the system of a negated element's value set code.
const OID_SYSTEM = '1.2.3.4.5.6.7.8.9.10';
const DEMOGRAPHICS = new Set([
    'QDM::PatientCharacteristicSex',
    'QDM::PatientCharacteristicRace',
    'QDM::PatientCharacteristicEthnicity',
    'QDM::PatientCharacteristicPayer',
    'QDM::PatientCharacteristicBirthdate',
    'QDM::PatientCharacteristicExpired',
]);
const DEFAULT_DEMOGRAPHICS = {
    'QDM::PatientCharacteristicSex': [['F', '2.16.840.1.113883.5.1'], ['M', '2.16.840.1.113883.5.1']],
    'QDM::PatientCharacteristicRace': [['2106-3', '2.16.840.1.113883.6.238'], ['2054-5', '2.16.840.1.113883.6.238']],
    'QDM::PatientCharacteristicEthnicity': [['2186-5', '2.16.840.1.113883.6.238'], ['2135-2', '2.16.840.1.113883.6.238']],
    'QDM::PatientCharacteristicPayer': [['1', '2.16.840.1.113883.3.221.5'], ['2', '2.16.840.1.113883.3.221.5']],
};
const PERIOD_KEYS = ['relevantPeriod', 'prevalencePeriod', 'participationPeriod'];
const DATETIME_KEYS = ['relevantDatetime', 'authorDatetime', 'resultDatetime', 'incisionDatetime', 'sentDatetime', 'receivedDatetime'];

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

function hashString(s) {
    let h = 2166136261;
    for (let i = 0; i < s.length; i++) {
        h = Math.imul(h ^ s.charCodeAt(i), 16777619);
    }
    return h >>> 0;
}

function iso(ms) {
    return new Date(ms).toISOString().replace('Z', '+00:00');
}

/**
 * Units the measure's ELM compares results against, from quantity literals
 * and from string comparisons such as result.unit = 'mm[Hg]'.
 */
function quantityUnits(measure) {
    const units = new Set();
    const isUnitProperty = (op) => op && op.type === 'Property' && op.path === 'unit';
    const walk = (node) => {
        if (Array.isArray(node)) {
            node.forEach(walk);
        } else if (node && typeof node === 'object') {
            if (node.type === 'Quantity' && typeof node.unit === 'string' && node.unit !== '') {
                units.add(node.unit);
            }
            if (['Equal', 'Equivalent'].includes(node.type) && Array.isArray(node.operand) && node.operand.some(isUnitProperty)) {
                for (const op of node.operand) {
                    if (op.type === 'Literal' && typeof op.value === 'string') {
                        units.add(op.value);
                    }
                }
            }
            Object.values(node).forEach(walk);
        }
    };
    measure.cql_libraries.forEach((lib) => walk(lib.elm));
    return [...units].sort();
}

/**
 * Builds random patients for one measure from its data criteria. create()
 * makes a fresh patient; extend() copies one and adds a few more criteria,
 * which is how numerators are reached: most are a denominator patient plus
 * one qualifying event.
 */
function patientFactory(measure, valueSets) {
    const rand = mulberry32(hashString(`${measure.cms_id}:${year}`));
    const pick = (list) => list[Math.floor(rand() * list.length)];
    const chance = (p) => rand() < p;
    const byOid = new Map(valueSets.map((vs) => [vs.oid, vs]));
    const codeFrom = (oid) => {
        const vs = byOid.get(oid);
        if (!vs || vs.concepts.length === 0) {
            return null;
        }
        const c = pick(vs.concepts);
        return { code: c.code, system: c.code_system_oid };
    };
    const named = (pattern) => valueSets.filter((vs) => pattern.test(vs.display_name)).map((vs) => vs.oid);
    const reasonSets = named(/reason/i);
    const dischargeSets = named(/discharge/i);
    const units = quantityUnits(measure);

    const criteria = measure.source_data_criteria.filter((c) => !DEMOGRAPHICS.has(c._type) && byOid.has(c.codeListId));
    const diagnosisSets = criteria.filter((c) => c._type === 'QDM::Diagnosis').map((c) => c.codeListId);
    // Value sets no data criterion retrieves are only used to filter attributes
    // (a principal diagnosis, a discharge disposition, a facility location).
    const retrieved = new Set(measure.source_data_criteria.map((c) => c.codeListId));
    const attributeSets = valueSets.filter((vs) => !retrieved.has(vs.oid) && vs.concepts.length > 0).map((vs) => vs.oid);
    const attributeCode = (preferred) => {
        const pool = preferred.length > 0 && (attributeSets.length === 0 || chance(0.5)) ? preferred : attributeSets;
        return pool.length > 0 ? codeFrom(pick(pool)) : null;
    };
    const resultSets = valueSets.filter((vs) => vs.concepts.length > 0).map((vs) => vs.oid);
    const demographicCriteria = (type) => measure.source_data_criteria.filter((c) => c._type === type && byOid.has(c.codeListId));

    // A time somewhere around the measurement period; most land inside it.
    // Given anchors (times already on the patient), it often lands beside one,
    // as data elements from the same visit do.
    const when = (anchors = []) => {
        if (anchors.length > 0 && chance(0.5)) {
            return pick(anchors) + rand() * 4 * 3600000;
        }
        const r = rand();
        if (r < 0.7) {
            return PERIOD_START + rand() * (PERIOD_END - PERIOD_START);
        }
        if (r < 0.92) {
            return PERIOD_START - rand() * 3 * 365 * DAY;
        }
        return PERIOD_END + rand() * 60 * DAY;
    };
    const span = () => (chance(0.6) ? rand() * DAY : rand() * 30 * DAY);

    const prefix = hashString(measure.cms_id).toString(16).padStart(8, '0');
    let serial = 0;
    const nextId = () => {
        serial += 1;
        return prefix + serial.toString(16).padStart(16, '0');
    };
    let patientCount = 0;

    const element = (criterion, anchors) => {
        const el = {};
        for (const [k, v] of Object.entries(criterion)) {
            // Empty attributes are left out; cqm-models fills the same defaults.
            const empty = v === null || (Array.isArray(v) && v.length === 0);
            if (!empty && !['_id', 'id', 'codeListId', 'description', 'hqmfOid', 'qrdaOid'].includes(k)) {
                el[k] = v;
            }
        }
        el.dataElementCodes = [codeFrom(criterion.codeListId)];
        const t = when(anchors);
        anchors.push(t);
        // Where a datatype allows either, real data carries a point in time or a period, not both.
        const asPoint = 'relevantDatetime' in criterion && 'relevantPeriod' in criterion ? chance(0.6) : null;
        for (const key of PERIOD_KEYS) {
            if (key in criterion && !(key === 'relevantPeriod' && asPoint === true)) {
                el[key] = { low: iso(t), high: chance(0.1) ? null : iso(t + span()), lowClosed: true, highClosed: true };
            }
        }
        for (const key of DATETIME_KEYS) {
            if (key in criterion && !(key === 'relevantDatetime' && asPoint === false)) {
                el[key] = iso(key === 'resultDatetime' ? t + rand() * DAY : t);
            }
        }
        if ('result' in criterion && chance(0.85)) {
            if (units.length > 0 && chance(0.7)) {
                el.result = { value: Math.round(rand() * 2000) / 10, unit: pick(units) };
            } else if (chance(0.5)) {
                el.result = Math.round(rand() * 200);
            } else {
                el.result = codeFrom(pick(resultSets));
            }
        }
        if ('components' in criterion && chance(0.5)) {
            el.components = [{
                _type: 'QDM::Component',
                code: codeFrom(pick(resultSets)),
                result: units.length > 0 ? { value: Math.round(rand() * 2000) / 10, unit: pick(units) } : Math.round(rand() * 200),
            }];
        }
        if ('diagnoses' in criterion && chance(0.5)) {
            const code = attributeCode(diagnosisSets);
            if (code) {
                el.diagnoses = [{ _type: 'QDM::DiagnosisComponent', code, rank: 1, presentOnAdmissionIndicator: null }];
            }
        }
        if ('dischargeDisposition' in criterion && chance(0.3)) {
            el.dischargeDisposition = attributeCode(dischargeSets);
        }
        if ('facilityLocations' in criterion && attributeSets.length > 0 && chance(0.2)) {
            el.facilityLocations = [{
                _type: 'QDM::FacilityLocation',
                code: codeFrom(pick(attributeSets)),
                locationPeriod: { low: iso(t), high: iso(t + span()), lowClosed: true, highClosed: true },
            }];
        }
        if ('negationRationale' in criterion && reasonSets.length > 0 && chance(0.2)) {
            // OpenEMR records a negated act against the value set itself.
            el.negationRationale = codeFrom(pick(reasonSets));
            el.dataElementCodes = [{ code: criterion.codeListId, system: OID_SYSTEM }];
            for (const key of PERIOD_KEYS) {
                delete el[key];
            }
            el.authorDatetime = iso(t);
        }
        const id = nextId();
        return { ...el, id, _id: id };
    };

    const timesOf = (elements) => elements.flatMap((el) => [
        el.relevantDatetime, el.authorDatetime, el.relevantPeriod?.low, el.prevalencePeriod?.low,
    ]).filter((v) => typeof v === 'string').map((v) => Date.parse(v))
        .filter((t) => t >= PERIOD_START - 365 * DAY);

    const addCriteria = (elements, count) => {
        const anchors = timesOf(elements);
        for (let n = 0; n < Math.min(count, criteria.length); n++) {
            const criterion = pick(criteria);
            elements.push(element(criterion, anchors));
            if (chance(0.2)) {
                elements.push(element(criterion, anchors));
            }
        }
    };

    const wrap = (birth, elements, pubpid = null) => {
        patientCount += 1;
        const id = nextId();
        return {
            _id: id,
            id,
            _type: 'QDM::Patient',
            qdmVersion: '5.6',
            birthDatetime: iso(birth),
            dataElements: elements,
            extendedData: { pubpid: pubpid ?? `${measure.cms_id}-${String(patientCount).padStart(3, '0')}` },
        };
    };

    // A seed is exactly what its file says, so it never gets a random death.
    const demographics = (birth, mayExpire = true) => {
        const elements = [];
        const add = (el) => {
            const id = nextId();
            elements.push({ ...el, id, _id: id });
        };
        for (const type of Object.keys(DEFAULT_DEMOGRAPHICS)) {
            const declared = demographicCriteria(type);
            const code = declared.length > 0 ? codeFrom(pick(declared).codeListId) : null;
            const [c, s] = code ? [code.code, code.system] : pick(DEFAULT_DEMOGRAPHICS[type]);
            const el = { _type: type, dataElementCodes: [{ code: c, system: s }], qdmVersion: '5.6' };
            if (type === 'QDM::PatientCharacteristicPayer') {
                el.relevantPeriod = { low: iso(PERIOD_START - 365 * DAY), high: null, lowClosed: true, highClosed: true };
            }
            add(el);
        }
        add({
            _type: 'QDM::PatientCharacteristicBirthdate',
            birthDatetime: iso(birth),
            dataElementCodes: [{ code: '21112-8', system: '2.16.840.1.113883.6.1' }],
            qdmVersion: '5.6',
        });
        const expired = demographicCriteria('QDM::PatientCharacteristicExpired');
        if (mayExpire && expired.length > 0 && chance(0.1)) {
            add({
                _type: 'QDM::PatientCharacteristicExpired',
                expiredDatetime: iso(when()),
                dataElementCodes: [codeFrom(pick(expired).codeListId)],
                qdmVersion: '5.6',
            });
        }
        return elements;
    };

    const valueSetNamed = (name, where) => {
        const vs = valueSets.find((v) => v.display_name === name && v.concepts.length > 0);
        if (!vs) {
            throw new Error(`${measure.cms_id} seed: no value set or code named "${name}" (${where})`);
        }
        return vs;
    };
    // A seed's codes are the first concept of the named value set or direct reference code.
    const firstCode = (name, where) => {
        const c = valueSetNamed(name, where).concepts[0];
        return { code: c.code, system: c.code_system_oid };
    };
    const SEED_KEYS = new Set(['ref', 'type', 'valueSet', 'result', 'clazz', 'diagnoses', 'relatedTo', ...PERIOD_KEYS, ...DATETIME_KEYS]);

    const seedElement = (item, where) => {
        for (const key of Object.keys(item)) {
            if (!SEED_KEYS.has(key)) {
                throw new Error(`${measure.cms_id} seed: unknown key "${key}" (${where})`);
            }
        }
        const type = `QDM::${item.type}`;
        const vs = valueSetNamed(item.valueSet, where);
        const criterion = measure.source_data_criteria.find((c) => c._type === type && c.codeListId === vs.oid);
        if (!criterion) {
            throw new Error(`${measure.cms_id} seed: the measure has no ${item.type} criterion for "${item.valueSet}" (${where})`);
        }
        const el = {};
        for (const [k, v] of Object.entries(criterion)) {
            const empty = v === null || (Array.isArray(v) && v.length === 0);
            if (!empty && !['_id', 'id', 'codeListId', 'description', 'hqmfOid', 'qrdaOid'].includes(k)) {
                el[k] = v;
            }
        }
        el.dataElementCodes = [firstCode(item.valueSet, where)];
        for (const key of PERIOD_KEYS) {
            if (key in item) {
                el[key] = { low: item[key][0], high: item[key][1], lowClosed: true, highClosed: true };
            }
        }
        for (const key of DATETIME_KEYS) {
            if (key in item) {
                el[key] = item[key];
            }
        }
        if ('authorDatetime' in criterion && !('authorDatetime' in item)) {
            el.authorDatetime = item.relevantDatetime ?? item.relevantPeriod?.[0] ?? item.prevalencePeriod?.[0] ?? null;
        }
        if ('result' in item) {
            el.result = item.result !== null && typeof item.result === 'object' && 'code' in item.result
                ? firstCode(item.result.code, where)
                : item.result;
        }
        if ('clazz' in item) {
            el.clazz = firstCode(item.clazz.code, where);
        }
        if ('diagnoses' in item) {
            el.diagnoses = item.diagnoses.map((d) => ({
                _type: 'QDM::DiagnosisComponent',
                code: firstCode(d.code, where),
                rank: d.rank ?? null,
                presentOnAdmissionIndicator: null,
            }));
        }
        const id = nextId();
        return { ...el, id, _id: id };
    };

    return {
        /** Builds a hand-written seed patient (see seeds/). */
        seed(spec, index) {
            const where = `${measure.cms_id} seed ${index + 1}`;
            const birth = Date.parse(spec.birthDatetime);
            const elements = demographics(birth, false);
            if (spec.sex) {
                const sex = elements.find((el) => el._type === 'QDM::PatientCharacteristicSex');
                sex.dataElementCodes = [{ code: spec.sex, system: '2.16.840.1.113883.5.1' }];
            }
            const refs = new Map();
            const pending = [];
            for (const item of spec.elements) {
                const el = seedElement(item, where);
                if (item.ref) {
                    refs.set(item.ref, el.id);
                }
                if (item.relatedTo) {
                    pending.push([el, item.relatedTo]);
                }
                elements.push(el);
            }
            for (const [el, names] of pending) {
                el.relatedTo = names.map((ref) => {
                    if (!refs.has(ref)) {
                        throw new Error(`${where}: relatedTo names unknown ref "${ref}"`);
                    }
                    return refs.get(ref);
                });
            }
            return wrap(birth, elements, `${measure.cms_id}-seed-${index + 1}`);
        },
        create() {
            const ageYears = chance(0.15) ? rand() * 18 : 18 + rand() * 75;
            const birth = Math.floor((PERIOD_START - ageYears * 365.25 * DAY) / 60000) * 60000;
            const elements = demographics(birth);
            // A few criteria per patient, so most patients meet some populations
            // and miss others instead of tripping every exclusion at once.
            addCriteria(elements, 2 + Math.floor(rand() * 11));
            return wrap(birth, elements);
        },
        extend(base) {
            let elements = base.dataElements.map((el) => {
                const id = nextId();
                return { ...JSON.parse(JSON.stringify(el)), id, _id: id };
            });
            // Dropping elements can lift an exclusion; adding them can reach a numerator.
            if (chance(0.5)) {
                const drop = 1 + Math.floor(rand() * 3);
                for (let n = 0; n < drop; n++) {
                    const removable = elements.filter((el) => !DEMOGRAPHICS.has(el._type));
                    if (removable.length > 1) {
                        const victim = pick(removable);
                        elements = elements.filter((el) => el !== victim);
                    }
                }
            }
            addCriteria(elements, (chance(0.3) ? 0 : 1) + Math.floor(rand() * 4));
            return wrap(Date.parse(base.birthDatetime), elements);
        },
    };
}

/**
 * The adjustments CqmCalculator::calculateMeasure() makes before sending
 * patients: a negated element also carries the first concept of its value
 * set, and every SubstanceOrder is duplicated as a MedicationOrder.
 */
function applyOpenEmrPreprocessing(patients, valueSets) {
    const byOid = new Map(valueSets.map((vs) => [vs.oid, vs]));
    for (const patient of patients) {
        const added = [];
        for (const el of patient.dataElements) {
            if (el.negationRationale) {
                const extra = [];
                for (const code of el.dataElementCodes) {
                    if (code.system === OID_SYSTEM) {
                        const vs = byOid.get(code.code);
                        if (vs && vs.concepts.length > 0) {
                            extra.push({ code: vs.concepts[0].code, system: vs.concepts[0].code_system_oid });
                        }
                    }
                }
                el.dataElementCodes.push(...extra);
            }
            if (el._type === 'QDM::SubstanceOrder') {
                added.push({
                    _type: 'QDM::MedicationOrder',
                    authorDatetime: el.authorDatetime ?? null,
                    dataElementCodes: el.dataElementCodes,
                    negationRationale: el.negationRationale ?? null,
                    relevantPeriod: el.relevantPeriod ?? null,
                    frequency: el.frequency ?? null,
                    qdmVersion: '5.6',
                });
            }
        }
        patient.dataElements.push(...added);
    }
}

const POPULATION_KEYS = ['STRAT', 'IPP', 'DENOM', 'NUMER', 'NUMEX', 'DENEX', 'DENEXCEP', 'MSRPOPL', 'MSRPOPLEX', 'OBSERV'];

/** The parts of a result OpenEMR consumes, plus statement finals to localize differences. */
function summarize(result) {
    const out = {};
    for (const key of POPULATION_KEYS) {
        if (key in result) {
            out[key] = result[key];
        }
    }
    if (result.observation_values && result.observation_values.length > 0) {
        out.observation_values = result.observation_values;
    }
    if (result.episode_results && Object.keys(result.episode_results).length > 0) {
        out.episode_results = result.episode_results;
    }
    const statements = {};
    for (const s of result.statement_results || []) {
        statements[s.library_name] ??= {};
        statements[s.library_name][s.statement_name] = s.final;
    }
    out.statements = statements;
    return out;
}

/** Outcomes a patient's results demonstrate; population outcomes weigh more than statement ones. */
function features(summary) {
    const out = new Map();
    for (const [key, result] of Object.entries(summary)) {
        for (const p of POPULATION_KEYS) {
            if (typeof result[p] === 'number') {
                out.set(`${key}|${p}|${result[p]}`, 10);
            }
        }
        if (result.observation_values) {
            out.set(`${key}|observation`, 10);
        }
        for (const [lib, statements] of Object.entries(result.statements)) {
            for (const [statement, final] of Object.entries(statements)) {
                out.set(`${lib}|${statement}|${final}`, 1);
            }
        }
    }
    return out;
}

/**
 * Keeps the seeds, then greedily the patients that add the most uncovered
 * outcomes, in generation order.
 */
function selectByCoverage(candidates, summaries, seeds) {
    const covered = new Set();
    const featureSets = new Map(candidates.map((p) => [p._id, features(summaries.get(p._id))]));
    const chosen = new Set();
    for (const seed of seeds) {
        chosen.add(seed._id);
        for (const f of featureSets.get(seed._id).keys()) {
            covered.add(f);
        }
    }
    while (chosen.size < KEEP) {
        let best = null;
        let bestGain = 0;
        for (const p of candidates) {
            if (chosen.has(p._id)) {
                continue;
            }
            let gain = 0;
            for (const [f, weight] of featureSets.get(p._id)) {
                if (!covered.has(f)) {
                    gain += weight;
                }
            }
            if (gain > bestGain) {
                best = p;
                bestGain = gain;
            }
        }
        if (best === null) {
            break;
        }
        chosen.add(best._id);
        for (const f of featureSets.get(best._id).keys()) {
            covered.add(f);
        }
    }
    return candidates.filter((p) => chosen.has(p._id));
}

function preprocessed(patients, valueSets) {
    const copies = JSON.parse(JSON.stringify(patients));
    applyOpenEmrPreprocessing(copies, valueSets);
    return copies;
}

const failures = [];

async function engine(measure, valueSets, patients) {
    // Round-trip through JSON as the HTTP service would.
    return JSON.parse(JSON.stringify(await Calculator.calculate(
        JSON.parse(JSON.stringify(measure)),
        preprocessed(patients, valueSets),
        valueSets,
        OPTIONS,
    )));
}

/**
 * Runs patients through the engine as OpenEMR would and summarizes each
 * patient's results. The engine fails a whole batch when one patient raises
 * an error, so a failed batch is retried patient by patient and the patients
 * that fail are left out.
 */
async function calculate(measure, valueSets, allPatients) {
    let patients = allPatients;
    let raw;
    try {
        raw = await engine(measure, valueSets, patients);
    } catch {
        raw = {};
        const kept = [];
        for (const patient of patients) {
            try {
                Object.assign(raw, await engine(measure, valueSets, [patient]));
                kept.push(patient);
            } catch (e) {
                failures.push({ measure: measure.cms_id, patient, error: String(e.cause?.message || e.message), library: e.libraryName });
            }
        }
        patients = kept;
    }
    return new Map(patients.map((patient) => {
        const byKey = raw[patient._id] || {};
        return [patient._id, Object.fromEntries(Object.entries(byKey).map(([k, r]) => [k, summarize(r)]))];
    }));
}

const reachedDenominator = (r) => r.DENOM > 0 || r.MSRPOPL > 0 || (r.IPP > 0 && !('DENOM' in r) && !('MSRPOPL' in r));
const excluded = (r) => r.DENEX > 0 || r.DENEXCEP > 0 || r.MSRPOPLEX > 0;

async function captureMeasure(dir) {
    const name = path.basename(dir);
    const measure = JSON.parse(fs.readFileSync(path.join(dir, `${name}.json`), 'utf8'));
    const valueSets = JSON.parse(fs.readFileSync(path.join(dir, 'value_sets.json'), 'utf8'));
    const factory = patientFactory(measure, valueSets);
    const fresh = Array.from({ length: POOL }, () => factory.create());
    const summaries = await calculate(measure, valueSets, fresh);

    const seedFile = path.join(path.dirname(module.filename), 'seeds', year, `${name}.json`);
    const seeds = fs.existsSync(seedFile)
        ? JSON.parse(fs.readFileSync(seedFile, 'utf8')).patients.map((spec, i) => factory.seed(spec, i))
        : [];
    if (seeds.length > 0) {
        // A seed is written by hand, so an engine error is a mistake to fix, not a patient to drop.
        const raw = await engine(measure, valueSets, seeds);
        for (const seed of seeds) {
            const byKey = raw[seed._id];
            if (!byKey || Object.keys(byKey).length === 0) {
                throw new Error(`${name} seed ${seed.extendedData.pubpid}: the engine returned no result`);
            }
            summaries.set(seed._id, Object.fromEntries(Object.entries(byKey).map(([k, r]) => [k, summarize(r)])));
        }
    }

    let candidates = [...fresh, ...seeds];
    const pickBase = mulberry32(hashString(`${name}:bases`));
    for (let g = 0; g < GENERATIONS; g++) {
        const reached = candidates.filter((p) => summaries.has(p._id) && Object.values(summaries.get(p._id)).some(reachedDenominator));
        if (reached.length === 0) {
            break;
        }
        // Prefer bases no exclusion caught, where a numerator is still possible.
        const open = reached.filter((p) => !Object.values(summaries.get(p._id)).some(excluded));
        const changed = Array.from({ length: PER_GENERATION }, () => {
            const pool = open.length > 0 && pickBase() < 0.7 ? open : reached;
            return factory.extend(pool[Math.floor(pickBase() * pool.length)]);
        });
        for (const [id, summary] of await calculate(measure, valueSets, changed)) {
            summaries.set(id, summary);
        }
        candidates = [...candidates, ...changed];
    }

    const patients = selectByCoverage(candidates.filter((p) => summaries.has(p._id)), summaries, seeds);
    const results = Object.fromEntries(patients.map((p) => [p._id, summaries.get(p._id)]));
    const engineError = patients.length === 0
        ? (failures.find((f) => f.measure === measure.cms_id)?.error ?? 'no results')
        : null;
    return { name, hqmfId: measure.hqmf_id, patients: preprocessed(patients, valueSets), results, engineError };
}

async function main() {
    fs.mkdirSync(outDir, { recursive: true });
    const dirs = fs.readdirSync(measuresDir)
        .filter((d) => fs.statSync(path.join(measuresDir, d)).isDirectory())
        .filter((d) => only.length === 0 || only.includes(d))
        .sort();
    const engine = require(path.resolve(nodeModules, 'cqm-execution', 'package.json')).version;
    const unsupported = [];
    for (const dir of dirs) {
        const { name, hqmfId, patients, results, engineError } = await captureMeasure(path.join(measuresDir, dir));
        // When every generated patient fails, the reference engine cannot run
        // the measure at all; the fixture records that instead of results.
        const fixture = {
            measure: name,
            hqmfId,
            reportingYear: year,
            engine: `cqm-execution ${engine}`,
            engineError,
            options: OPTIONS,
            patients,
            results,
        };
        fs.writeFileSync(path.join(outDir, `${name}.json`), `${JSON.stringify(fixture)}\n`);
        if (engineError !== null) {
            unsupported.push(name);
            console.log(name, `no reference: ${engineError}`);
            continue;
        }
        const counts = {};
        for (const byKey of Object.values(results)) {
            for (const [k, r] of Object.entries(byKey)) {
                counts[k] ??= {};
                for (const p of POPULATION_KEYS) {
                    if (typeof r[p] === 'number' && r[p] > 0) {
                        counts[k][p] = (counts[k][p] || 0) + 1;
                    }
                }
            }
        }
        console.log(name, JSON.stringify(counts));
    }
    console.log(`${failures.length} generated patients failed in the engine and were left out`);
    console.log(`measures the engine cannot run: ${unsupported.join(' ') || 'none'}`);
}

main().catch((e) => {
    console.error(e);
    process.exit(1);
});
