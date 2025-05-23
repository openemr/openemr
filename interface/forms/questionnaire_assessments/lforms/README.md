## What is LForms?

[LForms](http://lhncbc.github.io/lforms/), a.k.a. LHC-Forms, is a feature-rich,
open-source Web Component that creates input forms, based on definition files, 
for Web-based applications. In addition to its native form-definition format, 
it partially supports the HL7 FHIR Questionnaire standard (SDC profile), and work
is in progress to expand that support.

It is being developed by the Lister Hill National Center for Biomedical
Communications ([LHNCBC](https://lhncbc.nlm.nih.gov)), National Library of
Medicine ([NLM](https://www.nlm.nih.gov)), part of the National Institutes of
Health ([NIH](https://www.nih.gov)), with the collaboration and support from 
the [Regenstrief Institute](https://www.regenstrief.org/), Inc. and the
[LOINC](https://loinc.org/) Committee.

For features and demos, please visit the [project
page](http://lhncbc.github.io/lforms/).

## Licensing and Copyright Notice

See [LICENSE.md](LICENSE.md).

## Customizing and Contributing

If you wish to revise this package, the following steps will allow you to make
changes and test them:

- Install Node.js (version 14 is what we are currently using, but it should work 
  with later versions)
- Clone the lforms repository and cd to its directory
- `source bashrc.lforms` (make sure node dir is available at ~/)
- `npm ci`
- `source bashrc.lforms` # to add node_modules/.bin to your path
- `npm run build` # build both FHIR libs and LHC-Forms web component
- `npm run start` # starts the app we use for testing
- `npm run test` # runs the unit tests and e2e tests

If you are planning to contribute new functionality back to us, please
coordinate with us, so that the new code is in the right places, and so that
you don't accidentally add something that we are also working on.

## Development server

- Run `npm run start` for a dev server. Navigate to `http://localhost:4200/`.
  The app will automatically reload if you change any of the source files.

- Run `npm run start-public` if you need to access to the dev server from a 
  different machine. For example, to run Narrator from a Windows PC.

## Build

- Run `npm run build` to build the project and generate a production version of
  the js files, which are much smaller than the development version. It
  generates an ES2017 version of the js files under dist/lforms. For details on
  the files to load, see ["Usng the LHC-Forms Web Component"](#using).  
  The `dist` directory is deleted and recreated during the process.

  The build also concatenates all the js files (except for zone.min.js and the
  FHIR support files) into a single `lhc-forms.js` file, and it works,
  but we don't currently recommend their use because the
  source maps don't work with these files. Also, there is a dist/webcomponent
  directory that is created with a copy of the files in dist/lforms, but that
  is only needed for the tests.

## Running tests

1. Run `npm run test` to run unit tests and e2e tests, which also copies the 
   FHIR lib files and built files in places for testing.

## Running unit tests

1. Run `npm run test:unit` to execute the unit tests via 
   [Karma](https://karma-runner.github.io).

## Running end-to-end tests

1. Run `npm run test:e2e` to execute the end-to-end tests via 
   [Cypress](https://www.cypress.io/). The e2e tests are configured to use Chrome.

## <a id="using">Using the LHC-Forms Web Component</a>

There are several script files to load. If you are building this, these files 
will be under `dist/lforms`. If you are using the pre-built versions from
https://clinicaltables.nlm.nih.gov/lforms-versions, then the file paths below
are relative to those versioned directories.

1. `webcomponent/styles.css`

You need to include the following files in your project:

1. `webcomponent/styles.css`
2. `webcomponent/assets/lib/zone.min.js` (unless you already have zone.min.js 
   on the page)
3. `webcomponent/runtime.js`
4. `webcomponent/polyfills.js`
5. `webcomponent/main.js`
6. _One_ of the FHIR support library files, assuming you are planning to use
   FHIR Questionnaires:
   - `fhir/lformsFHIRAll.min.js`
   - `fhir/R5/lformsFHIR.min.js`
   - `fhir/R4B/lformsFHIR.min.js`
   - `fhir/R4/lformsFHIR.min.js`
   - `fhir/STU3/lformsFHIR.min.js`

There is an example of an app using these files at
https://lhcforms.nlm.nih.gov/lforms-fhir-app/.
For details about how to work with this library, and for information about an
announcements list, see the [documentation](https://lhncbc.github.io/lforms/).

## <a id="npm-package">lforms npm package</a>

A complete npm package that you can use with import/require statements in your
code is not ready yet. The current lforms npm package only contains built files
in `/dist/lforms` directory. You can use those files as described in above
section [Using the LHC-Forms Web Component](#using).

## <a id="docs">Related Documents</a>
- `form_definition.md` The internal data format of the LHC-Forms widget.
- `changed-features.md` The list of features that changes between the new 
version (v30.0.0) and previous versions.
- `r4-support.md` The FHIR R4 features supported by LHC-Forms widget. 
- `sdc-support.md` The FHIR SDC features supported by LHC-Forms widget.
