/** @type {import('jest').Config} */
const config = {
    // Use different test environments based on file location
    projects: [
        // Frontend tests with jsdom environment
        {
            displayName: 'Frontend Tests',
            testMatch: ['<rootDir>/tests/frontend/**/*.test.js'],
            testEnvironment: 'jsdom',
            setupFiles: ['<rootDir>/tests/frontend/polyfills.js'],
            setupFilesAfterEnv: ['<rootDir>/tests/frontend/setup.js'],
            coverageDirectory: 'coverage/frontend',
            moduleNameMapper: {
                '^jquery$': '<rootDir>/tests/frontend/__mocks__/jquery.js',
                '^angular$': '<rootDir>/tests/frontend/__mocks__/angular.js',
                '^bootstrap$': '<rootDir>/tests/frontend/__mocks__/bootstrap.js'
            }
        },
        // Existing tests with node environment
        {
            displayName: 'Node Tests',
            testMatch: ['<rootDir>/**/*.{test,spec}.js'],
            testPathIgnorePatterns: ['<rootDir>/tests/frontend/'],
            testEnvironment: 'node',
            coverageDirectory: 'coverage/js-unit'
        }
    ],
    modulePathIgnorePatterns: [
        'public/assets',
        'vendor'
    ],
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
