const path = require ('path');

module.exports = {
  mode: 'development', // 'development', 'production', 'none'
  entry: './src/telehealth.js',
  output: {
	  path: path.resolve(__dirname, 'dist')
	  ,filename: 'telehealth.js',
  },
};
