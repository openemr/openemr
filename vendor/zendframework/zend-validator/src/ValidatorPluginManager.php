<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\I18n\Validator as I18nValidator;

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
        'bitwise'                  => Bitwise::class,
        'Bitwise'                  => Bitwise::class,
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
        I18nValidator\IsFloat::class           => InvokableFactory::class,
        IsInstanceOf::class                    => InvokableFactory::class,
        I18nValidator\IsInt::class             => InvokableFactory::class,
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

        'zendvalidatorbarcodecode25interleaved' => InvokableFactory::class,
        'zendvalidatorbarcodecode25'            => InvokableFactory::class,
        'zendvalidatorbarcodecode39ext'         => InvokableFactory::class,
        'zendvalidatorbarcodecode39'            => InvokableFactory::class,
        'zendvalidatorbarcodecode93ext'         => InvokableFactory::class,
        'zendvalidatorbarcodecode93'            => InvokableFactory::class,
        'zendvalidatorbarcodeean12'             => InvokableFactory::class,
        'zendvalidatorbarcodeean13'             => InvokableFactory::class,
        'zendvalidatorbarcodeean14'             => InvokableFactory::class,
        'zendvalidatorbarcodeean18'             => InvokableFactory::class,
        'zendvalidatorbarcodeean2'              => InvokableFactory::class,
        'zendvalidatorbarcodeean5'              => InvokableFactory::class,
        'zendvalidatorbarcodeean8'              => InvokableFactory::class,
        'zendvalidatorbarcodegtin12'            => InvokableFactory::class,
        'zendvalidatorbarcodegtin13'            => InvokableFactory::class,
        'zendvalidatorbarcodegtin14'            => InvokableFactory::class,
        'zendvalidatorbarcodeidentcode'         => InvokableFactory::class,
        'zendvalidatorbarcodeintelligentmail'   => InvokableFactory::class,
        'zendvalidatorbarcodeissn'              => InvokableFactory::class,
        'zendvalidatorbarcodeitf14'             => InvokableFactory::class,
        'zendvalidatorbarcodeleitcode'          => InvokableFactory::class,
        'zendvalidatorbarcodeplanet'            => InvokableFactory::class,
        'zendvalidatorbarcodepostnet'           => InvokableFactory::class,
        'zendvalidatorbarcoderoyalmail'         => InvokableFactory::class,
        'zendvalidatorbarcodesscc'              => InvokableFactory::class,
        'zendvalidatorbarcodeupca'              => InvokableFactory::class,
        'zendvalidatorbarcodeupce'              => InvokableFactory::class,
        'zendvalidatorbarcode'                  => InvokableFactory::class,
        'zendvalidatorbetween'                  => InvokableFactory::class,
        'zendvalidatorbitwise'                  => InvokableFactory::class,
        'zendvalidatorcallback'                 => InvokableFactory::class,
        'zendvalidatorcreditcard'               => InvokableFactory::class,
        'zendvalidatorcsrf'                     => InvokableFactory::class,
        'zendvalidatordatestep'                 => InvokableFactory::class,
        'zendvalidatordate'                     => InvokableFactory::class,
        'zendvalidatordbnorecordexists'         => InvokableFactory::class,
        'zendvalidatordbrecordexists'           => InvokableFactory::class,
        'zendvalidatordigits'                   => InvokableFactory::class,
        'zendvalidatoremailaddress'             => InvokableFactory::class,
        'zendvalidatorexplode'                  => InvokableFactory::class,
        'zendvalidatorfilecount'                => InvokableFactory::class,
        'zendvalidatorfilecrc32'                => InvokableFactory::class,
        'zendvalidatorfileexcludeextension'     => InvokableFactory::class,
        'zendvalidatorfileexcludemimetype'      => InvokableFactory::class,
        'zendvalidatorfileexists'               => InvokableFactory::class,
        'zendvalidatorfileextension'            => InvokableFactory::class,
        'zendvalidatorfilefilessize'            => InvokableFactory::class,
        'zendvalidatorfilehash'                 => InvokableFactory::class,
        'zendvalidatorfileimagesize'            => InvokableFactory::class,
        'zendvalidatorfileiscompressed'         => InvokableFactory::class,
        'zendvalidatorfileisimage'              => InvokableFactory::class,
        'zendvalidatorfilemd5'                  => InvokableFactory::class,
        'zendvalidatorfilemimetype'             => InvokableFactory::class,
        'zendvalidatorfilenotexists'            => InvokableFactory::class,
        'zendvalidatorfilesha1'                 => InvokableFactory::class,
        'zendvalidatorfilesize'                 => InvokableFactory::class,
        'zendvalidatorfileupload'               => InvokableFactory::class,
        'zendvalidatorfileuploadfile'           => InvokableFactory::class,
        'zendvalidatorfilewordcount'            => InvokableFactory::class,
        'zendvalidatorgpspoint'                 => InvokableFactory::class,
        'zendvalidatorgreaterthan'              => InvokableFactory::class,
        'zendvalidatorhex'                      => InvokableFactory::class,
        'zendvalidatorhostname'                 => InvokableFactory::class,
        'zendi18nvalidatoralnum'                => InvokableFactory::class,
        'zendi18nvalidatoralpha'                => InvokableFactory::class,
        'zendi18nvalidatordatetime'             => InvokableFactory::class,
        'zendi18nvalidatorisfloat'              => InvokableFactory::class,
        'zendi18nvalidatorisint'                => InvokableFactory::class,
        'zendi18nvalidatorphonenumber'          => InvokableFactory::class,
        'zendi18nvalidatorpostcode'             => InvokableFactory::class,
        'zendvalidatoriban'                     => InvokableFactory::class,
        'zendvalidatoridentical'                => InvokableFactory::class,
        'zendvalidatorinarray'                  => InvokableFactory::class,
        'zendvalidatorip'                       => InvokableFactory::class,
        'zendvalidatorisbn'                     => InvokableFactory::class,
        'zendvalidatorisinstanceof'             => InvokableFactory::class,
        'zendvalidatorlessthan'                 => InvokableFactory::class,
        'zendvalidatornotempty'                 => InvokableFactory::class,
        'zendvalidatorregex'                    => InvokableFactory::class,
        'zendvalidatorsitemapchangefreq'        => InvokableFactory::class,
        'zendvalidatorsitemaplastmod'           => InvokableFactory::class,
        'zendvalidatorsitemaploc'               => InvokableFactory::class,
        'zendvalidatorsitemappriority'          => InvokableFactory::class,
        'zendvalidatorstringlength'             => InvokableFactory::class,
        'zendvalidatorstep'                     => InvokableFactory::class,
        'zendvalidatortimezone'                 => InvokableFactory::class,
        'zendvalidatoruri'                      => InvokableFactory::class,
        'zendvalidatoruuid'                     => InvokableFactory::class,
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
                (is_object($plugin) ? get_class($plugin) : gettype($plugin))
            ));
        }
    }

    /**
     * For v2 compatibility: validate plugin instance.
     *
     * Proxies to `validate()`.
     *
     * @param mixed $plugin
     * @throws Exception\RuntimeException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
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
