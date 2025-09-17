![Bower](https://img.shields.io/bower/v/checklist-model.svg) [![NPM](https://img.shields.io/npm/v/checklist-model.svg)](https://www.npmjs.com/package/checklist-model) ![License](https://img.shields.io/npm/l/checklist-model.svg)

[![NPM](https://nodei.co/npm/checklist-model.png)](https://nodei.co/npm/checklist-model/)

# checklist-model
AngularJS directive for list of checkboxes

## Why this is needed?  
In Angular one checkbox `<input type="checkbox" ng-model="...">` is linked
with one model.  
But in practice we usually want one model to store array of checked values
from several checkboxes.  
**Checklist-model** solves that task without additional code in controller.   

## Live demo
http://vitalets.github.io/checklist-model

## Installation
> Note: since version `1.0.0` you *must* install the `angular` library yourself as it is now a [`peerDependency`](https://nodejs.org/en/blog/npm/peer-dependencies/)

1. Download package:
    * From npm `npm install checklist-model`
    * From bower `bower install checklist-model`
    * From [latest release](https://github.com/vitalets/checklist-model/releases)
2. Include script to the HTML:
    ```html
    <script src='checklist-model.js'></script>
    ```
3. Add to app dependencies:
    ```js
    var app = angular.module("app", ["checklist-model"]);
    ```

## Usage
You should play with attributes of `<input>` tag:

| Attribute                 | Mandatory | Description                                   |
| :-----------------------: | :-------: | --------------------------------------------- |
| `checklist-model`         | Yes       | Use instead of `ng-model`                     |
| `checklist-value`         | No        | What should be picked as array item           |
| `value`                   | No        | What should be picked as item, but unlike `checklist-value`, this does not evaluate as an angular expression, but rather a static value |
| `ng-model`                | No        | Every checkbok will span a new scope and define a variable named `checked` to hold its state. You can modify this name by using this attribute. |
| `checklist-comparator`    | No   | A custom comparator. If it starts with dot(`.`) then it will be an expression applied to the array item. Otherwise it should evaluate to a function as an angular expression. The function return true if the first two arguments are equal and false otherwise. |
| `checklist-before-change` | No       | An angular expression evaluated each time before the `checklist-model` has changed. If it evaluates to 'false' then the model will not change anymore. |
| `checklist-change`        | No       | An angular expression evaluated each time the `checklist-model` has changed. |

* If you modify directly the value of the `checklist-model`, it is possible that the UI won't be updated. This is because this directive looks for the model in the parent, not in the current scope. Instead of doing `checklistModelList = []` you should do `checklistModelList.splice(0, checklistModelList.length)` or wrap it in another object. Consequently, instead of doing `checklistModelList = angular.copy(allValues)` you should do `checklistModelList.push.apply(checklistModelList, allValues)`. The idea is to use the same array and not replace it with a new one.
* If you're using `track by` you must specify the same thing for `checklist-value` too. See [#46](https://github.com/vitalets/checklist-model/issues/46).
* If you're also using `ngModel`, please keep in mind that the state of the checkbok is initialized with the value from `checklistModel`, not with the one from `ngModel`. Afterwards the two will be kept in sync, but initially, these two can be conflicting, so only `checklistModel` is used. See the entire discussion at [#104](https://github.com/vitalets/checklist-model/issues/104).

## Examples
* JsFiddle basic example (use this to report any issue): http://jsfiddle.net/beradrian/fjoLy5sq/
* JSFiddle required example: http://jsfiddle.net/beradrian/7wt9q1ev/  
* Plunkr example: http://plnkr.co/edit/0UrMwtiNQxJJbVWnYgSt?p=preview
* Plunkr example for [tree list](http://plnkr.co/edit/QPLk98pCljp8dFtptSYz?p=preview)

## How to get support
* Ask a question on StackOverflow and tag it with [checklist-model](http://stackoverflow.com/questions/tagged/checklist-model).
* [Fill in](https://github.com/vitalets/checklist-model/issues/new) an issue.

Please keep in mind to also add a Plunkr or JSFiddle example. This will greatly help us in assisting you and you can use one of the existing examples and fork it.

## Development
### How to run tests
1. Generate live demo *index.html* via `grunt jade` 
2. Run local http server `grunt server` 
3. Open in browser http://localhost:8000 to check that demo is accessible
4. Open in browser http://localhost:8000/test and wait until all tests pass

### How to add a new test case
1. Create a new folder under `docs/blocks` named `your-test`.
2. Create under that folder `ctrl.js` to describe the test Angular controller, `view.html` to describe the view part in HTML and `test.js` for the Angular scenario test. You can use an existing test as an example.
3. Add a line like `- items.push({id: 'your-test', text: 'Your test, ctrlName: 'CtrlTestName', testValue: 'selectedItems'})` to `docs/index.jade`
4. Add a line like `<script src="../docs/blocks/your-test/test.js"></script>` to `test\index.html`
5. Run `grunt jade` to generate `index.html` from `docs/index.jade`
6. Run `grunt server`
7. Access `http://localhost:8000` for samples and `http://localhost:8000/test` for running the tests.

### How to make a new release
1. Change the version number in `package.json`, `bower.json` and `checklist-model.nuspec` (if not already changed - check the version number against the latest release in Github)
2. Create a new [release](https://github.com/vitalets/checklist-model/releases) in github with the same name for tag and title as the version number (e.g. `1.0.0`). Do not forget to include the changelog in the release description.
3. Run `npm publish` to publish the new version to npm

## License
MIT
