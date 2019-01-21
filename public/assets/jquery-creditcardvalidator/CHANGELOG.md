# Change log

## 1.1

### New features

* Changed number matching engine from regex to [trie-backed ranges](https://github.com/PawelDecowski/jquery-creditcardvalidator/wiki/Trie)
* New card brands: Dankort, UATP
* Updated NIN ranges: Maestro, MasterCard
* Allow VISA lengths between 13 and 19 digits

## 1.0

### Breaking changes

* Minimum required version of jQuery is now 1.7. This is because the events are now attached using `.on` instead of `.bind`. The former is not available in jQuery prior to 1.7.

### New features

* Unit tests — thanks to [James Allardice](https://github.com/jamesallardice).

* Binding is now optional — thanks to [Tanner M Young](https://github.com/tmyoung).

  ```js
  .validateCreditCard( [options] )
  ```

  Called on an input field validates the number and *returns* a `result` object.

* Ability to pass an array of accepted credit cards — thanks to [gabrieljoelc](https://github.com/gabrieljoelc).

  ```js
  $('#cc_number').validateCreditCard({ accept: ['visa', 'mastercard'] })
  ```

* `this` variable in the context of callback refers to the input element the validation is bound to.

  ```js
  $('#cc_number').validateCreditCard(function() { console.log(this.val()) })
  ```

  The code above will log the value of the credit card number field to the console every time the value changes.

* The result object now includes a `valid` property which is a shorthand for `length_valid && luhn_valid`

* The library is now in [Bower](http://bower.io/search/?q=jquery-creditcardvalidator).

### Bug fixes

* Events are now namespaced. This prevents accidental unbinding of events attached by other plugins.

### Other changes

* Added a basic example of usage (in the `example` directory).

* Redesigned [demo page](http://jquerycreditcardvalidator.com) — thanks to [Relish](https://relish.io).

* MIT licence.

  It’s much clearer than any other licences. It means you can use jQuery CC Validator in any way you want as long as you include the copyright notice and licence text (found at the top of the source file).

## pre-1.0

jQuery Credit Card Validator was released three years before turning 1.0. It had gone through a lot of changes but wasn’t versioned so everything pre-1.0 is to be treated as *alpha*.
