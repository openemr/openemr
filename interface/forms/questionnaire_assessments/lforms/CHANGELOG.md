# Change log

This project follows [Semantic Versioning](http://semver.org/).

## [33.1.2] 2022-12-23
### Fixed
- FHIR Expressions were being run on form load once per item with an
  autocompletion list.  It now will only run an extra time (on form load) once
  for each item of type quantity which has a unit list, which is still more
  often that should be needed, but this fix by itself should help speed up the
  loading of many forms.

## [33.1.1] 2022-12-13
### Fixed
- Fixed a bug that hidden items in a questionnaire are displayed when
  a questionnaire response is loaded/merged into the questionnaire.

## [33.1.0] 2022-11-30
### Added
- Added support for answerExpression on items whose types are string, integer,
  date and time.
### Fixed
- Fixed a configuration issue in webpack dev server that FHIR js files
  failed to load through a proxy in dev mode.

## [33.0.0] 2022-11-15
### Updated
- Updated angular from v12 to v14
### Changed
- Removed the support for IE 11.
- Only ES2017 files (no ES5 files) are in the build files.
  The concatnated js file name is now lhc-forms.js, not
  lhc-forms.es5.js or lhc-forms.es2015.js
- Made item.dataType in lforms internal format required (a breaking change).
- Made initial[x] values and the initialSelected on answerOption both work
  when the item.type is choice or open-choice.
### Added
- Added support for string, date, time and integer values in answerOption (R4)
  and option (STU3), including the support for related extensions (optionPrefix,
  ordinalValue, and itemControl) and the initial[x] values.
### Fixed
- Fixed a bug in a custom cypress command.
- Fixed a bug in importing a DiagnosticReport resource.

## [32.2.3] 2022-11-03
### Fixed
- When running e2e tests, webpack-dev-server now serves the files so that
  changes to source files are more quickly recompiled and updated for the test
  runner.

## [32.2.2] 2022-10-25
### Fixed
- Fixed a bug in the construction of the URL for a FHIR ValueSet $expand
  operation, though there was already a work-around in place, so the only result
  was a single failed network call logged to the console (which then can cause a
  problem for Cypress tests).
### Updated
- Cypress has been updated to version 10.

## [32.2.1] 2022-10-20
### Fixed
- Fixed a bug that enableWhen does not work on the values calculated from FHIRPath
  expressions.

## [32.2.0] 2022-10-18
### Added
- Added support the currenly published URI for the preferred terminology server
  extension, though that URI will be changing
  (https://jira.hl7.org/browse/FHIR-23977).

## [32.1.0] 2022-09-18
### Added
- Added support the entryFormat extension

## [32.0.3] 2022-09-12
### Changed
- Removed item._modifiedAnswers and use item.answers directly.
- Removed fast-deep-equal package that is causing failed e2e tests in cypress.
### Fixed
- Fixed a bug that a user typed value on open-choice item is not set as a string value.

## [32.0.2] 2022-09-07
### Fixed
- Moved the URI for the custom FHIR extension lforms generates to store
  the "externallyDefined" setting out of the HL7 namespace.
- Minor refactoring of the SDC import code, which should not impact users,
  except that for STU3 item.definition will no longer be lost if you convert to
  LForms format and and back to FHIR.  (The R4 import did not have that issue.)

## [32.0.1] 2022-08-31
### Fixed
- Updated fhirpath.js to 3.0.0 to get a fix for
  https://github.com/lhncbc/lforms/issues/102

## [32.0.0] 2022-08-30
### Changed
- Removed a feature in which the autocompletion list fields would default to
  the answer list value if there were just one answer in the list.
- Changed the implementation on how the data changes in questionnaire data are handled.
- Added the active row indicator for the radio buttons and checkboxes.

## [31.0.1] 2022-08-26
### Fixed
- A very minor fix to the formatting of the form title, probably not visible to
  a user, but which was causing a test to fail in another package.

## [31.0.0] 2022-08-04
### Changed
- FHIR Quantities with a comparator can no longer be stored or loaded into a
  Questionnaire.  This change is in line with
  https://jira.hl7.org/browse/FHIR-37468.
### Fixed
- Fixes for handling Quantities in expressions.

## [30.4.2] 2022-07-07
### Changed
- Added a button on the test page (test/pages/lforms_testpage.html) to batch convert lforms
  to FHIR questionnaires or vice versa, where both input and output are zip files

## [30.4.1] 2022-06-24
### Fixed
- Fixed a bug that FHIRPath expressions are not calculated on newly added
  repeating items.
- Fixed a bug that enableWhen is not working on prepopulated answers.

## [30.4.0] 2022-06-13
### Added
- The beginnings of support to show error messages for individual fields.

## [30.3.0] 2022-05-19
### Added
- Added support for answerOption.initialSelected in R4 Questionnaire

## [30.2.0] 2022-05-18
### Changed
- Updated angular to v12 and ng-zorro-antd to v12.

## [30.1.7] 2022-05-17
### Fixed
- Fixed a bug that the radio button of "Other" of the CWE typed items lost
  its checked state when users typed in the input field.

## [30.1.6] 2022-05-02
### Fixed
- Off-list string values from an open-choice field are now usable in an
  expression for another open-choice field.

## [30.1.5] 2022-04-22
### Fixed
- Support for multiple values coming from intialExpression or calculateExpresion
  on fields that are of type string or coding.

## [30.1.4] 2022-04-20
### Fixed
- Fixed a bug that the initial value of boolean type was not imported correctly
  when it was false.

## [30.1.3] 2022-04-04
### Changed
- Adjusted the styles of readonly items

## [30.1.2] 2022-03-30
### Fixed
- Fixed a bug that observations were extracted from items that were either hidden or
  had no values.
- Fixed a bug that boolean value false was not extracted correctly.

## [30.1.1] 2022-03-30
### Fixed
- Fixed a performance issue in the initial loading of a Questionnaire when it
  contains a lot of choice and/or open-choice items.
- Fixed a bug that the values of date or dateTime items in a repeating group
  are not checked when adding a new repeating group.
- Fixed a style issue for the first item in a group.

## [30.1.0] 2022-03-25
### Added
- Added options to hide tree line styles, indentation or repetition numbers.

## [30.0.1] 2020-03-22
### Fixed
- Fixed answerExpression and calculatedExpression processing so that for list
  fields, both answers and answer lists can be set by an expression that depends
  on the answers of another field.

## [30.0.0] 2020-03-08
### Fixed
- Fixed a minor style issue
### Changed
- Code refactoring and clean up.
- Made most commands in package.json work on Windows.

## [30.0.0-beta.9] 2022-02-24
### Fixed
- Fixed a bug that the user saved data was overwritten by the values from initialExpression.
### Changed
- Added back the support of a CSS selector as the element parameter for LForms.Util functions
  such as getFormData, getUserData and getFormFHIRData.
- Removed the redundant resourceType parameter in the LForms.Util.mergeFHIRDataIntoLForms function.

## [30.0.0-beta.8] 2022-02-17
### Fixed
- Fixed a bug that saved user data in a form are lost when answer lists are updated
  by FHIRPath expression.

## [30.0.0-beta.7] 2022-02-17
### Added
- Support for the newer launchContext "name" data type, valueCoding.  For
  backward compatibility, valueId is still supported.
  (https://build.fhir.org/ig/HL7/sdc/StructureDefinition-sdc-questionnaire-launchContext.html)

## [30.0.0-beta.6] 2022-02-15
### Added
- Support for arbitrary launchContext names and resource types.  A second
  parameter has been added to LForms.Util.setFHIRContext which is a map between
  launchContext variable names and FHIR resource instances (as objects).  This
  allows an application that is familiar with a partictular Questionnaire
  to pass in the needed resources.

## [30.0.0-beta.5] 2022-02-09
### Fixed
- Fixed a a bug that repeating rows in horizontal table (gtable) are not added
  at the end of the table.
### Added
- Added the support of the questionnaire-hidden extension on the horizontal tables
  (gtable) and the matrix (table).

## [30.0.0-beta.4] 2022-02-04
### Fixed
- calculatedExpression no longer forces fields to be read-only.

## [30.0.0-beta.3] 2022-01-25
### Added
- Added an onError event, that returns an error when FHIR resources
  fail to load, or when there is an exception during the initialization.
- Added cypress for e2e tests.

## [30.0.0-beta.2] 2022-01-10
### Changed
- Removed locale files from moment.js
- Code refactoring on the initial release.

## [30.0.0-beta.1] 2021-12-15
### Fixed
- Fixed a style issue on items with a single unit.

## [30.0.0-beta.0] 2021-12-10
### Changed
- Included the initial release of the Web Component version of the LHC-Forms
  widget in the "next" directory. This will be reorganized out of the "next"
  directory prior to the non-beta release.

## [29.3.1] 2021-10-20
### Fixed
- If a form contained an attachment type question, hitting enter in another
  field opened up the attachment options.

## [29.3.0] 2021-09-01
### Added
- Support for the FHIR enableWhenExpression extension.

## [29.2.3] 2021-08-26
### Fixed
- Fixed a typo in the error message introduced in 29.2.2.

## [29.2.2] 2021-08-24
### Changed
- The LForms.Util.addFormToPage function will now display an error message in
  the cases where it does not render the form.  The previous behavior was that
  the form area was simply blank, so this is judged a "fix" over that.

## [29.2.1] 2021-08-23
### Changed
- Updated the URI for the FHIR "initialExpression" extension, but the old URI is
  still supported and will be updated if found in a Questionnaire.  As a
  side-effect, this means that a re-export back to FHIR will have the updated
  initialExpression URI.

## [29.2.0] 2021-07-26
### Added
- Support for attachments, both in FHIR (type="attachment") and in our internal
  format (dataType="attachment").

## [29.1.3] 2021-07-26
### Fixed
- FHIR answerExpressions that return arrays of strings now work for setting a
  list (though the type needs to be "choice", not "string", which is something
  that will be fixed later).

## [29.1.2] 2021-07-22
### Fixed
- Updated fhirpath.js for improved handling of choice types.

## [29.1.1] 2021-06-24
### Changed
- Removed direct refereces to angular in FHIR lib files.

## [29.1.0] 2021-05-27
### Added
- Support for named FHIR expressions.  Any supported FHIR expression can now
  have a name, which acts like a variable for other expressions.

## [29.0.3] 2021-05-03
### Fixed
- Fixed a bug where hidden items were triggering validation to fail.

## [29.0.2] 2021-04-30
### Fixed
- Fixed a bug that item.repeats is not converted correctly in some cases.

## [29.0.1] 2021-04-29
### Fixed
- Fixed a bug that minValue extention is not imported correctly when the value is 0

## [29.0.0] 2020-12-23
### Changes
- Breaking change to FHIR support. The launchContext URI has changed to
  http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-launchContext

## [28.1.6] 2021-03-24
### Fixed
- Updated jQuery to 3.6.0.

## [28.1.5] 2021-03-11
### Fixed
- FHIR calculatedExpressions on editable fields now stop calculating if the user
  edits them.

## [28.1.4] 2021-03-03
### Fixed
- A FHIR Observation-based prepoluation issue: the Observations were not
  being filtered by status.
- A problem in FHIR expression processing which caused issues if more than one
  form was added to the page.
- Sorting was corrected for FHIR Observation-based prepoluation.  It now takes
  the latest by "date", not "_lastUpdated".

## [28.1.3] 2021-02-18
### Fixed
- Processing of FHIR expressions for fields with multi-select lists, and
  for fields with radio buttons.

## [28.1.2] 2021-01-21
### Fixed
- Replaced local js-class.js with npm js-class package.
- Changed requiring a json package using .json extension.

## [28.1.1] 2021-01-13
### Fixed
- Added missing polyfills for IE 11.
- Fixed error handling logic for FHIR expressions.

## [28.1.0] 2021-01-11
### Added
- Support for x-fhir-query variables, and with embedded FHIRPath in the URLs
  as described at
  http://build.fhir.org/ig/HL7/sdc/expressions.html#x-fhir-query-enhancements.
  The variable extension is documented at
  http://hl7.org/fhir/R4/extension-variable.html

## [28.0.0] 2020-12-10
### Changed
- Breaking change to FHIR support. Changed the extract behavior to use the Observation Extract Extension
  http://build.fhir.org/ig/HL7/sdc/StructureDefinition-sdc-questionnaire-observationExtract.html.

## [27.1.1] 2020-12-17
### Fixed
- Fixed a bug that nummeric value of 0 was not exported in QuestionnaireResponse

## [27.1.0] 2020-12-09
### Added
- Support for the FHIR questionnaire choice orientation extension.

## [27.0.0] 2020-12-08
### Changes
- Breaking change to FHIR support.  LForms.Util.setFHIRContext now takes an
  instance of 'client-js', a.k.a. npm package fhirclient,
  version 2.  (See http://docs.smarthealthit.org/client-js).

## [26.4.0] 2020-12-01
### Added
- Multiple code support for observationLinkPeriod prepopulation.

## [26.3.1] 2020-10-19
### Fixed
- Fixed a bug in STU3 Questionnaire import that 'options' is not handled correctly

## [26.3.0] 2020-10-16
### Added
- Added two functions: LForms.Util.isValid, which checks if the form fields are
  valid and LForms.Util.getErrors to get a list of errors preventing the form
  from being valid.

## [26.2.0] 2020-10-06
### Added
- Added a function, LForms.Util.getAnswersResourceStatus, which checks the status
  of the loading of answer lists. (This only applies to forms defined as a
  FHIR Questionnaire.)

## [26.1.2] 2020-10-05
### Added
- Support for using a FHIR Questionnaire as a form definition in addFormToPage.

## [26.1.1] 2020-09-30
### Fixed
- Fixed the extensions URI for answer scores

## [26.1.0] 2020-09-23
### Added
- Support for the FHIR answerExpression extension (with FHIRPath expressions
  only).

## [26.0.0] 2020-09-16
### Changed
- Changed the default template options to hide the form controls and header.

## [25.1.6] 2020-09-15
### Fixed
- Remove duplicate entry in form level fields.
- Display a marker `*` on labels for required items.
- Add `aria-required` on required inputs.

## [25.1.5] 2020-09-04
### Fixed
- a bug in FHIR Observation-based extraction that kept it from working for
  nested items.

## [25.1.4] 2020-08-27
### Fixed
- a bug in detection of STU3 Questionnaires

## [25.1.3] 2020-07-31
### Changed
- Treat skip logic condition source as not existing when it is disabled.

## [25.1.2] 2020-07-17
### Changed
- Fix a bug with skip logic notEqual operator.

## [25.1.1] 2020-06-24
### Changed
- Update angularjs to 1.8.0

## [25.1.0] 2020-06-22
### Added
- Added a support for providing ValueSets via a FHIR resource package at the initialization.

## [25.0.0] 2020-06-17
### Changed
- Updated the URI for the FHIR calculatedExpression extension.  (If you are not
  using FHIR, this change will not affect you.)  The lforms-updater package can
  be used to update FHIR Questionnaires generated for previous versions of
  LForms.

## [24.1.4] 2020-05-08
### Fixed
- Fixed a bug that answers for questions under a question that has no answers were not exported.

## [24.1.3] 2020-05-06
### Fixed
- Replaced "Number.parseFloat" with "parseFloat" for IE11

## [24.1.2] 2020-05-04
### Fixed
- Fixed contained valueset reference for R4 (from using url to using #vs-id),
  but continue to support url-based reference only for the purpose of
  backward-compatibility with previous LForms versions.

## [24.1.1] 2020-04-28
### Changed
- Updated the fhirpath dependency to 2.1.3
- Updated the ucum-lhc dependency to 4.1.3
### Fixed
- Textarea placeholder binding causes exception in IE11

## [24.1.0] 2020-04-22
### Changed
- Separated the skip logic 'disabled' status from item's 'hidden' status.
### Fixed
- Correctly handled the import/export of the questionnaire-hidden extension.

## [24.0.2] 2020-04-21
### Fixed
- Fixed a bug that reloading existing LForms form definition object with instances of repeating items
  might be very slow.

## [24.0.1] 2020-04-21
### Fixed
- Since 19.0.0, lforms only supports units for types INT, REAL, and QTY.
  This fixes a place in which units should have been ignored but were not and
  caused exceptions to be raised.

## [24.0.0] 2020-03-31
### Changed
- 'linkId' is now required on each item. 'questionCode' is no longer required.
  The original 'questionCode', 'questionCodeSystem' are kept but might be refactored
  into 'codeList' in the future versions.
- the name 'sourceItemCode' in dataControl.source becomes 'sourceLinkId' and its value
  becomes the source item's linkId value
- the value of 'source' in skipLogic.conditions becomes the source item's linkId value.
- Remove _idPath from item object.
- 'linkId' is used in 'skipLogic', 'dataControl' and 'calculationMethod' to identify
  source items across the form. The source items no longer have to be ancestors or
  siblings of the target item.

## [23.0.1] 2020-03-24
### Fixed.
- Fixed missing extensions when outputting lforms format.

## [23.0.0] 2020-03-20
### Changed
- The version tag on generated FHIR resources is now set on the "code" property
  instead of the "display" property, as some FHIR servers drop tags that do not
  contain a code.
- Removed a FHIR extension for indicating that a question can have more than one answer.
  We now rely on FHIR Questionnaire.item.repeats, plus the item type, to determine
  whether an item repeats or the item's answer repeats in our rendering of the form.

## [22.0.0] 2020-03-12
### Changed
- The URI for the FHIR "observationLinkPeriod" extension has been updated to the
  current URI,
  http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-observationLinkPeriod.
  FHIR Questionnaires coded with the old URI will need to be updated to continue to
  support Observation-based prepoluation or extraction.  We are releasing a
  separate package, lforms-updater which can be used to automate updating LForms
  form definitions and FHIR Questionnaires to the current version.

## [21.2.1] 2020-03-05
### Changed
- Changed answer's 'codeSystem' to be 'system'. Legacy form data with 'codeSystem'
  are converted into 'system' when a form a loaded.

## [21.2.0] 2020-03-03
### Added
- LForms form definitions now contain an lformsVersion attribute indicating the
  LForms version from which they were generated.
- Generated/exported FHIR resources now contain a tag (meta.tag) that starts
  with 'lformsVersion: ' followed by the LForms version used to export them.

## [21.1.0] 2020-02-27
### Added
- Added support for a custom extension in FHIR Questionnaire to contain the dataControl
  data in LHC-Forms.

## [21.0.1] 2020-02-26
### Fixed
- Fixed a bug that valueBoolean in Questionnaire was not converted to LForms BL value.

## [21.0.0] 2020-02-26
### Changed
- Bumped up angularjs version to 1.7.x. This could potentially break applications still
  running prior angularjs versions, hence bumped up the major version.

### Fixed
- Fixed a bug in click event in CNE/CWE control.
  Changed ng-click event to ng-change event on radio/checkbox inputs when ngModel is used.
- Fix date time parsing test.

## [20.2.1] 2020-02-18
### Added
- Added support for 'notEqual' in skip logic, to support '!=' in the 'enableWhen' of
  FHIR Questionnaire.

## [20.2.0] 2019-02-13
### Changed
- Updated the fhirpath dependency to 1.0.1 (which was a breaking fhirpath
  change, but should not affect LForms).  This adds some FHIR model information to
  the evalution of FHIRPath expressions, paritcularly knowledge of choice-types,
  so that expressions like "item.answer.value" can work.
### Added
- Implemented calculated value support for additional FHIR types, particularly
  Coding, so that list fields can be set via a FHIRPath expression.

## [20.1.3] 2019-12-31
### Fixed
- Export calculatedExpression, variable and other FHIR extensions in lforms format.

## [20.1.2] 2019-12-26
### Fixed
- Exporting to FHIR no longer include the minOccurs extension if the value is 1
  and "required" is true.

## [20.1.1] 2019-12-18
### Fixed
- defaultAnswer was not handled correctly for DT and DTM.

## [20.1.0] 2019-12-17
### Added
- Added support for item.answer.item when importing from/exporting to FHIR.

## [20.0.1] 2019-11-07
### Changed
- Added a vertical scrollbar for long text of coding instructions and copyright notice
  in popover windows.

## [20.0.0] 2019-11-06
### Added
- Added "exists" support to skip logic
### Changes
- The trigger.code and triger.value.code were "hidden" features, but the issue of code system
  wasn't taken into consideration and therefore was broken.
  This has been reimplemented so that when the source question is of type CNE or CWE,
  the trigger.value will be a hash with any or all of these three fields:
  code, system, and text, and the trigger matching will be based on these fields. Please
  see the updated lforms definition for more details.

## [19.0.2] 2019-11-04
### Fixed
- Fixed a bug that autocompleter options are not set for items in templateOptions.

## [19.0.1] 2019-10-31
### Changed
- Default values are no longer set when loading QuestionnaireResponse, DiagnosticReport
  and saved form data.

## [19.0.0] 2019-10-28
### Added
- Data type QTY is now documented as a supported data type, and is now validated like a REAL.
  (Previously, there was no validation for type QTY).
### Changed
- Removed the "type" field from the form definition (though it should not hurt to
  include it).
- The codeSystem field is no longer defaulted to LOINC.  The LOINC
  default was causing problems for FHIR Questionnaires which did not have a code
  system specified.
- Data types INT and REAL are now only allowed to have one unit defined in
  field "units", for alignment with FHIR.  For more than one unit, use type QTY
  (quantity).  For backward compatibility with existing form definitions, INT or
  REAL fields with more than one unit will be changed to QTY, and a warning will
  be output.
- The data type is no longer changed to "ST" if a question has a calculationMethod.

## [18.3.0] 2019-10-22
### Added
- The FHIRPath expressions are now cached after being parsed, which greatly
  speeds up the processing.

## [18.2.2] 2019-10-17
### Fixed
- Corrected FHIR export and import of integer, decimal, and quantity types, and
  the unit extensions used.

## [18.2.1] 2019-10-15
### Fixed
- Corrected the default for handling multiple skip logic conditions to be "ANY"
  instead of "ALL" (i.e. now "ANY" of the conditions being true will make the
  field visible).  The "ANY" default was what should have been the case per the
  documentation in form_definition.md, and also matches the equivalent default
  in FHIR.
- Fixed the setting of the FHIR context for evaluating FHIR variables defined on
  Questionnaire.

## [18.2.0] 2019-10-15
### Changed
- Units column is no longer shown if there is no units values in the form data.

## [18.1.1] 2019-10-08
### Fixed
- Display of item with TITLE data type is fixed without setting header flag.

## [18.1.0] 2019-10-03
### Added
- Support for the FHIR "rendering-style" (CSS) extension on Questionnaire.title,
  item.text, and item.prefix.  (Support on answer list text is still TBD).

## [18.0.5] 2019-09-30
### Fixed
- Updated dependencies to get patches for a vulnerability.

## [18.0.4] 2019-09-26
### Fixed
- Fixed a bug that item values that have extra user data are not found in the answer list so that
  they are not displayed.

## [18.0.3] 2019-09-26
### Fixed
- Increased the maximum supported nesting level of form sections.

## [18.0.2] 2019-09-25
### Changed
- Changed form title to use title field, if present, otherwise use name field.
### Fixed
- Fixed missing form level fields related to FHIR Questionnaire. All form level fields are preserved.

## [18.0.1] 2019-09-24
### Changed
- The sequence numbers in front of answers won't be displayed if one of the
  answers has a numeric value, to avoid confusion.

## [18.0.0] 2019-09-12
### Added
- Added support for 'prefix' in FHIR Questionnaire and in LForms.
### Fixed
- Fixed a bug that on CWE items, user typed answers that are not in the answer list are
  not displayed in the "OTHER" fields, when answer list is displayed as radio buttons or
  checkboxes.
- Fixed a bug that code system of an answer is not imported for QuestionnaireResponse and
  DiagnosticReport.
### Changed
- User typed, not-on-list value for CWE item is now stored as a string.

## [17.4.1] 2019-09-06
### Fixed
- Fixed a bug in conversion of restrictions to corresponding FHIR extensions.

## [17.4.0] 2019-08-28
### Added
- Support for prefetched lists (FHIR item control type = 'dropdown') whose
  source comes from external FHIR ValueSets.

## [17.3.3] 2019-08-09
### Fixed
- Fixed a bug which was overwriting linkid with codepath in Questionnaire's item.enableWhen.question.

## [17.3.2] 2019-07-30
### Fixed
- Fixed a bug that multiple repeating items are added when the "add" button is clicked,
  if the form with user data that contains multiple repeating items is loaded.

## [17.3.1] 2019-07-26
### Fixed
- Fixed bug in LForms.Util.dateToDTMString()

## [17.3.0] 2019-07-24
### Added
- Support for FHIR Questionnaire items of itemControl type 'autocomplete'.
- Support for the terminology-server extension that specifies the FHIR server
  from which ValueSet expansions should be done.  (Currently, this assumes the
  terminology server does not require a login.)
### Changed
- Modified a test Questionnaire, test/data/R4/ussg-fhp.json, to contain examples
  of this new support.
### Fixed
- An issue that prevented observationLinkPeriod from working on fields that were
  not top-level.

## [17.2.4] 2019-07-19
### Fixed
- Fixed a bug in parsing ISO date and time string.

## [17.2.3] 2019-07-09
### Fixed
- Fixed a bug that help instructions are not exported correctly in R4 Questionnaire.

## [17.2.2] 2019-07-05
### Changed
- Removed unnecessary default code system value

## [17.2.1] 2019-07-03
### Changed
- Removed generated identifiers from the exported FHIR QuestionnaireResponse resources.
  The generated identifiers as they are now are not unique and do not serve specific purposes.
  The identifiers from previously imported QuestionnaireResponse resources will be preserved.
  This is not considered a breaking change because FHIR spec governs the protocol here and
  the identifier field is optional.

## [17.2.0] 2019-07-03
### Added
- Added support for multiple code system in answers.

## [17.1.2] 2019-06-28
### Fixed
- Fixed missing form level properties in conversion of FHIR questionnaire to lforms.
- Fixed missing item code list in conversion of FHIR questionnaire to lforms.

## [17.1.1] 2019-06-26
### Fixed
- Fixed a bug in FHIR/SDC Questionnaire version detection.

## [17.1.0] 2019-06-20
### Added
- DTM support and datetime picker

## [17.0.0] 2019-06-21
### Changed (for FHIR forms only)
- This contains a breaking change that only affects forms imported from FHIR
  Questionnaires.  The loading of external FHIR resources no longer happens when
  an LFormsData object is constructed.  Instead, after constructing LFormsData,
  one must call (on the instance) loadFHIRResources(), which will return a
  promise that resolves when the resources are loaded.  Similarly, if using
  LForms.Util.addFormToPage(), that will also return a promise that resolves
  when external FHIR resources had been loaded.  (See the
  [documentation](https://lhncbc.github.io/lforms/index.html#fhirQueries).)

## [16.0.1] 2019-06-18
### Fixed
- Fixed extension URLs for SDC Questionnaire

## [16.0.0] 2019-06-17
### Fixed
- Each value of the multiple selected values in CNE/CWE typed item
  should be a separate Observation resource when LForms data is converted
  to corresponding FHIR resources.

## [15.7.2] 2019-06-14
### Fixed
- Fixed a bug that user selected units in saved data were not loaded correctly into the LForms.

## [15.7.1] 2019-06-06
### Fixed
- Observation-based prepoluation of list fields now works.

## [15.7.0] 2019-06-03
### Fixed
- Used a correct extension for coding instructions in FHIR Questionnaire
### Added
- Added support for coding instructions format in FHIR Questionnaire

## [15.6.2] 2019-05-31
### Fixed
- Fix an error when creating LFormsData object with empty items.

## [15.6.1] 2019-05-28
### Fixed
- Empty Observation records were being saved during data extraction to an STU3
  FHIR server (for fields without values).
- Corrected the setting of derivedFrom in extracted obsevations in R4.

## [15.6.0] 2019-05-23
### Added
- The %questionnaire variable is now available for FHIRPath expressions.

## [15.5.1] 2019-05-21
### Fixed
- Fixed URL in R4 for ordinalValue (score)

## [15.5.0] 2019-04-11
### Added
- Support for data extraction via observationLinkPeriod.  If you pass in an
  "extract" option (set to true) to LForms.Util.getFormFHIRData when
  "resourceType" is set to QuestionnaireResponse, it will return an array of the
  QuestionnaireResponse resource and any extracted Observation resources.
### Fixed
- Added a check to make sure linked observations do not have focus set to true.

## [15.4.0] 2019-05-09
### Added
- Added support for FHIR Questionnaire form level fields.
- Patched date parsing errors encountered in form builder environment.

## [15.3.2] 2019-05-15
### Fixed
- Changed to allow 'label' in answers to be null.

## [15.3.1] 2019-04-30
### Fixed
- Corrected the FHIR itemControl codes.  We still accept the incorrect codes (as
  well as the correct ones) for import, but the export is now correct.

## [15.3.0] 2019-04-29
### Changed
- Changed view's screen size breakpoints.
- Changed import function to work around 'linkId' in FHIR QuestionnaireResponse
  resource.
- Added validation for signed values of INT and REAL data types.
### Fixed
- Fixed bug in processing regexp value of 'pattern' restriction.

## [15.2.5] 2019-04-25
### Fixed
- Problems with mapping from the LForms structure to QuestionnaireResponse when
  running FHIRPath expressions.  Added some tests.

## [15.2.4] 2019-04-19
### Fixed
- Enabled generation of source maps for the FHIR supported files.

## [15.2.3] 2019-04-15
### Fixed
- Improved error handling and reporting for LForms.util.getFHIRServerReleaseID.

## [15.2.2] 2019-04-05
### Fixed
- Corrected a problem with detecting which version of FHIR was being used by a
  FHIR server.

## [15.2.1] 2019-03-27
### Removed
- Excluded unnecessary files from the npm package.

## [15.2.0] 2019-03-26
### Added
- The build process has been changed to use npm packages, in preparation for
  registering this package on npm.  (At this point, this package should be ready
  for being registered, which we will do soon.)  It is not expected that this is
  a breaking change.

## [15.1.0] 2019-03-22
### Added
- Support for the questionnaire-observationLinkPeriod extenstion for
  pre-population.  (Extraction is not yet supported.)

## [15.0.1] 2019-03-20
### Fixed
- This is a fix for calculated expressions in the Safari browser.  (Chrome, Edge,
  and Firefox were working).

## [15.0.0] 2019-03-20
### Changed
- Updated the jquery-ui package to 1.12.1 from 1.11.4, which is a breaking
  change for that package.  Since jQuery is a global variable, this is
  potentially a breaking change for users of the lforms package.  The lforms
  APIs themselves are unchanged.

## [14.2.0] 2019-02-13
### Added
- Added code and system attributes to unit, to align with FHIR specs.

## [14.1.4] 2019-02-07
### Changed
- Fix typo in url for FHIR unit option.

## [14.1.3] 2019-01-29
### Changed
- Fixed unit conversions to store the unit lists in unitOption extensions and
  change the data type to quantity. Used initial.valueQuantity.unit to store the
  lforms default value. If no default is found, the first unit in the list is set to
  default.
- Refactored the code to reduce some duplication in STU3 and R4.

## [14.1.2] 2019-01-25
### Fixed
- The BMI "calculationMethod" can now handle "[in_i]" as a unit.

## [14.1.1] 2019-01-23
### Changed
- Standard Questionnaire exports for R4 now contain '4.0' (instead of '3.5') as
  the FHIR version.
- Standard QuestionnaireResponse exports now include meta.profile.
- Exceptions raised for problems with detecting the version of a FHIR resource
  are now Error objects instead of strings.

## [14.1.0] 2019-01-22
### Added
- Added an option to specify the subject for an exported FHIR resource.

## [14.0.1] 2019-01-15
### Fixed
- Removed some outdated documentation from form_definitions.md.
- Reorganized some code (but not in a way the affects public APIs).

## [14.0.0] 2018-12-22
### Changed.
- The "themes" css file is now in a separate file from the lforms.min.css file
  in the distribution, to avoid making that larger than necessary.  It has also
  been removed from bower.json, so that themes.css will no longer be
  automatically pulled in with things like wiredep.  (This is possibly a breaking
  change for someone, and is the reason for the major-version increase.)
- The autocomplete-lhc package is now being pull in via npm.

## [13.10.2] 2018-01-10
### Fixed
- Now handles FHIR QuestionnaireResponse items that do not have answers.

## [13.10.1] 2018-01-09
### Fixed
- Corrected version number in bower.json.

## [13.10.0] 2018-01-08
### Added
- Support for the FHIR extension "variable" has been added, but as with the
  "questionnaire-initialExpression" and "questionnaire-calculatedExpression"
  support is currently limited to FHIRPath-based expresions.

## [13.9.1] 2019-01-02
### Fixed.
- Fixed errors in parsing argonaut questionnaire extensions with support for item.option.extension-score.

## [13.9.0] 2018-12-15
### Added
- FHIR Questionnaire contained ValueSet support.

## [13.8.0] 2018-11-30
### Added.
- Added support for the questionnaire-hidden extension
    http://hl7.org/fhir/StructureDefinition/questionnaire-hidden
  A good test example is:
    https://argonautproject.github.io/questionnaire/Questionnaire-questionnaire-example-asq3.html

## [13.7.2] 2018-12-12
### Fixed
- FHIR value quantity import/export
- Misc fixes.

## [13.7.1] 2018-12-07
### Fixed
- FHIRPath intialization has been corrected, so that the context passed in is
  now the item on which the expression is attached, and %resource is set to the
  QustionnaireResponse object.

## [13.7.0] 2018-12-06
### Added.
- Added support for enableWhen and initial(x) in R4.

## [13.6.0] 2018-12-04
### Added
- Support the FHIR extension "questionnaire-initialExpression".

## [13.5.0] 2018-11-26
### Added
- A new function LForms.Util.guessFHIRVersion(fhirResource) which looks at the
  structure of the resource to guess the version of FHIR being used.
- Made a previously internal function,
  LForms.Util.detectFHIRVersion(fhirResource), public.  This function looks at
  the meta.profile field to determine the FHIR version of the resource.
- Made a previously internal function,
  LForms.Util.validateFHIRVersion(version), public.  This function checks to see
  if the given version is supported by LForms and if the needed auxiliarly
  lformsFHIR.min.js file has been loaded.
### Fixed
- In the pre-built files placed on
  https://clinicaltables.nlm.nih.gov/lforms-versions/, the FHIR auxiliary files
  were missing from 13.4.0 and possibly earlier.

## [13.4.0] 2018-11-14
### Added
- Added support for the FHIR questionnaire-launchContext extension in R4.

## [13.3.0] 2018-11-08
### Added
- Added support for answerValueSet and answerOption in R4.
### Changed
- Separated test suites and fixtures for R4 and STU3 versions.
### Fixed.
- Removed hard coding of loinc code system in answerOption/option valueCoding
object.

## [13.2.0] 2018-10-24
### Added
- LForms.Util.addFormToPage can now accept a form defintion object as its first
  argument rather than just a variable name that holds that object.
### Fixed
- There were several problems with the FHIR APIs in LForms.Util.  Tests were
  testing lower-level methods rather than the public APIs, which is how that
  escaped notice.

## [13.1.0] 2018-10-16
### Added
- The start of support for FHIR R4, in the form of copy of STU3 (so no real R4
  support yet, except for questionnaire-calculatedExpression support which we
  added in 12.12.0).

## [13.0.1] 2018-10-16
### Fixed
- An issue with the "grunt test" task introduced in 13.0.0.  (The build was not
  run prior to the tests.)

## [13.0.0] 2018-10-15
### Changed
- Reorganized the FHIR functions to facilitate support for multiple versions of
  FHIR.  (This should not be a breaking change if you are not using FHIR).

## [12.13.0] 2018-10-02
### Fixed
- Fixed data type for items with units.
### Added
- Added support for TX data type and "text" item type in Questionnaire.

## [12.12.2] 2018-09-26
### Fixed
- Updated FHIRPath to 0.8.0.

## [12.12.1] 2018-09-21
### Fixed
- Fixed a bug that answers of repeating items in QuestionnaireResponse are
  not handled correctly.

## [12.12.0] - 2018-09-19
### Added
- Support for the FHIR Questionnaire resource extension
  questionnaire-calculatedExpression.  This requires the fhirpath.js library,
  which is now packaged with LForms, but as a separate file,
  app/scripts/fhirpath/fhirpath.min.js.

## [12.11.0] 2018-08-13
### Added
- Added a 'linkId' field in items.

## [12.10.4] 2018-08-14
### Fixed
- The BL (boolean) data type now works correctly, and is displayed as a
  checkbox.

## [12.10.3] 2018-08-07
### Fixed
- The background color for textarea based fields.

## [12.10.2] 2018-07-19
### Fixed
- The protractor tests stopped running in Firefox, and a fix is not yet
  available, so the tests were revised to run in Chrome.

## [12.10.1] 2018-05-16
### Changed
- Upgraded autocomplete-lhc package to 16.0.0.
### Fixed
- Fixed problem with validity of FHIR output by stripping nulls from the resource.

## [12.10.0] 2018-05-02
### Changed
- Treated data type TX same as ST.
- Used TEXTAREA for items with ST data type instead of INPUT.

## [12.9.3] 2018-03-22
### Fixed
- Accessibility issue with the announcing of validation errors by a screen
  reader.

## [12.9.2] 2018-03-16
### Fixed
- Accessibility issue with the announcing of date picker buttons by a screen
  reader.

## [12.9.1] 2018-03-12
### Fixed
- Several accessibility issues were fixed, including the reading of inline help
  and questions and answers for radio buttons and checkboxes.

## [12.9.0] 2018-02-22
### Changed
- Added an option to export LForms data in a standard FHIR QuestionnaireResponse resource
  without any extensions.
- Changed related functions definitions in LForms.Util

## [12.8.2] 2018-02-09
### Changed
- Modified the TOTALSCORE function to support multiple TOTALSCORE rules on one form.

## [12.8.1] 2018-01-30
### Changed
- Added an option to export LForms data in a standard FHIR Questionnaire resource without
  any extensions.

## [12.8.0] 2018-01-24
### Added
- Added an option to export LForms data in a FHIR Bundle with a DiagnosticReport resource and
  associated Observation resources.
- Added supporting functions for FHIR Bundle.
- Added new functions under LForms.Util for exporting/importing to/from FHIR resources.
- Added documentations for LForms.Util functions in form_definition.md.

## [12.7.1] 2018-01-02
### Fixed
- Added an FHIR Questionnaire extension for externallyDefined.

## [12.7.0] 2017-12-20
### Changed
- Changed the linkId of FHIR Questionnaire item to be the _codePath of LForms' item, and updated
  related conversion functions between FHIR data and LForms data.
- Added an FHIR extension to support repeating answers in FHIR Questionnaire.
- Added support for multiple answers for the initial values of an FHIR Questionnaire.

## [12.6.1] 2017-12-08
### Fixed
- Fixed z-index of icon in date input field to go behind any calendar widget coming on the top.

## [12.6.0] 2017-10-23
### Added
- Added conversion of externallyDefined field in LForms to store it in FHIR questionnaire resource.

## [12.5.3] 2017-10-19
### Changed
- Changed our internal development environment shell from tcsh to bash.

## [12.5.2] 2017-09-29
### Fixed
- Fixed an accessibility issue with plain-text popups.  Popups with HTML (e.g.
  HTML-encoded help) are still not completely read.

## [12.5.1] 2017-08-25
### Fixed
- Fixed a bug in FHIR QuestionnaireResponse convert function to handle cases where
  items with units could have data types as "ST"

## [12.5.0] 2017-08-25
### Added
- Added support to convert a FHIR Questionnaire resource to LForms format.

## [12.4.2] 2017-08-17
### Changed
- Moved themeList.js, which is just for demos, out of the test directory and
  into app/scripts, so that other applications (e.g. our demo site) can make use
  of it.

## [12.4.1] 2017-08-14
### Fixed
- Fixed the calendar next/previous buttons under themes for IE.

## [12.4.0] 2017-07-31
### Added
- Added cyan and teal color themes.
- Added color theme coffee.

## [12.3.1]  2017-07-27
### Fixed
- The autofill for list fields with only a single list item had been
  accidentally disabled by previous changes.

## [12.3.0]  2017-07-07
### Changed
- Added support back for validations of items in horizontal tables.

## [12.2.0]  2017-07-06
### Changed
- Added support back for displayControl.colCSS of items in formHeaderItems.

## [12.1.0]  2017-06-28
### Changed
- CSS styles in displayControl.css can now be applied to the item.

## [12.0.0]  2017-06-22
### Changed
- The HL7 v2 output has changed.  In particular, for questions with repeating
  answers, each answer is now in a separate OBX record.
- Added a themes capability, with two new themes.
- List fields whose data comes from an external source (via the URL in the
  "externallyDefined" property) have a revised format for the content of the
  AngularJS data model.  This will only affect you if you are accessing the data
  model directly.  The change is that any extra data properties for a selected
  item (other than the "text" and "code" properties) are now placed inside
  value.data, instead of directly on the item value object.

## [11.5.0]  2017-05-18
### Added
- Added support for disabling CNE/CWE items (autocomplete input fields or radio buttons/checkboxes)
- Added support for displaying coding instructions and copyright info on the section headers of
  horizontal tables and matrix tables.

## [11.4.3]  2017-05-24
### Fixed
- Addressed some accessibility issues.

## [11.4.2]  2017-05-18
### Fixed
- Added checking for questions in form header when generating HL7 and FHIR data.

## [11.4.1]  2017-05-10
### Changed
- Used autocomplete-lhc v13.0.0 and changed the way to handle modified
  display text for answers.

## [11.4.0]  2017-05-08
### Changed
- Added support for non-clickable headers in lists (when the list items are
  defined in the form).

## [11.3.0]  2017-05-02
### Changed
- Added supports for FHIR SDC Questionnaire and QuestionnaireResponse

## [11.2.1]  2017-04-27
### Fixed
- There was a problem with the handling of defaultAnswer for radio buttons and
  checkboxes.

## [11.2.0]  2017-04-24
### Added
- Support for default answers for questions via the defaultAnswer field in
  the form definition.

## [11.1.1]  2017-04-14
- Used a different library (element resize detector) for checking
  the container element's size, which can handle multiple rendered
  forms on a page.

## [11.1.0]  2017-03-27
### Added
- Added a viewMode option to the form and to the individual items to
  control the layout

## [11.0.5]  2017-03-20
### Fixed
- Fixed a bug in URL data type parsing.

## [11.0.4]  2017-03-06
### Changed
- Added a resize event listener on the container element to make the form
  responsive in cases where container's size changes without a change in
  window size.

## [11.0.3]  2017-03-03
### Changed
- Changed some background color on section headers and buttons
- Made the form responsive to the container's size, not the screen's size

## [11.0.2]  2017-03-02
### Fixed
- Default values are now handled for questions whose answers are presented as
  radio buttons.
- Also eliminated an unnecessary copy of answer data for the case when there is
  no label value for the answers.
- Corrected missing fonts in the pre-compiled version.
- Fixed display issues with the icon buttons.

## [11.0.1]  2017-02-24
### Changed
- Fixed the styles for forms where a question contains questions of sections

## [11.0.0]  2017-02-08
### Changed
- Redesigned the form template to make it responsive

## [10.0.0]   2016-12-06
### Changed
- Changed OBX4 value calculation method in HL7 messages.

## [9.0.2]   2016-12-05
### Changed
- Updated a test form, and added a test to make sure a problem with the
  autocompletion stays fixed.

## [9.0.1]   2016-11-22
### Changed
- Changed the default style of TITLE row to be same as section headers.

## [9.0.0]   2016-11-17
### Changed
- Updated the autocomplete-lhc package to version 10, in which URLs for search
  autcompleters now take a "maxList" parameter (in place of, but with opposite
  meaning to, the "autocomp" parameter).  Forms with URLs for search lists might
  need to be updated, or more likely the servers handling the URLs will need to
  be changed to handle the new parameter, which is the reason for the major
  version increment on this package.  The [Clinical Table Search
  Service](https://clin-table-search.lhc.nlm.nih.gov) supports the new
  autocomplete-lhc version with the addition of newer API URLs (v3 and higher).

## [8.1.2]   2016-11-08
### Fixed
- Fixed a bug in getting form data without questions that have empty values.

## [8.1.1]   2016-11-02
### Changed
- Changed a FHIR data processing function's name.

## [8.1.0]   2016-10-28
### Added
- Added preliminary support for generating FHIR DiagnosticReport data and merging
  it back into a form.

## [8.0.0]   2016-09-21
### Changed
- Updated the design and docs of the data control function.

## [7.6.1]   2016-09-21
### Fixed
- Renamed the CSS class 'empty-question' to 'lf-empty-question' to avoid
  possible confusion.

## [7.6.0]   2016-08-12
### Added
- Added support for setting the code systems for the form and for questions.

## [7.5.0]   2016-08-09
### Added
- Added support for a new data type, NR (numeric range).

## [7.4.1]   2016-08-08
### Fixed
- Prevented HTML tags in listColHeaders from rendering, to reduce the risk
  of XSS attacks.

## [7.4.0]   2016-08-05
### Added
- Added an option to hide the Units column/field.

## [7.3.0]   2016-08-04
### Added
- A listColHeaders option for specifying column headers to appear over the lists
  for search fields (lists specified with "externallyDefined").

## [7.2.0]   2016-08-01
### Added
- Added an optional codingInstructionsFormat field on item level to specify
  the format of coding instructions for each individual item.

### Fixed
- Added a missing polyfill.js file that should have been in 7.1.1.

## [7.1.1]   2016-07-29
### Changed
- Uncoded CWE values rendered as HL7 are now placed into OBX5.9.
- Adjusted formatting of HL7 output to allow the message lines to wrap after a
  repeat.

## [7.1.0]   2016-07-21
### Added
- The code system of an answer list can now be specified in "answerCodeSystem"
  on individual items in a form definition.

## [7.0.0]   2016-07-13
### Added
- User data validations based on dataType and restrictions

### Changed
- Use a single namespace, LForms, for all LForms functions and objects.

## [6.1.3]   2016-06-13
### Fixed
- Another CSS fix for IE.  This is a fix for the placeholder text for textareas,
  and should have been fixed as a part of the 6.1.1 changes, but was missed.

## [6.1.2]   2016-06-09
### Fixed
- Updated the autocomplete-lhc dependency (to 9.0.1) to get some fixes.

## [6.1.1]   2016-06-02
### Fixed
- Two CSS fixes for IE.

## [6.1.0]   2016-05-13
### Added
- Added a function to export user data in HL7 segments (in progress).

## [6.0.2]   2016-05-10
### Fixed
- Turned on "minification" of the distribution version of the JavaScript.

## [6.0.1]   2016-05-04
### Fixed
- Updated Protractor to work with new version of Firefox, and updated some
  tests to work with the new Protractor.

## [6.0.0]   2016-04-21
### Added
- A getFormData function to get complete form definition data, including
  user input data.
- Events are emitted when a repeating item or section is added or removed.

### Changed
- The existing getFormData function is renamed to getUserData.

## [5.7.0]   2016-04-19
### Added
- A 'list' template.
- A 'matrix' layout for section items, in addition to 'horizontal' and
  'vertical'.
- A attribute 'answerLayout' in the 'displayControl' field for items
  that have answers.
- A 'displayControl' field in 'templateOptions' at the form level.

### Changed
- All existing template names are combined into a new 'table' template
- The existing 'layout' field is renamed as 'questionLayout' and moved
  into the 'displayControl' field.

## [5.6.1]   2016-04-01
### Changed
- Field hints for search autocompleters were changed to read "Search for... "
  rather than "Select... " because for search autocompleters the user does not
  see a list until the first couple of characters are typed.

## [5.6.0]   2016-03-29
### Removed
- WidgetUtil.preprocessRIData.  This was an internal API, so it should not be
  a breaking change for anyone but ourselves.

