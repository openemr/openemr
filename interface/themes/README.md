# OpenEMR-interface

The OpenEMR-interface uses the [Fabricator](http://fbrctr.github.io/) UI toolkit to document and standardize the creation of user interface elements. The project is using bootstrap as base. It contains different materials that can be assembled into more complex page layouts.

The live version of this guide can be found at `url`.

## Getting Started

OpenEMR-interface requires [node.js](http://nodejs.org). Make sure your have `v0.10` or higher installed before proceeding.

**Start the local development environment:**

```
$ npm start
```

### Development Environment Features

- Live preview sever (using [BrowserSync](http://www.browsersync.io/))
- CSS Autoprefixing
- Sass compilation (not yet using in our current themes)
- Browserify bundling
- Image optimization

## Build

**Build for release:**

```
$ npm run build
```

Fabricator builds both a static documentation site and optimized CSS and JS toolkit files.

The build artifacts output to the `dist` directory. This can be deployed to any static hosting environment - no language runtime or database is required.

## TODOs
- [ ] Add a lot of documentation on current component usage (including migrating the "buttons at the bottom of form" sections, below)
- [ ] Migrate style dependencies in the php code to use the /dist directory
- [ ] Migrate component css still left in the `/themes` directory into scss in `/src/assets`


Buttons at bottom of form
-----
Sample code for buttons at the bottom of form:

```php
<div class="form-group clearfix">
    <div class="col-sm-12 col-sm-offset-1 position-override">
        <div class="btn-group oe-opt-btn-group-pinch" role="group">
            <button type='submit' onclick='top.restoreSession()' class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
            <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>';"><?php echo xlt('Cancel');?></button>
        </div>
    </div>
</div>
```
#### Classes
When adding buttons to the bottom of forms, will be important to incorporate following classes.

`position-override` gives a hook for style to change placement of buttons. In light/manila style this is ignored and buttons go to left positioned under data entry field. Whereas in the other styles this is used to center the buttons.

`oe-opt-btn-group-pinch` gives a hook for style to pinch the buttons (i think make them more rounded). Not used in light/manila, but used in other styles.

`oe-opt-btn-separate-left` gives a hook to place a space between the buttons. Not used in light/manila, but used in other styles.

#### Miscellaneous

(note there is also flexibility in how the Cancel links are shown. For example, in light, it's simple a link (not a button). And in Manila and other styles , some neat work was done to make it a button, but less accented than the Save buttons.)
