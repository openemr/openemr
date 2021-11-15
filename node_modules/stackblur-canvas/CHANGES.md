# StackBlur CHANGES

## 2.5.0

- Enhancement: boolean arg to skip setting canvas styles (@LukeeeeBennett)
- Build: As per latest devDeps.
- Docs: Update API docs as per latest

**Dev-facing:**

- Linting: As per ash-nazg
- npm: Switch to server without reported vulnerabilities
- npm: Switch to pnpm lock
- npm: Update devDeps (and switch to new ash-nazg peerDeps)

## 2.4.0

- Enhancement (image): add useOffsetWidth option for scaled images
- Refactoring: Move `let` to closer/deeper scope
- Linting: As per latest ash-nazg (add or avoid need for disable comments)
- npm: Update devDeps.

## 2.3.0

- Build: Update build files per latest devDeps
- Linting (ESLint): Switch to ash-nazg/sauron-node
- Linting (ESLint): Lint MD, HTML, hidden files
- Linting (ESLint): Switch to 2 sp. indent, fix max-len
- Linting (ESLint): Prefer `document.querySelector` in demo
- Linting (ESLint): Add a recommended extension (js) to eslintrc
- Maintenance: Add `.editorconfig`
- Mainenance: Use `.json` extension on `.babelrc`
- npm: Remove `package-lock.json` in favor of `yarn.lock`
- npm: Avoid bundling `rollup.config.js`
- npm: Move from deprecated `opn-cli` to `open-cli`
- npm: Add valid `engines` (pointing to minimum Node version until may confirm)
- npm: Update devDeps (except jsdoc which seems to still have a problem
    with ESM exports: <https://github.com/jsdoc/jsdoc/issues/1644>)

## 2.2.0

- Build fix: Target 100% coverage
- Refactoring (minor): `x + x` -> `2 * x`
- npm Add prepublishOnly script for yarn
- npm: Update devDeps (no impact on build)

## 2.1.0

- Enhancement: Throw descriptive `TypeError` rather than silently returning
    upon `processCanvasRGB`/`processCanvasRGBA` methods not supplying proper
    canvas (dependent methods will throw anyways, so shouldn't be a
    breaking change)
- Enhancement: Update TypeScript definition (@Jose Peleteiro)

## 2.0.0

- Breaking change: Remove now deprecated Bower
- Fix: Duck type with image or canvas in place of `instanceof` check
    (and a broken one)
- Enhancement: Add JSDoc comments
- Linting (ESLint): Add ESLint with "standard" base
- Linting (Markdown): Add `.remarkrc`
- Linting (package.json): Add recommended properties
- Linting (HTML): Add empty favicon to suppress console
- License: Change MIT license file name to reflect license type (MIT)
- Docs: Move changelog to own file: `CHANGES.md`
- Demo: Move demo to own directory (with static server to avoid Chrome
    security problems reaching out of folder)
- Demo: Move JS and CSS to separate files for easier linting/examination
- Build: Move from Grunt to Rollup, supporting ES6 Modules distribution
    as well as UMD
- Build: Add npm-recommended `package-lock.json`
- npm: Add start, eslint, rollup, open-docs, docs scripts
- npm: Add `module` for ES6 module discovery and switch `main` to point
    to `dist`

## 1.4.1

- Moves `grunt-cli` to `devDependencies` (#23)

## 1.4.0

- Allows the lib to be used with node-canvas

## 1.3.0

- TypeScript typings added

## 1.2.1

- Includes built files in the NPM packgage

## 1.2.0

- Remove alerts and obsolete `netscape.security.PrivilegeManager`

## 1.1.0

- Allow blur to be applied to `ImageData` directly (thanks @WebSeed)

## 1.0.0

- First Release
