const path = require ('path');

module.exports = {
  mode: 'production', // 'development', 'production', 'none'
  entry: './src/telehealth.js',
  output: {
	  path: path.resolve(__dirname, 'dist')
	  ,filename: 'telehealth.min.js',
  },
};
