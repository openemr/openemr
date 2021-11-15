'use strict';

module.exports = {
  extends: ['ash-nazg/sauron-node'],
  parserOptions: {
    sourceType: 'module'
  },
  env: {
    node: true,
    browser: true
  },
  overrides: [
    {
      files: '*.md',
      processor: 'markdown/markdown'
    },
    {
      files: '**/*.md/*.js',
      globals: {
        require: true,
        StackBlur: true,
        width: true,
        height: true,
        top_x: true,
        top_y: true,
        radius: true,
        imageData: true,
        sourceImage: true,
        targetCanvas: true,
        blurAlphaChannel: true
      },
      rules: {
        'import/unambiguous': 0,
        'import/no-commonjs': 0,
        'node/no-missing-import': 0,
        'import/no-unresolved': ['error', {
          ignore: ['stackblur-canvas']
        }],
        'node/no-missing-require': ['error', {
          allowModules: ['stackblur-canvas']
        }],
        'no-shadow': 0,
        'no-unused-vars': ['error', {varsIgnorePattern: 'StackBlur'}]
      }
    }, {
      files: 'rollup.config.js',
      env: {
        node: true
      }
    }, {
      files: '.*.js',
      extends: ['plugin:node/recommended-script'],
      rules: {
        'import/unambiguous': 0,
        'import/no-commonjs': 0
      }
    }
  ],
  rules: {
    'jsdoc/require-returns': ['error', {exemptedBy: ['see']}],
    // Handled by Babel
    'node/no-unsupported-features/es-syntax': 0,

    // Would be good, but as not supported in older Node and browsers,
    //   would need polyfill for `Number.isNaN`
    'unicorn/prefer-number-properties': 0,
    'unicorn/prefer-math-trunc': 0
  }
};
