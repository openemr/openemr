# CKEditor 4 LTS - Smart WYSIWYG HTML editor [![Tweet](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/intent/tweet?text=Check%20out%20CKEditor%204%20on%20GitHub&url=https%3A%2F%2Fgithub.com%2Fckeditor%2Fckeditor4)

[![npm version](https://badge.fury.io/js/ckeditor4.svg)](https://www.npmjs.com/package/ckeditor4)
[![GitHub tag](https://img.shields.io/github/tag/ckeditor/ckeditor4.svg)](https://github.com/ckeditor/ckeditor4-releases)
[![Build Status](https://app.travis-ci.com/ckeditor/ckeditor4.svg?branch=master)](https://app.travis-ci.com/ckeditor/ckeditor4)

[![Join newsletter](https://img.shields.io/badge/join-newsletter-00cc99.svg)](http://eepurl.com/c3zRPr)
[![Follow Twitter](https://img.shields.io/badge/follow-twitter-00cc99.svg)](https://twitter.com/ckeditor)

## ⚠️ CKEditor 4: End of Life and Extended Support Model until Dec 2026

CKEditor 4 was launched in 2012 and reached its End of Life (EOL) on June 30, 2023.

A special edition, **[CKEditor 4 LTS](https://ckeditor.com/ckeditor-4-support/)** ("Long Term Support"), is available under commercial terms (["Extended Support Model"](https://ckeditor.com/ckeditor-4-support/)) for anyone looking to **extend the coverage of security updates and critical bug fixes**.

With CKEditor 4 LTS, security updates and critical bug fixes are guaranteed until December 2026.

## About this package

### CKEditor 4 LTS

After June 30, 2023 the `master` version of the [LICENSE.md](https://github.com/ckeditor/ckeditor4/blob/master/LICENSE.md) file changed to reflect the license of CKEditor 4 LTS available under the Extended Support Model.

All future versions of CKEditor 4 are released as CKEditor 4 LTS distributions and require a license key.

### Getting CKEditor 4 (Open Source)

You may continue using CKEditor 4.22.* and below under the open source license terms. Please note, however, that the open source version no longer comes with any security updates, so your application will be at risk.

In order to install the open source version of CKEditor 4, use ****versions 4.22.1 and below****. CKEditor 4.22.1 was the last version of CKEditor 4 available under the open source license terms.

## Summary of options after the CKEditor 4 End of Life

### Upgrading to CKEditor 5

CKEditor 5 is a great new editor with [lots of exciting features](https://ckeditor.com/docs/ckeditor5/latest/features/index.html).

Before upgrading, please be aware of the following changes:

- CKEditor 5 is a completely new editor. **Upgrading is not as simple as replacing the folder with "ckeditor"** - read more in the [Migration from CKEditor 4](https://ckeditor.com/docs/ckeditor5/latest/updating/ckeditor4/migration-from-ckeditor-4.html) guide.
- CKEditor 5 is available only under the GPL copyleft license (or under a commercial license).
- Open source projects with a GPL-incompatible license may apply for a license under the [Free for Open Source](https://ckeditor.com/wysiwyg-editor-open-source/) program.

### Using an outdated, unsupported version

You may continue using CKEditor 4.22.* (or below). The license terms of the older CKEditor 4 versions have not changed. However, please note that by using software that is no longer maintained, you are introducing a **security risk to your application**.

### Signing an "Extended Support Model" contract

If you are not ready to replace CKEditor 4 in your application yet, you may continue using CKEditor 4 until December 2026.
CKEditor 4 LTS, available under the "[Extended Support Model](https://ckeditor.com/ckeditor-4-support/)", will ship all important security updates and critical bug fixes, providing an interrupted editing experience for your end users. Please note that this version of CKEditor 4 is available only under a special agreement and requires a license key.

## About CKEditor 4

A highly configurable WYSIWYG HTML editor with hundreds of features, from creating rich text content with captioned images, videos, tables, media embeds, emoji, or mentions to pasting from Word and Google Docs and drag&drop image upload.

It supports a broad range of browsers, including legacy ones.

![CKEditor 4 screenshot](https://c.cksource.com/a/1/img/npm/ckeditor4.png)

## Getting started

### Using [npm package](https://www.npmjs.com/package/ckeditor4)

```bash
npm install --save ckeditor4
```

Use it on your website:

```html
<div id="editor">
    <p>This is the editor content.</p>
</div>
<script src="./node_modules/ckeditor4/ckeditor.js"></script>
<script>
    CKEDITOR.replace( 'editor' );
</script>
```

You can also load CKEditor 4 using [CDN](https://cdn.ckeditor.com/#ckeditor4).

#### CKEditor 4 LTS

Since the introduction of the LTS version of CKEditor (`4.23.0-lts`) in July 2023, all future versions of CKEditor 4 will contain `-lts` in their version number.

## Features

* Over 500 plugins in the [Add-ons Repository](https://ckeditor.com/cke4/addons).
* Pasting from Microsoft Word, Excel, and Google Docs.
* Drag&drop image uploads.
* Media embeds to insert videos, tweets, maps, or slideshows.
* Powerful clipboard integration.
* Content quality control with Advanced Content Filter.
* Extensible widget system.
* Custom table selection.
* Accessibility conforming to WCAG and Section 508.
* Over 70 localizations available with full RTL support.

## Presets

The CKEditor 4 npm package comes in the `standard-all` preset, so it includes all official CKEditor plugins, with those from the [standard package](https://sdk.ckeditor.com/samples/standardpreset.html) active by default.

## Further Resources

* [CKEditor 4 demo](https://ckeditor.com/ckeditor-4/)
* [Documentation](https://ckeditor.com/docs/ckeditor4/latest/)
* [API documentation](https://ckeditor.com/docs/ckeditor4/latest/api/index.html)
* [Configuration reference](https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_config.html)
* [CKEditor SDK with more samples](https://sdk.ckeditor.com/)

If you are looking for CKEditor 5, here's a link to the relevant npm package: <https://www.npmjs.com/package/ckeditor5>

## Browser support

| [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/edge/edge_48x48.png" alt="IE / Edge" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br>IE / Edge | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/firefox/firefox_48x48.png" alt="Firefox" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br>Firefox | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/chrome/chrome_48x48.png" alt="Chrome" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br>Chrome | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/chrome/chrome_48x48.png" alt="Chrome" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br>Chrome (Android) | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/safari/safari_48x48.png" alt="Safari" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br>Safari | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/safari-ios/safari-ios_48x48.png" alt="iOS Safari" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br>iOS Safari | [<img src="https://raw.githubusercontent.com/alrra/browser-logos/master/src/opera/opera_48x48.png" alt="Opera" width="24px" height="24px" />](http://godban.github.io/browsers-support-badges/)<br>Opera |
| --------- | --------- | --------- | --------- | --------- | --------- | --------- |
| IE8, IE9, IE10, IE11, Edge| latest version| latest version| latest version| latest version| latest version| latest version

Find out more in the [Browser Compatibility guide](https://ckeditor.com/docs/ckeditor4/latest/guide/dev_browsers.html#officially-supported-browsers).

## License

Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.

For licensing, see LICENSE.md or [https://ckeditor.com/legal/ckeditor-oss-license](https://ckeditor.com/legal/ckeditor-oss-license)

### CKEditor 4.22.* and below

CKEditor 4 until version 4.22.* was licensed under the terms of any of the following licenses of your choice:

 - GNU General Public License Version 2 or later.
 - GNU Lesser General Public License Version 2.1 or later.
 - Mozilla Public License Version 1.1 or later.

### CKEditor 4.23.0-lts and above

CKEditor 4 LTS (starting from version 4.23.0-lts) is available under a commercial license only.
