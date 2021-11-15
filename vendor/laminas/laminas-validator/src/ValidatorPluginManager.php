<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

use Interop\Container\ContainerInterface;
use Laminas\I18n\Validator as I18nValidator;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

class ValidatorPluginManager extends AbstractPluginManager
{
    /**
     * Default set of aliases
     *
     * @var array
     */
    protected $aliases = [
        'alnum'                    => I18nValidator\Alnum::class,
        'Alnum'                    => I18nValidator\Alnum::class,
        'alpha'                    => I18nValidator\Alpha::class,
        'Alpha'                    => I18nValidator\Alpha::class,
        'barcode'                  => Barcode::class,
        'Barcode'                  => Barcode::class,
        'between'                  => Between::class,
        'Between'                  => Between::class,
        'BIC'                      => BusinessIdentifierCode::class,
        'bic'                      => BusinessIdentifierCode::class,
        'bitwise'                  => Bitwise::class,
        'Bitwise'                  => Bitwise::class,
        'BusinessIdentifierCode'   => BusinessIdentifierCode::class,
        'businessidentifiercode'   => BusinessIdentifierCode::class,
        'callback'                 => Callback::class,
        'Callback'                 => Callback::class,
        'creditcard'               => CreditCard::class,
        'creditCard'               => CreditCard::class,
        'CreditCard'               => CreditCard::class,
        'csrf'                     => Csrf::class,
        'Csrf'                     => Csrf::class,
        'date'                     => Date::class,
        'Date'                     => Date::class,
        'datestep'                 => DateStep::class,
        'dateStep'                 => DateStep::class,
        'DateStep'                 => DateStep::class,
        'datetime'                 => I18nValidator\DateTime::class,
        'dateTime'                 => I18nValidator\DateTime::class,
        'DateTime'                 => I18nValidator\DateTime::class,
        'dbnorecordexists'         => Db\NoRecordExists::class,
        'dbNoRecordExists'         => Db\NoRecordExists::class,
        'DbNoRecordExists'         => Db\NoRecordExists::class,
        'dbrecordexists'           => Db\RecordExists::class,
        'dbRecordExists'           => Db\RecordExists::class,
        'DbRecordExists'           => Db\RecordExists::class,
        'digits'                   => Digits::class,
        'Digits'                   => Digits::class,
        'emailaddress'             => EmailAddress::class,
        'emailAddress'             => EmailAddress::class,
        'EmailAddress'             => EmailAddress::class,
        'explode'                  => Explode::class,
        'Explode'                  => Explode::class,
        'filecount'                => File\Count::class,
        'fileCount'                => File\Count::class,
        'FileCount'                => File\Count::class,
        'filecrc32'                => File\Crc32::class,
        'fileCrc32'                => File\Crc32::class,
        'FileCrc32'                => File\Crc32::class,
        'fileexcludeextension'     => File\ExcludeExtension::class,
        'fileExcludeExtension'     => File\ExcludeExtension::class,
        'FileExcludeExtension'     => File\ExcludeExtension::class,
        'fileexcludemimetype'      => File\ExcludeMimeType::class,
        'fileExcludeMimeType'      => File\ExcludeMimeType::class,
        'FileExcludeMimeType'      => File\ExcludeMimeType::class,
        'fileexists'               => File\Exists::class,
        'fileExists'               => File\Exists::class,
        'FileExists'               => File\Exists::class,
        'fileextension'            => File\Extension::class,
        'fileExtension'            => File\Extension::class,
        'FileExtension'            => File\Extension::class,
        'filefilessize'            => File\FilesSize::class,
        'fileFilesSize'            => File\FilesSize::class,
        'FileFilesSize'            => File\FilesSize::class,
        'filehash'                 => File\Hash::class,
        'fileHash'                 => File\Hash::class,
        'FileHash'                 => File\Hash::class,
        'fileimagesize'            => File\ImageSize::class,
        'fileImageSize'            => File\ImageSize::class,
        'FileImageSize'            => File\ImageSize::class,
        'fileiscompressed'         => File\IsCompressed::class,
        'fileIsCompressed'         => File\IsCompressed::class,
        'FileIsCompressed'         => File\IsCompressed::class,
        'fileisimage'              => File\IsImage::class,
        'fileIsImage'              => File\IsImage::class,
        'FileIsImage'              => File\IsImage::class,
        'filemd5'                  => File\Md5::class,
        'fileMd5'                  => File\Md5::class,
        'FileMd5'                  => File\Md5::class,
        'filemimetype'             => File\MimeType::class,
        'fileMimeType'             => File\MimeType::class,
        'FileMimeType'             => File\MimeType::class,
        'filenotexists'            => File\NotExists::class,
        'fileNotExists'            => File\NotExists::class,
        'FileNotExists'            => File\NotExists::class,
        'filesha1'                 => File\Sha1::class,
        'fileSha1'                 => File\Sha1::class,
        'FileSha1'                 => File\Sha1::class,
        'filesize'                 => File\Size::class,
        'fileSize'                 => File\Size::class,
        'FileSize'                 => File\Size::class,
        'fileupload'               => File\Upload::class,
        'fileUpload'               => File\Upload::class,
        'FileUpload'               => File\Upload::class,
        'fileuploadfile'           => File\UploadFile::class,
        'fileUploadFile'           => File\UploadFile::class,
        'FileUploadFile'           => File\UploadFile::class,
        'filewordcount'            => File\WordCount::class,
        'fileWordCount'            => File\WordCount::class,
        'FileWordCount'            => File\WordCount::class,
        'float'                    => I18nValidator\IsFloat::class,
        'Float'                    => I18nValidator\IsFloat::class,
        'gpspoint'                 => GpsPoint::class,
        'gpsPoint'                 => GpsPoint::class,
        'GpsPoint'                 => GpsPoint::class,
        'greaterthan'              => GreaterThan::class,
        'greaterThan'              => GreaterThan::class,
        'GreaterThan'              => GreaterThan::class,
        'hex'                      => Hex::class,
        'Hex'                      => Hex::class,
        'hostname'                 => Hostname::class,
        'Hostname'                 => Hostname::class,
        'iban'                     => Iban::class,
        'Iban'                     => Iban::class,
        'identical'                => Identical::class,
        'Identical'                => Identical::class,
        'inarray'                  => InArray::class,
        'inArray'                  => InArray::class,
        'InArray'                  => InArray::class,
        'int'                      => I18nValidator\IsInt::class,
        'Int'                      => I18nValidator\IsInt::class,
        'ip'                       => Ip::class,
        'Ip'                       => Ip::class,
        'isbn'                     => Isbn::class,
        'Isbn'                     => Isbn::class,
        'isfloat'                  => I18nValidator\IsFloat::class,
        'isFloat'                  => I18nValidator\IsFloat::class,
        'IsFloat'                  => I18nValidator\IsFloat::class,
        'isinstanceof'             => IsInstanceOf::class,
        'isInstanceOf'             => IsInstanceOf::class,
        'IsInstanceOf'             => IsInstanceOf::class,
        'isint'                    => I18nValidator\IsInt::class,
        'isInt'                    => I18nValidator\IsInt::class,
        'IsInt'                    => I18nValidator\IsInt::class,
        'lessthan'                 => LessThan::class,
        'lessThan'                 => LessThan::class,
        'LessThan'                 => LessThan::class,
        'notempty'                 => NotEmpty::class,
        'notEmpty'                 => NotEmpty::class,
        'NotEmpty'                 => NotEmpty::class,
        'phonenumber'              => I18nValidator\PhoneNumber::class,
        'phoneNumber'              => I18nValidator\PhoneNumber::class,
        'PhoneNumber'              => I18nValidator\PhoneNumber::class,
        'postcode'                 => I18nValidator\PostCode::class,
        'postCode'                 => I18nValidator\PostCode::class,
        'PostCode'                 => I18nValidator\PostCode::class,
        'regex'                    => Regex::class,
        'Regex'                    => Regex::class,
        'sitemapchangefreq'        => Sitemap\Changefreq::class,
        'sitemapChangefreq'        => Sitemap\Changefreq::class,
        'SitemapChangefreq'        => Sitemap\Changefreq::class,
        'sitemaplastmod'           => Sitemap\Lastmod::class,
        'sitemapLastmod'           => Sitemap\Lastmod::class,
        'SitemapLastmod'           => Sitemap\Lastmod::class,
        'sitemaploc'               => Sitemap\Loc::class,
        'sitemapLoc'               => Sitemap\Loc::class,
        'SitemapLoc'               => Sitemap\Loc::class,
        'sitemappriority'          => Sitemap\Priority::class,
        'sitemapPriority'          => Sitemap\Priority::class,
        'SitemapPriority'          => Sitemap\Priority::class,
        'stringlength'             => StringLength::class,
        'stringLength'             => StringLength::class,
        'StringLength'             => StringLength::class,
        'step'                     => Step::class,
        'Step'                     => Step::class,
        'timezone'                 => Timezone::class,
        'Timezone'                 => Timezone::class,
        'uri'                      => Uri::class,
        'Uri'                      => Uri::class,
        'uuid'                     => Uuid::class,
        'Uuid'                     => Uuid::class,

        // Legacy Zend Framework aliases
        \Zend\I18n\Validator\Alnum::class => I18nValidator\Alnum::class,
        \Zend\I18n\Validator\Alpha::class => I18nValidator\Alpha::class,
        \Zend\Validator\Barcode::class => Barcode::class,
        \Zend\Validator\Between::class => Between::class,
        \Zend\Validator\Bitwise::class => Bitwise::class,
        \Zend\Validator\Callback::class => Callback::class,
        \Zend\Validator\CreditCard::class => CreditCard::class,
        \Zend\Validator\Csrf::class => Csrf::class,
        \Zend\Validator\DateStep::class => DateStep::class,
        \Zend\Validator\Date::class => Date::class,
        \Zend\I18n\Validator\DateTime::class => I18nValidator\DateTime::class,
        \Zend\Validator\Db\NoRecordExists::class => Db\NoRecordExists::class,
        \Zend\Validator\Db\RecordExists::class => Db\RecordExists::class,
        \Zend\Validator\Digits::class => Digits::class,
        \Zend\Validator\EmailAddress::class => EmailAddress::class,
        \Zend\Validator\Explode::class => Explode::class,
        \Zend\Validator\File\Count::class => File\Count::class,
        \Zend\Validator\File\Crc32::class => File\Crc32::class,
        \Zend\Validator\File\ExcludeExtension::class => File\ExcludeExtension::class,
        \Zend\Validator\File\ExcludeMimeType::class => File\ExcludeMimeType::class,
        \Zend\Validator\File\Exists::class => File\Exists::class,
        \Zend\Validator\File\Extension::class => File\Extension::class,
        \Zend\Validator\File\FilesSize::class => File\FilesSize::class,
        \Zend\Validator\File\Hash::class => File\Hash::class,
        \Zend\Validator\File\ImageSize::class => File\ImageSize::class,
        \Zend\Validator\File\IsCompressed::class => File\IsCompressed::class,
        \Zend\Validator\File\IsImage::class => File\IsImage::class,
        \Zend\Validator\File\Md5::class => File\Md5::class,
        \Zend\Validator\File\MimeType::class => File\MimeType::class,
        \Zend\Validator\File\NotExists::class => File\NotExists::class,
        \Zend\Validator\File\Sha1::class => File\Sha1::class,
        \Zend\Validator\File\Size::class => File\Size::class,
        \Zend\Validator\File\Upload::class => File\Upload::class,
        \Zend\Validator\File\UploadFile::class => File\UploadFile::class,
        \Zend\Validator\File\WordCount::class => File\WordCount::class,
        \Zend\I18n\Validator\IsFloat::class => I18nValidator\IsFloat::class,
        \Zend\Validator\GpsPoint::class => GpsPoint::class,
        \Zend\Validator\GreaterThan::class => GreaterThan::class,
        \Zend\Validator\Hex::class => Hex::class,
        \Zend\Validator\Hostname::class => Hostname::class,
        \Zend\Validator\Iban::class => Iban::class,
        \Zend\Validator\Identical::class => Identical::class,
        \Zend\Validator\InArray::class => InArray::class,
        \Zend\I18n\Validator\IsInt::class => I18nValidator\IsInt::class,
        \Zend\Validator\Ip::class => Ip::class,
        \Zend\Validator\Isbn::class => Isbn::class,
        \Zend\Validator\IsInstanceOf::class => IsInstanceOf::class,
        \Zend\Validator\LessThan::class => LessThan::class,
        \Zend\Validator\NotEmpty::class => NotEmpty::class,
        \Zend\I18n\Validator\PhoneNumber::class => I18nValidator\PhoneNumber::class,
        \Zend\I18n\Validator\PostCode::class => I18nValidator\PostCode::class,
        \Zend\Validator\Regex::class => Regex::class,
        \Zend\Validator\Sitemap\Changefreq::class => Sitemap\Changefreq::class,
        \Zend\Validator\Sitemap\Lastmod::class => Sitemap\Lastmod::class,
        \Zend\Validator\Sitemap\Loc::class => Sitemap\Loc::class,
        \Zend\Validator\Sitemap\Priority::class => Sitemap\Priority::class,
        \Zend\Validator\StringLength::class => StringLength::class,
        \Zend\Validator\Step::class => Step::class,
        \Zend\Validator\Timezone::class => Timezone::class,
        \Zend\Validator\Uri::class => Uri::class,
        \Zend\Validator\Uuid::class => Uuid::class,

        // v2 normalized FQCNs
        'zendvalidatorbarcode' => Barcode::class,
        'zendvalidatorbetween' => Between::class,
        'zendvalidatorbitwise' => Bitwise::class,
        'zendvalidatorcallback' => Callback::class,
        'zendvalidatorcreditcard' => CreditCard::class,
        'zendvalidatorcsrf' => Csrf::class,
        'zendvalidatordatestep' => DateStep::class,
        'zendvalidatordate' => Date::class,
        'zendvalidatordbnorecordexists' => Db\NoRecordExists::class,
        'zendvalidatordbrecordexists' => Db\RecordExists::class,
        'zendvalidatordigits' => Digits::class,
        'zendvalidatoremailaddress' => EmailAddress::class,
        'zendvalidatorexplode' => Explode::class,
        'zendvalidatorfilecount' => File\Count::class,
        'zendvalidatorfilecrc32' => File\Crc32::class,
        'zendvalidatorfileexcludeextension' => File\ExcludeExtension::class,
        'zendvalidatorfileexcludemimetype' => File\ExcludeMimeType::class,
        'zendvalidatorfileexists' => File\Exists::class,
        'zendvalidatorfileextension' => File\Extension::class,
        'zendvalidatorfilefilessize' => File\FilesSize::class,
        'zendvalidatorfilehash' => File\Hash::class,
        'zendvalidatorfileimagesize' => File\ImageSize::class,
        'zendvalidatorfileiscompressed' => File\IsCompressed::class,
        'zendvalidatorfileisimage' => File\IsImage::class,
        'zendvalidatorfilemd5' => File\Md5::class,
        'zendvalidatorfilemimetype' => File\MimeType::class,
        'zendvalidatorfilenotexists' => File\NotExists::class,
        'zendvalidatorfilesha1' => File\Sha1::class,
        'zendvalidatorfilesize' => File\Size::class,
        'zendvalidatorfileupload' => File\Upload::class,
        'zendvalidatorfileuploadfile' => File\UploadFile::class,
        'zendvalidatorfilewordcount' => File\WordCount::class,
        'zendvalidatorgpspoint' => GpsPoint::class,
        'zendvalidatorgreaterthan' => GreaterThan::class,
        'zendvalidatorhex' => Hex::class,
        'zendvalidatorhostname' => Hostname::class,
        'zendi18nvalidatoralnum' => I18nValidator\Alnum::class,
        'zendi18nvalidatoralpha' => I18nValidator\Alpha::class,
        'zendi18nvalidatordatetime' => I18nValidator\DateTime::class,
        'zendi18nvalidatorisfloat' => I18nValidator\IsFloat::class,
        'zendi18nvalidatorisint' => I18nValidator\IsInt::class,
        'zendi18nvalidatorphonenumber' => I18nValidator\PhoneNumber::class,
        'zendi18nvalidatorpostcode' => I18nValidator\PostCode::class,
        'zendvalidatoriban' => Iban::class,
        'zendvalidatoridentical' => Identical::class,
        'zendvalidatorinarray' => InArray::class,
        'zendvalidatorip' => Ip::class,
        'zendvalidatorisbn' => Isbn::class,
        'zendvalidatorisinstanceof' => IsInstanceOf::class,
        'zendvalidatorlessthan' => LessThan::class,
        'zendvalidatornotempty' => NotEmpty::class,
        'zendvalidatorregex' => Regex::class,
        'zendvalidatorsitemapchangefreq' => Sitemap\Changefreq::class,
        'zendvalidatorsitemaplastmod' => Sitemap\Lastmod::class,
        'zendvalidatorsitemaploc' => Sitemap\Loc::class,
        'zendvalidatorsitemappriority' => Sitemap\Priority::class,
        'zendvalidatorstringlength' => StringLength::class,
        'zendvalidatorstep' => Step::class,
        'zendvalidatortimezone' => Timezone::class,
        'zendvalidatoruri' => Uri::class,
        'zendvalidatoruuid' => Uuid::class,
    ];

