// Mocha Specification Cases

// Imports
const assert = require('assert');
const fs =     require('fs');
const Vinyl =  require('vinyl');

// Plugin
const gap = require('../index.js');

// Object.values polyfill for Node <= 6
if(!Object.values) {
    Object.values = (obj) => Object.keys(obj).map((k) => obj[k]);
}

// Data
console.log('  Input files:');
fs.readdirSync('spec/fixture').forEach(file => console.log('    spec/fixture/' + file));
const page = {
    begin: '<!doctype html>\n<html>\n<head><title>GAP</title></head>',
    body:  '<body><h1>GAP</h1></body>',
    end:   '</html>'
};

////////////////////////////////////////////////////////////////////////////////////////////////////
describe('The gulp-append-prepend plugin', () => {

    it('is exported as an object', () => {
        const actual =   { type: typeof gap };
        const expected = { type: 'object' };
        assert.deepStrictEqual(actual, expected);
    });

    it('contains the functions: appendFile(), prependFile(), appendText(), prependText()', () => {
        const names = ['appendFile', 'prependFile', 'appendText', 'prependText'];
        const actual =   { functions: Object.keys(gap).sort() };
        const expected = { functions: names.sort() };
        assert.deepStrictEqual(actual, expected);
    });

    it('functions are the correct type', () => {
        const actual =   { types: Object.values(gap).map(v => typeof v) };
        const expected = { types: ['function', 'function', 'function', 'function'] };
        assert.deepStrictEqual(actual, expected);
    });

});

////////////////////////////////////////////////////////////////////////////////////////////////////
describe('The appendFile() function', () => {

    it('correctly appends file contents to the body of an HTML file', (done) => {
        const inputFile = 'spec/fixture/index.html';
        const handleFileFromStream = (file) => {
            const actual =   { page: file.contents.toString() };
            const expected = { page: page.body + '\n\n' + page.end };
            assert.deepStrictEqual(actual, expected);
            done();
        };
        const stream = gap.appendFile('spec/fixture/page-end.html');
        stream.on('data', handleFileFromStream);
        stream.write(new Vinyl({ contents: fs.readFileSync(inputFile) }));
        stream.end();
    });

});

////////////////////////////////////////////////////////////////////////////////////////////////////
describe('The prependFile() function', () => {

    it('correctly prepends file contents to the body of an HTML file', (done) => {
        const inputFile = 'spec/fixture/index.html';
        const handleFileFromStream = (file) => {
            const actual =   { page: file.contents.toString() };
            const expected = { page: page.begin + '\n' + page.body + '\n' };
            assert.deepStrictEqual(actual, expected);
            done();
        };
        const stream = gap.prependFile('spec/fixture/page-begin.html');
        stream.on('data', handleFileFromStream);
        stream.write(new Vinyl({ contents: fs.readFileSync(inputFile) }));
        stream.end();
    });

});

////////////////////////////////////////////////////////////////////////////////////////////////////
describe('The appendText() function', () => {

    it('correctly appends a string to the body of an HTML file', (done) => {
        const inputFile = 'spec/fixture/index.html';
        const handleFileFromStream = (file) => {
            const actual =   { page: file.contents.toString() };
            const expected = { page: page.body + '\n\n' + page.end };
            assert.deepStrictEqual(actual, expected);
            done();
        };
        const stream = gap.appendText(page.end);
        stream.on('data', handleFileFromStream);
        stream.write(new Vinyl({ contents: fs.readFileSync(inputFile) }));
        stream.end();
    });

});

////////////////////////////////////////////////////////////////////////////////////////////////////
describe('The prependText() function', () => {

    it('correctly prepends a string to the body of an HTML file', (done) => {
       const inputFile = 'spec/fixture/index.html';
        const handleFileFromStream = (file) => {
            const actual =   { page: file.contents.toString() };
            const expected = { page: page.begin + '\n' + page.body + '\n' };
            assert.deepStrictEqual(actual, expected);
            done();
        };
        const stream = gap.prependText(page.begin);
        stream.on('data', handleFileFromStream);
        stream.write(new Vinyl({ contents: fs.readFileSync(inputFile) }));
        stream.end();
    });

});
