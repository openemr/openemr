text-segmentation
==============

![CI](https://github.com/niklasvh/text-segmentation/workflows/CI/badge.svg?branch=main)
[![NPM Downloads](https://img.shields.io/npm/dm/text-segmentation.svg)](https://www.npmjs.org/package/text-segmentation)
[![NPM Version](https://img.shields.io/npm/v/text-segmentation.svg)](https://www.npmjs.org/package/text-segmentation)

A JavaScript library for Grapheme Breaking and identifying Grapheme Boundaries, [implementing the Unicode Line Breaking Algorithm (UAX #29)](https://unicode.org/reports/tr29/)

### Installing
You can install the module via npm:

    npm install text-segmentation
  
### Example
```javascript
import {splitGraphemes} from 'text-segmentation';

const graphemes =  splitGraphemes('Hello 👨‍👩‍👧‍👦!');
```    

### Testing
You can run the test suite with:

    npm test

The library implements all the [GraphemeBreakTest.txt tests](https://www.unicode.org/Public/13.0.0/ucd/auxiliary/GraphemeBreakTest.txt).
