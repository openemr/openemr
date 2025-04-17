# OpenEMR-interface

The OpenEMR UI is built with [SASS](https://sass-lang.com/) on top of a bootstrap base (compiled with [gulp](https://gulpjs.com/)).

### Themes

Different `themes` share a common `core` and have their own overrides to customize the appearance of OpenEMR.

There are three different types of themes:
* The `light` theme is the default modern theme
* The `manila` theme is a combination of OpenEMR's legacy themes (which have all been removed) with some modern elements.
* The other themes (called `colors`) are the same `color_base` theme with different color palettes.

`rtl_` prefixed themes are built by appending the `rtl.css` file to every theme automatically. These overrides provide right to left adjustments for all `style*.css` files

Files specific to different themes are named with the following conventions:
* `themes/core` contain shared styles that all themes import toward the top of their files
* `themes/colors` contain all changes specific to the color theme work (led by [zbig01](https://github.com/zbig01))
* `themes/[component_name]` (e.g. `buttons` or `navigation-slide`) contain files named after each theme variant.
    * See TODOs on how we might be able to manage component-level styles in the future

### Special Classes

* `position-override` gives a hook for style to change placement of buttons. In light/manila style this is ignored and buttons go to left positioned under data entry field. Whereas in the other styles this is used to center the buttons.

## Getting Started

Compiling SASS files locally requires [node.js](http://nodejs.org) and [npm](https://www.npmjs.com/).

1. **Setup your local development environment** as described in [CONTRIBUTING.md](../CONTRIBUTING.md)

- If running on a local machine, run `npm install` from the root directory.
- If running in docker: `docker exec -it [your_container_id] /bin/sh` then cd into `openemr`

From here you can either:
* `npm run dev` - just compiles the local `.scss` files and recompiles them whenever they are changed.
* `npm run dev-sync` (EXPERIMENTAL*) - loads your local OpenEMR instance using BrowserSync (port 3000) in front of 80 (feel free to edit the package.json to change the port)
    * [See video of `dev-sync` in action](https://imgur.com/a/C0dVnfq)

## TODOs
- [ ] Incorporate tabs_style_compact.css and tabs_style_full.css (and associated RTL) into scss
- [x] Don't require 2 build runs to build the rtl themes
- [ ] Add a lot of documentation on current component usage (starting with theme-only components)
- [ ] Migrate style dependencies in the php code to use the components from the `interface` directory
- [ ] Migrate component css still left in the `/themes` directory into scss
