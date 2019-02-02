# Translation

zend-i18n comes with a complete translation suite supporting all major formats
and including popular features such as plural translations and text domains. The
Translator subcomponent is mostly dependency free, except for the fallback to a
default locale, where it relies on the PHP's intl extension.

The translator itself is initialized without any parameters, as any
configuration to it is optional. A translator without any translations will do
nothing but return all messages verbatim.

## Adding translations

Two options exist for adding translations to the translator:

- Add every translation file individually; use this for translation formats that
  store multiple locales in the same file.
- Add translation files based on a pattern; use this for formats that use one
  file per locale.

To add a single file to the translator, use the `addTranslationFile()` method:

```php
use Zend\I18n\Translator\Translator;

$translator = new Translator();
$translator->addTranslationFile($type, $filename, $textDomain, $locale);
```

where the arguments are:

- `$type`: the name of the format loader to use; see the next section for
  details.
- `$filename`: the file containing translations.
- `$textDomain`: a "category" name for translations. If this is omitted, it
  defaults to "default". Use text domains to segregate translations by context.
- `$locale`: the language strings are translated from; this argument is only
  required for formats which contain translations for single locales.

> ### Text domain and locale are related
>
> For each text domain and locale combination, there can only be one file
> loaded. Every successive file would override the translations which were
> loaded prior.

When storing one locale per file, you should specify those files via a pattern.
This allows you to add new translations to the file system, without touching
your code. Patterns are added with the `addTranslationFilePattern()` method:

```php
use Zend\I18n\Translator\Translator;

$translator = new Translator();
$translator->addTranslationFilePattern($type, $baseDir, $pattern, $textDomain);
```

where the arguments are roughly the same as for `addTranslationFile()`, with a
few differences:

- `$baseDir` is a directory containing translation files.
- `$pattern` is an `sprintf()`-formatted string describing a pattern for
  locating files under `$baseDir`. The `$pattern` should contain a substitution
  character for the `$locale` &mdash; which is omitted from the
  `addTranslationFilePattern()` call, but passed whenever a translation is
  requested. Use either `%s` or `%1$s` in the `$pattern` as a placeholder for
  the locale. As an example, if your translation files are located in
  `/var/messages/<LOCALE>/messages.mo`, your pattern would be
  `/var/messages/%s/messages.mo`.

## Supported formats

The translator supports the following major translation formats:

- PHP arrays
- Gettext
- INI

Additionally, you can use custom formats by implementing one or more of
`Zend\I18n\Translator\Loader\FileLoaderInterface` or
`Zend\I18n\Translator\Loader\RemoteLoaderInterface`, and registering your loader
with the `Translator` instance's composed plugin manager.

## Setting a locale

By default, the translator will get the locale to use from ext/intl's `Locale`
class. If you want to set an alternative locale explicitly, you can do so by
passing it to the `setLocale()` method.

When there is no translation for a specific message identifier in a locale, the
message identifier itself will be returned by default. Alternately, you can set
a fallback locale which is used to retrieve a fallback translation. To do so,
pass it to the `setFallbackLocale()` method.

## Translating messages

Translating messages is accomplished by calling the `translate()` method of the
translator:

```php
$translator->translate($message, $textDomain, $locale);
```

The message is the message identifier to translate. If it does not exist in the
loader, or is empty, the original message ID will be returned. The text domain
parameter is the one you specified when adding translations. If omitted, the
"default" text domain will be used. The locale parameter will usually not be
used in this context, as by default the locale is taken from the locale set in
the translator.

To translate plural messages, you can use the `translatePlural()` method. It
works similarly to `translate()`, but instead of a single message, it takes a
singular value, a plural value, and an additional integer number on which the
returned plural form is based:

```php
$translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
```

Plural translations are only available if the underlying format supports the
translation of plural messages and plural rule definitions.

## Caching

In production, it makes sense to cache your translations. This not only saves
you from loading and parsing the individual formats each time, but also
guarantees an optimized loading procedure. To enable caching, pass a
`Zend\Cache\Storage\Adapter` to the `setCache()` method. To disable the cache,
pass a `null` value to the method.
