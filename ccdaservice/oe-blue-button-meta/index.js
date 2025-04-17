var CCDA = require("./lib/CCDA");

//CCDA metadata stuff
var meta = {};
meta.CCDA = CCDA;

meta.supported_sections = [
    'allergies',
    'procedures',
    'immunizations',
    'medications',
    'encounters',
    'vitals',
    'results',
    'social_history',
    'demographics',
    'problems',
    'insurance',
    'claims',
    'plan_of_care',
    'payers',
    'providers',
    'organizations',
    'reason_for_referral',
    'hospital_discharge_instructions'
];

meta.code_systems = require("./lib/code-systems");

module.exports = exports = meta;