    /**
     * Default set of factories
     *
     * @var array
     */
    protected $factories = [
        I18nValidator\Alnum::class             => InvokableFactory::class,
        I18nValidator\Alpha::class             => InvokableFactory::class,
        Barcode::class                         => InvokableFactory::class,
        Between::class                         => InvokableFactory::class,
        Bitwise::class                         => InvokableFactory::class,
        BusinessIdentifierCode::class          => InvokableFactory::class,
        Callback::class                        => InvokableFactory::class,
        CreditCard::class                      => InvokableFactory::class,
        Csrf::class                            => InvokableFactory::class,
        DateStep::class                        => InvokableFactory::class,
        Date::class                            => InvokableFactory::class,
        I18nValidator\DateTime::class          => InvokableFactory::class,
        Db\NoRecordExists::class               => InvokableFactory::class,
        Db\RecordExists::class                 => InvokableFactory::class,
        Digits::class                          => InvokableFactory::class,
        EmailAddress::class                    => InvokableFactory::class,
        Explode::class                         => InvokableFactory::class,
        File\Count::class                      => InvokableFactory::class,
        File\Crc32::class                      => InvokableFactory::class,
        File\ExcludeExtension::class           => InvokableFactory::class,
        File\ExcludeMimeType::class            => InvokableFactory::class,
        File\Exists::class                     => InvokableFactory::class,
        File\Extension::class                  => InvokableFactory::class,
        File\FilesSize::class                  => InvokableFactory::class,
        File\Hash::class                       => InvokableFactory::class,
        File\ImageSize::class                  => InvokableFactory::class,
        File\IsCompressed::class               => InvokableFactory::class,
        File\IsImage::class                    => InvokableFactory::class,
        File\Md5::class                        => InvokableFactory::class,
        File\MimeType::class                   => InvokableFactory::class,
        File\NotExists::class                  => InvokableFactory::class,
        File\Sha1::class                       => InvokableFactory::class,
        File\Size::class                       => InvokableFactory::class,
        File\Upload::class                     => InvokableFactory::class,
        File\UploadFile::class                 => InvokableFactory::class,
        File\WordCount::class                  => InvokableFactory::class,
        I18nValidator\IsFloat::class           => InvokableFactory::class,
        GpsPoint::class                        => InvokableFactory::class,
        GreaterThan::class                     => InvokableFactory::class,
        Hex::class                             => InvokableFactory::class,
        Hostname::class                        => InvokableFactory::class,
        Iban::class                            => InvokableFactory::class,
        Identical::class                       => InvokableFactory::class,
        InArray::class                         => InvokableFactory::class,
        I18nValidator\IsInt::class             => InvokableFactory::class,
        Ip::class                              => InvokableFactory::class,
        Isbn::class                            => InvokableFactory::class,
        IsInstanceOf::class                    => InvokableFactory::class,
        LessThan::class                        => InvokableFactory::class,
        NotEmpty::class                        => InvokableFactory::class,
        I18nValidator\PhoneNumber::class       => InvokableFactory::class,
        I18nValidator\PostCode::class          => InvokableFactory::class,
        Regex::class                           => InvokableFactory::class,
        Sitemap\Changefreq::class              => InvokableFactory::class,
        Sitemap\Lastmod::class                 => InvokableFactory::class,
        Sitemap\Loc::class                     => InvokableFactory::class,
        Sitemap\Priority::class                => InvokableFactory::class,
        StringLength::class                    => InvokableFactory::class,
        Step::class                            => InvokableFactory::class,
        Timezone::class                        => InvokableFactory::class,
        Uri::class                             => InvokableFactory::class,
        Uuid::class                            => InvokableFactory::class,

        // v2 canonical FQCNs

        'laminasvalidatorbarcodecode25interleaved' => InvokableFactory::class,
        'laminasvalidatorbarcodecode25'            => InvokableFactory::class,
        'laminasvalidatorbarcodecode39ext'         => InvokableFactory::class,
        'laminasvalidatorbarcodecode39'            => InvokableFactory::class,
        'laminasvalidatorbarcodecode93ext'         => InvokableFactory::class,
        'laminasvalidatorbarcodecode93'            => InvokableFactory::class,
        'laminasvalidatorbarcodeean12'             => InvokableFactory::class,
        'laminasvalidatorbarcodeean13'             => InvokableFactory::class,
        'laminasvalidatorbarcodeean14'             => InvokableFactory::class,
        'laminasvalidatorbarcodeean18'             => InvokableFactory::class,
        'laminasvalidatorbarcodeean2'              => InvokableFactory::class,
        'laminasvalidatorbarcodeean5'              => InvokableFactory::class,
        'laminasvalidatorbarcodeean8'              => InvokableFactory::class,
        'laminasvalidatorbarcodegtin12'            => InvokableFactory::class,
        'laminasvalidatorbarcodegtin13'            => InvokableFactory::class,
        'laminasvalidatorbarcodegtin14'            => InvokableFactory::class,
        'laminasvalidatorbarcodeidentcode'         => InvokableFactory::class,
        'laminasvalidatorbarcodeintelligentmail'   => InvokableFactory::class,
        'laminasvalidatorbarcodeissn'              => InvokableFactory::class,
        'laminasvalidatorbarcodeitf14'             => InvokableFactory::class,
        'laminasvalidatorbarcodeleitcode'          => InvokableFactory::class,
        'laminasvalidatorbarcodeplanet'            => InvokableFactory::class,
        'laminasvalidatorbarcodepostnet'           => InvokableFactory::class,
        'laminasvalidatorbarcoderoyalmail'         => InvokableFactory::class,
        'laminasvalidatorbarcodesscc'              => InvokableFactory::class,
        'laminasvalidatorbarcodeupca'              => InvokableFactory::class,
        'laminasvalidatorbarcodeupce'              => InvokableFactory::class,
        'laminasvalidatorbarcode'                  => InvokableFactory::class,
        'laminasvalidatorbetween'                  => InvokableFactory::class,
        'laminasvalidatorbitwise'                  => InvokableFactory::class,
        'laminasvalidatorcallback'                 => InvokableFactory::class,
        'laminasvalidatorcreditcard'               => InvokableFactory::class,
        'laminasvalidatorcsrf'                     => InvokableFactory::class,
        'laminasvalidatordatestep'                 => InvokableFactory::class,
        'laminasvalidatordate'                     => InvokableFactory::class,
        'laminasvalidatordbnorecordexists'         => InvokableFactory::class,
        'laminasvalidatordbrecordexists'           => InvokableFactory::class,
        'laminasvalidatordigits'                   => InvokableFactory::class,
        'laminasvalidatoremailaddress'             => InvokableFactory::class,
        'laminasvalidatorexplode'                  => InvokableFactory::class,
        'laminasvalidatorfilecount'                => InvokableFactory::class,
        'laminasvalidatorfilecrc32'                => InvokableFactory::class,
        'laminasvalidatorfileexcludeextension'     => InvokableFactory::class,
        'laminasvalidatorfileexcludemimetype'      => InvokableFactory::class,
        'laminasvalidatorfileexists'               => InvokableFactory::class,
        'laminasvalidatorfileextension'            => InvokableFactory::class,
        'laminasvalidatorfilefilessize'            => InvokableFactory::class,
        'laminasvalidatorfilehash'                 => InvokableFactory::class,
        'laminasvalidatorfileimagesize'            => InvokableFactory::class,
        'laminasvalidatorfileiscompressed'         => InvokableFactory::class,
        'laminasvalidatorfileisimage'              => InvokableFactory::class,
        'laminasvalidatorfilemd5'                  => InvokableFactory::class,
        'laminasvalidatorfilemimetype'             => InvokableFactory::class,
        'laminasvalidatorfilenotexists'            => InvokableFactory::class,
        'laminasvalidatorfilesha1'                 => InvokableFactory::class,
        'laminasvalidatorfilesize'                 => InvokableFactory::class,
        'laminasvalidatorfileupload'               => InvokableFactory::class,
        'laminasvalidatorfileuploadfile'           => InvokableFactory::class,
        'laminasvalidatorfilewordcount'            => InvokableFactory::class,
        'laminasvalidatorgpspoint'                 => InvokableFactory::class,
        'laminasvalidatorgreaterthan'              => InvokableFactory::class,
        'laminasvalidatorhex'                      => InvokableFactory::class,
        'laminasvalidatorhostname'                 => InvokableFactory::class,
        'laminasi18nvalidatoralnum'                => InvokableFactory::class,
        'laminasi18nvalidatoralpha'                => InvokableFactory::class,
        'laminasi18nvalidatordatetime'             => InvokableFactory::class,
        'laminasi18nvalidatorisfloat'              => InvokableFactory::class,
        'laminasi18nvalidatorisint'                => InvokableFactory::class,
        'laminasi18nvalidatorphonenumber'          => InvokableFactory::class,
        'laminasi18nvalidatorpostcode'             => InvokableFactory::class,
        'laminasvalidatoriban'                     => InvokableFactory::class,
        'laminasvalidatoridentical'                => InvokableFactory::class,
        'laminasvalidatorinarray'                  => InvokableFactory::class,
        'laminasvalidatorip'                       => InvokableFactory::class,
        'laminasvalidatorisbn'                     => InvokableFactory::class,
        'laminasvalidatorisinstanceof'             => InvokableFactory::class,
        'laminasvalidatorlessthan'                 => InvokableFactory::class,
        'laminasvalidatornotempty'                 => InvokableFactory::class,
        'laminasvalidatorregex'                    => InvokableFactory::class,
        'laminasvalidatorsitemapchangefreq'        => InvokableFactory::class,
        'laminasvalidatorsitemaplastmod'           => InvokableFactory::class,
        'laminasvalidatorsitemaploc'               => InvokableFactory::class,
        'laminasvalidatorsitemappriority'          => InvokableFactory::class,
        'laminasvalidatorstringlength'             => InvokableFactory::class,
        'laminasvalidatorstep'                     => InvokableFactory::class,
        'laminasvalidatortimezone'                 => InvokableFactory::class,
        'laminasvalidatoruri'                      => InvokableFactory::class,
        'laminasvalidatoruuid'                     => InvokableFactory::class,
    ];

