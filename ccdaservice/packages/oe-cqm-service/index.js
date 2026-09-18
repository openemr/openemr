const Calculator = require('cqm-execution').Calculator;
const Parsers = require('oe-cqm-parsers');

// Example fixture loader (useful for this example).
const fs = require('fs');
const getJSONFixture = function(fixturePath) {
    var contents = fs.readFileSync(fixturePath);
    return JSON.parse(contents);
};

// Load value sets from test fixtures, the getJSONFixture base path is spec/fixtures/json
const valueSets = getJSONFixture('fixtures/json/cqm_measures/CMS137v7/value_sets.json');

// Load a measure from test fixtures.
const measure = getJSONFixture('fixtures/json//cqm_measures/CMS137v7/CMS137v7.json');

// Load in an example patient from test fixtures
let patients = [];
// The calculator will return results for each patient in this array
patients.push(getJSONFixture('fixtures/json/patients/CMS137v7/Dependency<60daysSB4_DENEXPop>18StratPass.json'));

// Example options; includes directive to produce pretty statement results.
const options = { doPretty: true };

// Calculate results.
const calculationResults = Calculator.calculate(measure, patients, valueSets, options);
