# Standard Validation Classes

The following validators come with the zend-validator distribution.

- [Barcode](validators/barcode.md)
- [Between](validators/between.md)
- [Callback](validators/callback.md)
- [CreditCard](validators/credit-card.md)
- [Date](validators/date.md)
- [RecordExists and NoRecordExists (database)](validators/db.md)
- [Digits](validators/digits.md)
- [EmailAddress](validators/email-address.md)
- [File Validation Classes](validators/file/intro.md)
- [GreaterThan](validators/greater-than.md)
- [Hex](validators/hex.md)
- [Hostname](validators/hostname.md)
- [Iban](validators/iban.md)
- [Identical](validators/identical.md)
- [InArray](validators/in-array.md)
- [Ip](validators/ip.md)
- [Isbn](validators/isbn.md)
- [IsInstanceOf](validators/isinstanceof.md)
- [LessThan](validators/less-than.md)
- [NotEmpty](validators/not-empty.md)
- [Regex](validators/regex.md)
- [Sitemap](validators/sitemap.md)
- [Step](validators/step.md)
- [StringLength](validators/string-length.md)
- [Timezone](validators/timezone.md)
- [Uri](validators/uri.md)
- [Uuid](validators/uuid.md)

## Additional validators

Several other components offer validators as well:

- [zend-i18n](http://zendframework.github.io/zend-i18n/validators/)

## Deprecated Validators

### Ccnum

The `Ccnum` validator has been deprecated in favor of the `CreditCard`
validator. For security reasons you should use `CreditCard` instead of `Ccnum`.
