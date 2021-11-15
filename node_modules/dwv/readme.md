DWV
===

DWV (DICOM Web Viewer) is an open source zero footprint medical image viewer library. It uses _only_ javascript and HTML5 technologies, meaning that it can be run on any platform that provides a modern browser (laptop, tablet, phone and even modern TVs). It can load local or remote data in DICOM format (the standard for medical imaging data such as MR, CT, Echo, Mammo, NM...) and  provides standard tools for its manipulation such as contrast, zoom, drag, possibility to draw regions on top of the image and imaging filters such as threshold and sharpening.

[![Build Status](https://travis-ci.org/ivmartel/dwv.svg?branch=master)](https://travis-ci.org/ivmartel/dwv) [![Coverage Status](https://img.shields.io/coveralls/ivmartel/dwv.svg)](https://coveralls.io/r/ivmartel/dwv?branch=master) [![Code Climate](https://codeclimate.com/github/ivmartel/dwv.svg)](https://codeclimate.com/github/ivmartel/dwv) [![Dependency Status](https://david-dm.org/ivmartel/dwv.svg)](https://david-dm.org/ivmartel/dwv) [![npm](https://img.shields.io/npm/v/dwv.svg)](https://www.npmjs.com/package/dwv) [![Bower](https://img.shields.io/bower/v/dwv.svg)](https://libraries.io/bower/dwv)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fivmartel%2Fdwv.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fivmartel%2Fdwv?ref=badge_shield)

Try a [live demo](https://ivmartel.github.io/dwv/) and read a lot more information on the [wiki](https://github.com/ivmartel/dwv/wiki) (such as [Dicom-Support](https://github.com/ivmartel/dwv/wiki/Dicom-Support) or [Pacs-Support](https://github.com/ivmartel/dwv/wiki/Pacs-Support)). You can also check out the [example viewers](https://github.com/ivmartel/dwv/wiki/Examples#viewers-and-integrations) based on dwv.

 - All coding/implementation contributions and comments are welcome.
 - DWV is not certified for diagnostic use.<sup>[1](#footnote1)</sup>
 - Released under GNU GPL-3.0 license (see [license.txt](license.txt)).

And for those who want to support the dwv development:

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=VQWYY8ZS75H3E&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted)

## Steps to run the viewer from scratch

Get the code:
```sh
git clone https://github.com/ivmartel/dwv.git
```

Move to its folder:
```sh
cd dwv
```

Install dependencies:
```sh
yarn install
```

Call the test script to launch the tests on a local server:
```sh
yarn run test
```

You can now open a browser at http://localhost:8080 and enjoy!

## Available Scripts

``` bash
# install dependencies
yarn install

# run linting
yarn run lint

# run unit tests with hot reload at localhost:8080
yarn run test

# create release files
yarn run build

# create documentation
yarn run doc

# watch for changes and copy build (to be connected with a demo viewer)
yarn run dev

```
Using `yarn` as the main package manager. Best to use it to install since
the lock file (that contains the exact dependency tree) is a yarn file.
All scripts also work with `npm`.

## Notes

<a name="footnote1">1</a>: Certification refers to official medical software certification that are issued by the FDA or EU Notified Bodies. The sentence here serves as a reminder that the Dicom Web Viewer is not ceritifed, and comes with no warranties (and no possible liability of its authors) as stated in the [license](license.txt). To learn more about standards used in certification, see the [wikipedia Medical software](https://en.wikipedia.org/wiki/Medical_software) page. If you have additional questions, please [open an issue](https://www.github.com/ivmartel/dwv/issues).
