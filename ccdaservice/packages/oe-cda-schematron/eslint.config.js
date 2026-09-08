const globals = require('globals');

module.exports = [
    {
        files: ['**/*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'commonjs',
            globals: {
                ...globals.browser,
                ...globals.node,
                ...globals.jquery,
                ...globals.mocha,
            },
        },
        rules: {
            'no-bitwise': 'error',
            eqeqeq: 'error',
            'guard-for-in': 'error',
            'wrap-iife': ['error', 'inside'],
            'no-caller': 'error',
            'no-empty': 'error',
            quotes: ['error', 'single', { avoidEscape: true }],
            'no-undef': 'error',
            'no-unused-vars': ['error', { caughtErrors: 'none' }],
        },
    },
];