    /**
     * Whether or not to share by default; default to false (v2)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Whether or not to share by default; default to false (v3)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Default instance type
     *
     * @var string
     */
    protected $instanceOf = ValidatorInterface::class;

    /**
     * Constructor
     *
     * After invoking parent constructor, add an initializer to inject the
     * attached translator, if any, to the currently requested helper.
     *
     * {@inheritDoc}
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        parent::__construct($configOrContainerInstance, $v3config);

        $this->addInitializer([$this, 'injectTranslator']);
        $this->addInitializer([$this, 'injectValidatorPluginManager']);
    }

    /**
     * Validate plugin instance
     *
     * {@inheritDoc}
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s expects only to create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                is_object($plugin) ? get_class($plugin) : gettype($plugin)
            ));
        }
    }

    /**
     * For v2 compatibility: validate plugin instance.
     *
     * Proxies to `validate()`.
     *
     * @param mixed $plugin
     * @return void
     * @throws Exception\RuntimeException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                is_object($plugin) ? get_class($plugin) : gettype($plugin),
                ValidatorInterface::class
            ), $e->getCode(), $e);
        }
    }

    /**
     * Inject a validator instance with the registered translator
     *
     * @param  ContainerInterface|object $first
     * @param  ContainerInterface|object $second
     * @return void
     */
    public function injectTranslator($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $container = $first;
            $validator = $second;
        } else {
            $container = $second;
            $validator = $first;
        }

        // V2 means we pull it from the parent container
        if ($container === $this && method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }

        if ($validator instanceof Translator\TranslatorAwareInterface) {
            if ($container && $container->has('MvcTranslator')) {
                $validator->setTranslator($container->get('MvcTranslator'));
            }
        }
    }

    /**
     * Inject a validator plugin manager
     *
     * @param  ContainerInterface|object $first
     * @param  ContainerInterface|object $second
     * @return void
     */
    public function injectValidatorPluginManager($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $container = $first;
            $validator = $second;
        } else {
            $container = $second;
            $validator = $first;
        }
        if ($validator instanceof ValidatorPluginManagerAwareInterface) {
            $validator->setValidatorPluginManager($this);
        }
    }
}
