# OpenEMR-interface

The OpenEMR-interface uses [Storybook](https://storybook.js.org) to document and standardize the creation of user interface elements. The project is using bootstrap as base as is built with [SASS](https://sass-lang.com/) (compiled with [gulp](https://gulpjs.com/)).

Different `themes` share a common `core` and have their own overrides to customize the appearance of OpenEMR. You can view how these themes differ using the "Knobs" tool at the bottom of the storybook interface.

The live version of this guide can be found at [openemr-interface.surge.sh](http://openemr-interface.surge.sh).

## Getting Started

OpenEMR-interface requires [node.js](http://nodejs.org) and [npm](https://www.npmjs.com/).

**Setup local development environment:**

```
$ npm install
```

From here you can either:
* `npm run dev-docs` - runs Storybook (port 9001) and watch changes to local `.scss` files load automatically with [BrowserSync](http://www.browsersync.io/)
* `npm run dev` - just compiles the local `.scss` files and recompiles when they're changed.
* `npm run dev 8081` (EXPERIMENTAL) - loads your local OpenEMR instance using BrowserSync (port 3000) in front of 8081 (you can use any port in this command) 

**If you're using docker** or other locally-hosted development environment, it is recommended that you automatically copy files to a mounted volume instead of mounting your working directory. See ["Option 2" in this doc](/contrib/util/docker/README.md) for more info.

### Development Environment Features

- Live preview sever
- CSS Autoprefixing
- Sass compilation (not yet using in our current themes)
- Browserify bundling
- Image optimization

## Build

**Build before you commit:**

```
$ npm run build
```

## TODOs
- [ ] Add a lot of documentation on current component usage (starting with theme-only components)
- [ ] Migrate style dependencies in the php code to use the components from the `interface` directory
- [ ] Migrate component css still left in the `/themes` directory into scss
