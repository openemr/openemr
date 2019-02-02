# View Helpers

zend-i18n ships with a set of zend-view helper classes related to
internationalization: e.g., formatting a date, formatting currency, or
displaying translated content.

See the [zend-view helpers documentation](http://framework.zend.com/manual/current/en/modules/zend.view.helpers.html#zend-view-helpers)
for more information.

## CurrencyFormat Helper

The `CurrencyFormat` view helper can be used to simplify rendering of localized
currency values. It acts as a wrapper for the `NumberFormatter` class within the
internationalization extension (ext/intl).

### Basic Usage

```php
// Within your view:

echo $this->currencyFormat(1234.56, 'USD', null, 'en_US');
// Returns: "$1,234.56"

echo $this->currencyFormat(1234.56, 'EUR', null, 'de_DE');
// Returns: "1.234,56 €"

echo $this->currencyFormat(1234.56, 'USD', true, 'en_US');
// Returns: "$1,234.56"

echo $this->currencyFormat(1234.56, 'USD', false, 'en_US');
// Returns: "$1,235"

echo $this->currencyFormat(12345678.90, 'EUR', true, 'de_DE', '#0.# kg');
// Returns: "12345678,90 kg"

echo $this->currencyFormat(12345678.90, 'EUR', false, 'de_DE', '#0.# kg');
// Returns: "12345679 kg"
```

### Method description

```php
currencyFormat(
    float $number [,
    string $currencyCode = null [,
    bool $showDecimals = null [,
    string $locale = null [,
    string $pattern = null
] ] ] ]) : string
```

where:

- `$number`: the numeric currency value.
- `$currencyCode`: the 3-letter ISO 4217 currency code indicating the currency
  to use. If unset, it will use the default value current in the helper
  instance (`null` by default).
- `$showDecimals`: Boolean `false` indicates that no decimals should be
  represented. If unset, it will use the value current in the helper instance
  (`true` by default).
- `$locale`: Locale in which the currency would be formatted (locale name, e.g.
  `en_US`). If unset, it will use the default locale (default is the value of
  `Locale::getDefault()`).
- `$pattern`: Pattern string the formatter should use. If unset, it will use the
  value current in the helper instance (`null` by default).


### Available Functionality

#### Set the currency code and the locale

The `$currencyCode` and `$locale` options can be set prior to formatting and
will be applied each time the helper is used:

```php
// Within your view

$this->plugin('currencyformat')->setCurrencyCode('USD')->setLocale('en_US');

echo $this->currencyFormat(1234.56);
// This returns: "$1,234.56"

echo $this->currencyFormat(5678.90);
// This returns: "$5,678.90"
```

The method signatures are:

```php
setCurrencyCode(string $currencyCode) : CurrencyFormat
```

where `$currencyCode` is the 3-letter ISO 4217 currency code, and

```php
setLocale(string $locale) : CurrencyFormat
```

where `$locale` is the locale with which to format the number.

#### Show decimals

```php
// Within your view

$this->plugin('currencyformat')->setShouldShowDecimals(false);

echo $this->currencyFormat(1234.56, 'USD', null, 'en_US');
// This returns: "$1,235"
```

with the following method signature:

```php
setShouldShowDecimals(bool $showDecimals) : CurrencyFormat
```

where `$showDecimals` indicates whether or not decimal values will be displayed.

#### Set the currency pattern

```php
// Within your view

$this->plugin('currencyformat')->setCurrencyPattern('#0.# kg');

echo $this->currencyFormat(12345678.90, 'EUR', null, 'de_DE');
// This returns: "12345678,90 kg"
```

with the following method signature:

```php
setCurrencyPattern(string $currencyPattern) : CurrencyFormat
```

where `$currencyPattern` is a valid [ICU DecimalFormat pattern](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#details);
see the [NumberFormatter::setPattern() documentation](http://php.net/manual/numberformatter.setpattern.php)
for more information.

## DateFormat Helper

The `DateFormat` view helper can be used to simplify rendering of localized
date/time values. It acts as a wrapper for the `IntlDateFormatter` class within
ext/intl.

### Basic Usage

```php
// Within your view

// Date and Time
echo $this->dateFormat(
    new DateTime(),
    IntlDateFormatter::MEDIUM, // date
    IntlDateFormatter::MEDIUM, // time
    "en_US"
);
// This returns: "Jul 2, 2012 6:44:03 PM"

// Date Only
echo $this->dateFormat(
    new DateTime(),
    IntlDateFormatter::LONG, // date
    IntlDateFormatter::NONE, // time
    "en_US"
);
// This returns: "July 2, 2012"

// Time Only
echo $this->dateFormat(
    new DateTime(),
    IntlDateFormatter::NONE,  // date
    IntlDateFormatter::SHORT, // time
    "en_US"
);
// This returns: "6:44 PM"
```

### Method description

```php
dateFormat(
    mixed $date [,
    int $dateType = null [,
    int $timeType = null [,
    string $locale = null
] ] ]) : string
```

where:

- `$date`: The value to format. This may be a `DateTime` instance, an integer
  representing a Unix timestamp value, or an array in the format returned by
  `localtime()`.
- `$dateType`: Date type to use (none, short, medium, long, full). This is one
  of the [IntlDateFormatter constants](http://www.php.net/manual/class.intldateformatter.php#intl.intldateformatter-constants).
  Defaults to `IntlDateFormatter::NONE`.
- `$timeType`: Time type to use (none, short, medium, long, full). This is one
  of the [IntlDateFormatter constants](http://www.php.net/manual/class.intldateformatter.php#intl.intldateformatter-constants).
  Defaults to `IntlDateFormatter::NONE`.
- `$locale`: Locale in which the date would be formatted (locale name, e.g.
  `en_US`). If unset, it will use the default locale (return value of
  `Locale::getDefault()`).

### Public Methods

The `$locale` option can be set prior to formatting with the `setLocale()`
method and will be applied each time the helper is used.

By default, the system's default timezone will be used when formatting. This
overrides any timezone that may be set inside a `DateTime` object. To change the
timezone when formatting, use the `setTimezone()` method.

```php
// Within your view
$this->plugin('dateFormat')
    ->setTimezone('America/New_York')
    ->setLocale('en_US');

echo $this->dateFormat(new DateTime(), IntlDateFormatter::MEDIUM);  // "Jul 2, 2012"
echo $this->dateFormat(new DateTime(), IntlDateFormatter::SHORT);   // "7/2/12"
```

## NumberFormat Helper

The `NumberFormat` view helper can be used to simplify rendering of
locale-specific number and/or percentage strings. It acts as a wrapper for the
`NumberFormatter` class within ext/intl.

### Basic Usage

```php
// Within your view

// Example of Decimal formatting:
echo $this->numberFormat(
    1234567.891234567890000,
    NumberFormatter::DECIMAL,
    NumberFormatter::TYPE_DEFAULT,
    'de_DE'
);
// This returns: "1.234.567,891"

// Example of Percent formatting:
echo $this->numberFormat(
    0.80,
    NumberFormatter::PERCENT,
    NumberFormatter::TYPE_DEFAULT,
    'en_US'
);
// This returns: "80%"

// Example of Scientific notation formatting:
echo $this->numberFormat(
    0.00123456789,
    NumberFormatter::SCIENTIFIC,
    NumberFormatter::TYPE_DEFAULT,
    'fr_FR'
);
// This returns: "1,23456789E-3"
```

### Method description

```php
numberFormat(
    int|float $number [,
    int $formatStyle = null [,
    int $formatType = null [,
    string $locale = null [,
    int $decimals = null [,
    array $textAttributes = null
] ] ] ] ]) : string
```

where:

- `$number`: the number to format.
- `$formatStyle`: one of the `NumberFormatter` styles:
  `NumberFormatter::DECIMAL`, `NumberFormatter::CURRENCY`, etc.
- `$formatType`: one of the `NumberFormatter` types:
  `NumberFormatter::TYPE_DEFAULT` (basic numeric),
  `NumberFormatter::TYPE_CURRENCY`, etc.
- `$locale`: a valid locale to use when formatting the number.
- `$decimals`: the number of digits beyond the decimal point to display.
- `$textAttributes`: text attributes to use with the number (e.g., prefix and/or
  suffix for positive/negative numbers, currency code):
  `NumberFormatter::POSITIVE_PREFIX`, `NumberFormatter::NEGATIVE_PREFIX`, etc.

### Public Methods

Each of the `$formatStyle`, `$formatType`, `$locale`, and `$textAttributes`
options can be set prior to formatting and will be applied each time the helper
is used.

```php
// Within your view
$this->plugin("numberformat")
            ->setFormatStyle(NumberFormatter::PERCENT)
            ->setFormatType(NumberFormatter::TYPE_DOUBLE)
            ->setLocale("en_US")
            ->setTextAttributes([
                NumberFormatter::POSITIVE_PREFIX => '^ ',
                NumberFormatter::NEGATIVE_PREFIX => 'v ',
            ]);

echo $this->numberFormat(0.56);   // "^ 56%"
echo $this->numberFormat(-0.90);  // "v 90%"
```

## Plural Helper

Most languages have specific rules for handling plurals. For instance, in
English, we say "0 cars" and "2 cars" (plural) while we say "1 car" (singular).
On the other hand, French uses the singular form for both 0 and 1 ("0 voiture"
and "1 voiture") and the plural form otherwise ("3 voitures").

Therefore we often need to handle plural cases even without translation (mono-lingual
application). The `Plural` helper was created for this.

> ### Plural helper does not translate
>
> If you need to handle both plural cases *and* translations, you must use the
> `TranslatePlural` helper; `Plural` does not translate.

Internally, the `Plural` helper uses the `Zend\I18n\Translator\Plural\Rule` class to handle rules.

### Setup

Defining plural rules is left to the developer. To help you with this process,
here are some links with up-to-date plural rules for tons of languages:

- [http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html)
- [https://developer.mozilla.org/en-US/docs/Localization_and_Plurals](https://developer.mozilla.org/en-US/docs/Localization_and_Plurals)

### Basic Usage

First, define a rule. As an example, you could add the following code in your
`Module` class:

```php
// Get the ViewHelperPlugin Manager from the ServiceManager, so we can fetch the
// Plural helper and add the plural rule for the application's language:
$viewHelperManager = $serviceManager->get('ViewHelperManager');
$pluralHelper      = $viewHelperManager->get('Plural');

// Here is the rule for French
$pluralHelper->setPluralRule('nplurals=2; plural=(n==0 || n==1 ? 0 : 1)');
```

The string reads as follows:

1. First, we specify how many plural forms we have. For French, only two (singular/plural).
2. Next, we specify the rule. Here, if the count is 0 or 1, this is rule n°0
   (singular) while it's rule n°1 otherwise.

As noted earlier earlier, English considers "1" as singular and "0/other" as
plural. Here is how that would be declared:

```php
// Here is the rule for English
$pluralHelper->setPluralRule('nplurals=2; plural=(n==1 ? 0 : 1)');
```

Now that we have defined the rule, we can use it in our views:

```php
// Within a view script...
// If the rule defined in Module.php is the English one:

echo $this->plural(array('car', 'cars'), 0); // prints "cars"
echo $this->plural(array('car', 'cars'), 1); // prints "car"

// If the rule defined in Module.php is the French one:
echo $this->plural(array('voiture', 'voitures'), 0); // prints "voiture"
echo $this->plural(array('voiture', 'voitures'), 1); // prints "voiture"
echo $this->plural(array('voiture', 'voitures'), 2); // prints "voitures"
```

## Translate Helper

The `Translate` view helper can be used to translate content. It acts as a
wrapper for the `Zend\I18n\Translator\Translator` class.

### Setup

Before using the `Translate` view helper, you must have first created a
`Translator` object and have attached it to the view helper. If you use the
`Zend\View\HelperPluginManager` to invoke the view helper, this will be done
automatically for you.

### Basic Usage

```php
// Within your view...

echo $this->translate("Some translated text.");
echo $this->translate("Translated text from a custom text domain.", "customDomain");
echo sprintf($this->translate("The current time is %s."), $currentTime);
echo $this->translate("Translate in a specific locale", "default", "de_DE");
```

### Method description

```php
translate(
    string $message [,
    string $textDomain = null [,
    string $locale = null
] ]) : string
```

where:

- `$message`: The message to translate.
- `$textDomain`: The text domain/context of the translation; defaults to
  "default".
- `$locale`: Locale to which the message should be translated (locale name, e.g.
  `en_US`). If unset, it will use the default locale (return value of
  `Locale::getDefault()`).

### Gettext

The `xgettext` utility can be used to compile `*.po` files from PHP source files containing the
translate view helper.

```bash
$ xgettext --language=php --add-location --keyword=translate my-view-file.phtml
```

See the [Gettext Wikipedia page](http://en.wikipedia.org/wiki/Gettext) for more information.

### Public Methods

Public methods for setting a `Zend\I18n\Translator\Translator` and a default
text domain are inherited from the [AbstractTranslatorHelper](#abstract-translator-helper).

## TranslatePlural Helper

The `TranslatePlural` view helper can be used to translate words which take into
account numeric meanings. English, for example, has a singular definition of
"car", for one car, and the plural definition, "cars", meaning zero "cars"
or more than one car. Other languages like Russian or Polish have more plurals
with different rules.

The helper acts as a wrapper for the `Zend\I18n\Translator\Translator` class.

### Setup

Before using the `TranslatePlural` view helper, you must have first created a
`Translator` object and have attached it to the view helper. If you use the
`Zend\View\HelperPluginManager` to invoke the view helper, this will be done
automatically for you.

### Basic Usage

```php
// Within your view
echo $this->translatePlural("car", "cars", $num);

// Use a custom domain
echo $this->translatePlural("monitor", "monitors", $num, "customDomain");

// Change locale
echo $this->translatePlural("locale", "locales", $num, "default", "de_DE");
```

### Method description

```php
translatePlural(
    string $singular,
    string $plural,
    int $number [,
    string $textDomain = null [,
    string $locale = null
] ]) : string
```

where:

- `$singular`: The message to use for singular values.
- `$plural`: The message to use for plural values.
- `$number`: The number to evaluate in order to determine which number to use.
- `$textDomain`: The text domain/context of the translation; defaults to
  "default".
- `$locale`: Locale to which the message should be translated (locale name, e.g.
  `en_US`). If unset, it will use the default locale (return value of
  `Locale::getDefault()`).

### Public Methods

Public methods for setting a `Zend\I18n\Translator\Translator` and a default
text domain are inherited from the [AbstractTranslatorHelper](#abstract-translator-helper).

## Abstract Translator Helper

The `AbstractTranslatorHelper` view helper is used as a base abstract class for
any helpers that need to translate content. It provides an implementation for
the `Zend\I18n\Translator\TranslatorAwareInterface`, allowing translator
injection as well as text domain injection.

### Public Methods

#### setTranslator()

```php
setTranslator(
    Translator $translator [ ,
    string $textDomain = null
] ) : void
```

Sets the `Zend\I18n\Translator\Translator` instance to use in the helper. The
`$textDomain` argument is optional, and provided as a convenienct to allow
setting both the translator and text domain simultaneously.

#### getTranslator()

```php
getTranslator() : Translator
```

Returns the `Zend\I18n\Translator\Translator` instance used by the helper.

#### hasTranslator()

```php
hasTranslator() : bool
```

Returns true if the helper composes a `Zend\I18n\Translator\Translator` instance.

#### setTranslatorEnabled()

```php
setTranslatorEnabled(bool $enabled) : void
```

Sets whether or not translations are enabled.

#### isTranslatorEnabled()

```php
isTranslatorEnabled() : bool
```

Returns true if translations are enabled.

#### setTranslatorTextDomain()

```php
setTranslatorTextDomain(string $textDomain) : void
```

Sets the default translation text domain to use with the helper.

#### getTranslatorTextDomain()

```php
getTranslatorTextDomain() : string
```

Returns the current text domain used by the helper.
