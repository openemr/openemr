'use strict';

var acorn = require('acorn');
var postcss = require('postcss');
var SourceMapGenerator = require('source-map').SourceMapGenerator;

function generateJs(sourcePath, fileContent) {
  var generator = new SourceMapGenerator({ file: sourcePath });
  var tokenizer = acorn.tokenizer(fileContent, {
    allowHashBang: true,
    locations: true,
  });

  /* eslint no-constant-condition: 0 */
  while (true) {
    var token = tokenizer.getToken();

    if (token.type.label === 'eof') {
      break;
    }
    var mapping = {
      original: token.loc.start,
      generated: token.loc.start,
      source: sourcePath,
    };
    if (token.type.label === 'name') {
      mapping.name = token.value;
    }
    generator.addMapping(mapping);
  }
  generator.setSourceContent(sourcePath, fileContent);

  return generator.toJSON();
}

var postcssSourceMapOptions = {
  inline: false,
  prev: false,
  sourcesContent: true,
  annotation: false,
};

function generateCss(sourcePath, fileContent) {
  var root = postcss.parse(fileContent, { from: sourcePath });
  var result = root.toResult({ to: sourcePath, map: postcssSourceMapOptions });

  return result.map.toJSON();
}

module.exports = {
  js: generateJs,
  css: generateCss,
};
