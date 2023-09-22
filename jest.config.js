/** @type {import('jest').Config} */
const config = {
    modulePathIgnorePatterns: [
        'public/assets',
        'vendor'
    ],
    coverageDirectory: 'coverage/js-unit',
    collectCoverageFrom: ['**/*.js'],
    coveragePathIgnorePatterns: [
        'gulpfile.js',
        'jest.config.js',
        'node_modules',
        'ccdaservice/node_modules',
        'coverage',
        'interface/forms/eye_mag/js/jquery-1-10-2',
        'interface/forms/eye_mag/js/jquery-panelslider',
        'interface/forms/eye_mag/js/jquery-ui-1-11-4',
        'interface/forms/eye_mag/js/jquery-1-10-2',
        'interface/forms/questionnaire_assessments/lforms/fhir',
        'interface/forms/questionnaire_assessments/lforms/webcomponent',
        'interface/modules/zend_modules/public/js/lib',
        'interface/super/rules/www/js/cdr-multiselect',
        'portal/patient/scripts/libs',
        'public/assets',
        'swagger',
        'tests',
        'vendor'
    ]
};
  
module.exports = config;
