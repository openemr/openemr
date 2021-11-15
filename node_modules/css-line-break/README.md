css-line-break
==============

![CI](https://github.com/niklasvh/html2canvas/workflows/CI/badge.svg?branch=master)
[![NPM Downloads](https://img.shields.io/npm/dm/css-line-break.svg)](https://www.npmjs.org/package/css-line-break)
[![NPM Version](https://img.shields.io/npm/v/css-line-break.svg)](https://www.npmjs.org/package/css-line-break)

A JavaScript library for Line Breaking and identifying Word Boundaries, 
[implementing the Unicode Line Breaking Algorithm (UAX #14)](http://unicode.org/reports/tr14/)  

>> Line breaking, also known as word wrapping, is the process of breaking a section of text into 
lines such that it will fit in the available width of a page, window or other display area. 
The Unicode Line Breaking Algorithm performs part of this process. Given an input text, 
it produces a set of positions called "break opportunities" that are appropriate points to 
begin a new line. The selection of actual line break positions from the set of break opportunities 
is not covered by the Unicode Line Breaking Algorithm, but is in the domain of higher level 
software with knowledge of the available width and the display size of the text.

In addition, the module implements CSS specific tailoring options to line breaking as 
defined in [CSS Text Module Level 3](https://www.w3.org/TR/css-text-3/#line-breaking).

### Installing
You can install the module via npm:

    npm install css-line-break
  
### Usage
The `LineBreaker` creates an [iterator](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Iterators_and_Generators) that returns `Break`s for a given text.

    LineBreaker(text, [options]);
    
### Example
[JSFiddle](https://jsfiddle.net/ofd3752k)
```javascript
import {LineBreaker} from 'css-line-break';

const breaker = LineBreaker('Lorem ipsum lol.', {
    lineBreak: 'strict',
    wordBreak: 'normal'
});

const words = [];
let bk;

while (!(bk = breaker.next()).done) {
    words.push(bk.value.slice());
}

assert.deepEqual(words, ['Lorem ', 'ipsum ', 'lol.']);
```    
### Options
The following parameters are available for the options:

 - `lineBreak`: `normal` | `strict`
 - `wordBreak`: `normal` | `break-all` | `break-word` | `keep-all`
 
For more information how they affect the line breaking algorithms, 
check out [CSS Text Module Level 3](https://www.w3.org/TR/css-text-3/#line-breaking).

### Testing
You can run the test suite with:

    npm test

The library implements all the [LineBreakTest.txt tests](http://www.unicode.org/Public/10.0.0/ucd/auxiliary/LineBreakTest.txt) 
 and a number of CSS web-platform-tests.