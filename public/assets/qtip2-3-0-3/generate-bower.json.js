var fs = require('fs');
var pkg = require('./package.json');

var bower = {
	name: pkg.name,
	description: pkg.description,
	version: pkg.version,
	homepage: pkg.homepage,
	repository: pkg.repository,
	author: pkg.author,
	license: pkg.licenses.map(function (license) {
		return license.type;
	}),
	keywords: pkg.keywords,
	main: [
		'./dist/jquery.qtip.js',
		'./dist/jquery.qtip.css'
	],
	dependencies: pkg.dependencies
};

fs.writeFile('bower.json', JSON.stringify(bower, null, '\t'));

