# Iban Validator

`Zend\Validator\Iban` validates if a given value could be a IBAN number. IBAN is
the abbreviation for "International Bank Account Number".

## Supported options

The following options are supported for `Zend\Validator\Iban`:

- `country_code`: Sets the country code which is used to get the IBAN format
  for validation.

## IBAN validation

IBAN numbers are always related to a country. This means that different
countries use different formats for their IBAN numbers. This is the reason why
IBAN numbers always need a country code. By knowing this we already know how
to use `Zend\Validator\Iban`.

### Ungreedy IBAN validation

Sometime it is useful just to validate if the given value is a IBAN number or
not. This means that you don't want to validate it against a defined country.
This can be done by using `false` as locale.

```php
$validator = new Zend\Validator\Iban(['country_code' => false]);
// Note: you can also provide FALSE as the sole parameter

if ($validator->isValid('AT611904300234573201')) {
    // IBAN appears to be valid
} else {
    // IBAN is not valid
}
```

In this situation, any IBAN number from any country will considered valid. Note
that this should not be done when you accept only accounts from a single
country!

### Region aware IBAN validation

To validate against a defined country, you just provide a country code. You can
do this during instaniation via the option `country_code`, or afterwards by
using `setCountryCode()`.

```php
$validator = new Zend\Validator\Iban(['country_code' => 'AT']);

if ($validator->isValid('AT611904300234573201')) {
    // IBAN appears to be valid
} else {
    // IBAN is not valid
}
```
