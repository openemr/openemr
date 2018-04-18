<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use Zend\Filter\Exception\RuntimeException;
use Zend\I18n\Filter\Alnum;
use Zend\I18n\Filter\Alpha;
use Zend\I18n\Filter\NumberFormat;
use Zend\I18n\Filter\NumberParse;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for the filter chain.
 *
 * Enforces that filters retrieved are either callbacks or instances of
 * FilterInterface. Additionally, it registers a number of default filters
 * available, as well as aliases for them.
 */
class FilterPluginManager extends AbstractPluginManager
{
    protected $aliases = [

        // For the future
        'int'                        => ToInt::class,
        'Int'                        => ToInt::class,
        'null'                       => ToNull::class,
        'Null'                       => ToNull::class,

        // I18n filters
        'alnum'                      => Alnum::class,
        'Alnum'                      => Alnum::class,
        'alpha'                      => Alpha::class,
        'Alpha'                      => Alpha::class,
        'numberformat'               => NumberFormat::class,
        'numberFormat'               => NumberFormat::class,
        'NumberFormat'               => NumberFormat::class,
        'numberparse'                => NumberParse::class,
        'numberParse'                => NumberParse::class,
        'NumberParse'                => NumberParse::class,

        // Standard filters
        'basename'                   => BaseName::class,
        'Basename'                   => BaseName::class,
        'blacklist'                  => Blacklist::class,
        'Blacklist'                  => Blacklist::class,
        'boolean'                    => Boolean::class,
        'Boolean'                    => Boolean::class,
        'callback'                   => Callback::class,
        'Callback'                   => Callback::class,
        'compress'                   => Compress::class,
        'Compress'                   => Compress::class,
        'dataunitformatter'          => DataUnitFormatter::class,
        'dataUnitFormatter'          => DataUnitFormatter::class,
        'DataUnitFormatter'          => DataUnitFormatter::class,
        'dateselect'                 => DateSelect::class,
        'dateSelect'                 => DateSelect::class,
        'DateSelect'                 => DateSelect::class,
        'datetimeformatter'          => DateTimeFormatter::class,
        'datetimeFormatter'          => DateTimeFormatter::class,
        'DatetimeFormatter'          => DateTimeFormatter::class,
        'dateTimeFormatter'          => DateTimeFormatter::class,
        'DateTimeFormatter'          => DateTimeFormatter::class,
        'datetimeselect'             => DateTimeSelect::class,
        'datetimeSelect'             => DateTimeSelect::class,
        'DatetimeSelect'             => DateTimeSelect::class,
        'dateTimeSelect'             => DateTimeSelect::class,
        'DateTimeSelect'             => DateTimeSelect::class,
        'decompress'                 => Decompress::class,
        'Decompress'                 => Decompress::class,
        'decrypt'                    => Decrypt::class,
        'Decrypt'                    => Decrypt::class,
        'digits'                     => Digits::class,
        'Digits'                     => Digits::class,
        'dir'                        => Dir::class,
        'Dir'                        => Dir::class,
        'encrypt'                    => Encrypt::class,
        'Encrypt'                    => Encrypt::class,
        'filedecrypt'                => File\Decrypt::class,
        'fileDecrypt'                => File\Decrypt::class,
        'FileDecrypt'                => File\Decrypt::class,
        'fileencrypt'                => File\Encrypt::class,
        'fileEncrypt'                => File\Encrypt::class,
        'FileEncrypt'                => File\Encrypt::class,
        'filelowercase'              => File\LowerCase::class,
        'fileLowercase'              => File\LowerCase::class,
        'FileLowercase'              => File\LowerCase::class,
        'fileLowerCase'              => File\LowerCase::class,
        'FileLowerCase'              => File\LowerCase::class,
        'filerename'                 => File\Rename::class,
        'fileRename'                 => File\Rename::class,
        'FileRename'                 => File\Rename::class,
        'filerenameupload'           => File\RenameUpload::class,
        'fileRenameUpload'           => File\RenameUpload::class,
        'FileRenameUpload'           => File\RenameUpload::class,
        'fileuppercase'              => File\UpperCase::class,
        'fileUppercase'              => File\UpperCase::class,
        'FileUppercase'              => File\UpperCase::class,
        'fileUpperCase'              => File\UpperCase::class,
        'FileUpperCase'              => File\UpperCase::class,
        'htmlentities'               => HtmlEntities::class,
        'htmlEntities'               => HtmlEntities::class,
        'HtmlEntities'               => HtmlEntities::class,
        'inflector'                  => Inflector::class,
        'Inflector'                  => Inflector::class,
        'monthselect'                => MonthSelect::class,
        'monthSelect'                => MonthSelect::class,
        'MonthSelect'                => MonthSelect::class,
        'pregreplace'                => PregReplace::class,
        'pregReplace'                => PregReplace::class,
        'PregReplace'                => PregReplace::class,
        'realpath'                   => RealPath::class,
        'realPath'                   => RealPath::class,
        'RealPath'                   => RealPath::class,
        'stringtolower'              => StringToLower::class,
        'stringToLower'              => StringToLower::class,
        'StringToLower'              => StringToLower::class,
        'stringtoupper'              => StringToUpper::class,
        'stringToUpper'              => StringToUpper::class,
        'StringToUpper'              => StringToUpper::class,
        'stringtrim'                 => StringTrim::class,
        'stringTrim'                 => StringTrim::class,
        'StringTrim'                 => StringTrim::class,
        'stripnewlines'              => StripNewlines::class,
        'stripNewlines'              => StripNewlines::class,
        'StripNewlines'              => StripNewlines::class,
        'striptags'                  => StripTags::class,
        'stripTags'                  => StripTags::class,
        'StripTags'                  => StripTags::class,
        'toint'                      => ToInt::class,
        'toInt'                      => ToInt::class,
        'ToInt'                      => ToInt::class,
        'tonull'                     => ToNull::class,
        'toNull'                     => ToNull::class,
        'ToNull'                     => ToNull::class,
        'uppercasewords'             => UpperCaseWords::class,
        'upperCaseWords'             => UpperCaseWords::class,
        'UpperCaseWords'             => UpperCaseWords::class,
        'urinormalize'               => UriNormalize::class,
        'uriNormalize'               => UriNormalize::class,
        'UriNormalize'               => UriNormalize::class,
        'whitelist'                  => Whitelist::class,
        'Whitelist'                  => Whitelist::class,
        'wordcamelcasetodash'        => Word\CamelCaseToDash::class,
        'wordCamelCaseToDash'        => Word\CamelCaseToDash::class,
        'WordCamelCaseToDash'        => Word\CamelCaseToDash::class,
        'wordcamelcasetoseparator'   => Word\CamelCaseToSeparator::class,
        'wordCamelCaseToSeparator'   => Word\CamelCaseToSeparator::class,
        'WordCamelCaseToSeparator'   => Word\CamelCaseToSeparator::class,
        'wordcamelcasetounderscore'  => Word\CamelCaseToUnderscore::class,
        'wordCamelCaseToUnderscore'  => Word\CamelCaseToUnderscore::class,
        'WordCamelCaseToUnderscore'  => Word\CamelCaseToUnderscore::class,
        'worddashtocamelcase'        => Word\DashToCamelCase::class,
        'wordDashToCamelCase'        => Word\DashToCamelCase::class,
        'WordDashToCamelCase'        => Word\DashToCamelCase::class,
        'worddashtoseparator'        => Word\DashToSeparator::class,
        'wordDashToSeparator'        => Word\DashToSeparator::class,
        'WordDashToSeparator'        => Word\DashToSeparator::class,
        'worddashtounderscore'       => Word\DashToUnderscore::class,
        'wordDashToUnderscore'       => Word\DashToUnderscore::class,
        'WordDashToUnderscore'       => Word\DashToUnderscore::class,
        'wordseparatortocamelcase'   => Word\SeparatorToCamelCase::class,
        'wordSeparatorToCamelCase'   => Word\SeparatorToCamelCase::class,
        'WordSeparatorToCamelCase'   => Word\SeparatorToCamelCase::class,
        'wordseparatortodash'        => Word\SeparatorToDash::class,
        'wordSeparatorToDash'        => Word\SeparatorToDash::class,
        'WordSeparatorToDash'        => Word\SeparatorToDash::class,
        'wordseparatortoseparator'   => Word\SeparatorToSeparator::class,
        'wordSeparatorToSeparator'   => Word\SeparatorToSeparator::class,
        'WordSeparatorToSeparator'   => Word\SeparatorToSeparator::class,
        'wordunderscoretocamelcase'  => Word\UnderscoreToCamelCase::class,
        'wordUnderscoreToCamelCase'  => Word\UnderscoreToCamelCase::class,
        'WordUnderscoreToCamelCase'  => Word\UnderscoreToCamelCase::class,
        'wordunderscoretostudlycase' => Word\UnderscoreToStudlyCase::class,
        'wordUnderscoreToStudlyCase' => Word\UnderscoreToStudlyCase::class,
        'WordUnderscoreToStudlyCase' => Word\UnderscoreToStudlyCase::class,
        'wordunderscoretodash'       => Word\UnderscoreToDash::class,
        'wordUnderscoreToDash'       => Word\UnderscoreToDash::class,
        'WordUnderscoreToDash'       => Word\UnderscoreToDash::class,
        'wordunderscoretoseparator'  => Word\UnderscoreToSeparator::class,
        'wordUnderscoreToSeparator'  => Word\UnderscoreToSeparator::class,
        'WordUnderscoreToSeparator'  => Word\UnderscoreToSeparator::class,
    ];

    /**
     * Default set of plugins factories
     *
     * @var array
     */
    protected $factories = [
        // I18n filters
        Alnum::class                           => InvokableFactory::class,
        Alpha::class                           => InvokableFactory::class,
        NumberFormat::class                    => InvokableFactory::class,
        NumberParse::class                     => InvokableFactory::class,

        // Standard filters
        BaseName::class                        => InvokableFactory::class,
        Blacklist::class                       => InvokableFactory::class,
        Boolean::class                         => InvokableFactory::class,
        Callback::class                        => InvokableFactory::class,
        Compress::class                        => InvokableFactory::class,
        DataUnitFormatter::class               => InvokableFactory::class,
        DateSelect::class                      => InvokableFactory::class,
        DateTimeFormatter::class               => InvokableFactory::class,
        DateTimeSelect::class                  => InvokableFactory::class,
        Decompress::class                      => InvokableFactory::class,
        Decrypt::class                         => InvokableFactory::class,
        Digits::class                          => InvokableFactory::class,
        Dir::class                             => InvokableFactory::class,
        Encrypt::class                         => InvokableFactory::class,
        File\Decrypt::class                    => InvokableFactory::class,
        File\Encrypt::class                    => InvokableFactory::class,
        File\LowerCase::class                  => InvokableFactory::class,
        File\Rename::class                     => InvokableFactory::class,
        File\RenameUpload::class               => InvokableFactory::class,
        File\UpperCase::class                  => InvokableFactory::class,
        HtmlEntities::class                    => InvokableFactory::class,
        Inflector::class                       => InvokableFactory::class,
        ToInt::class                           => InvokableFactory::class,
        MonthSelect::class                     => InvokableFactory::class,
        ToNull::class                          => InvokableFactory::class,
        UpperCaseWords::class                  => InvokableFactory::class,
        PregReplace::class                     => InvokableFactory::class,
        RealPath::class                        => InvokableFactory::class,
        StringToLower::class                   => InvokableFactory::class,
        StringToUpper::class                   => InvokableFactory::class,
        StringTrim::class                      => InvokableFactory::class,
        StripNewlines::class                   => InvokableFactory::class,
        StripTags::class                       => InvokableFactory::class,
        ToInt::class                           => InvokableFactory::class,
        ToNull::class                          => InvokableFactory::class,
        UriNormalize::class                    => InvokableFactory::class,
        Whitelist::class                       => InvokableFactory::class,
        Word\CamelCaseToDash::class            => InvokableFactory::class,
        Word\CamelCaseToSeparator::class       => InvokableFactory::class,
        Word\CamelCaseToUnderscore::class      => InvokableFactory::class,
        Word\DashToCamelCase::class            => InvokableFactory::class,
        Word\DashToSeparator::class            => InvokableFactory::class,
        Word\DashToUnderscore::class           => InvokableFactory::class,
        Word\SeparatorToCamelCase::class       => InvokableFactory::class,
        Word\SeparatorToDash::class            => InvokableFactory::class,
        Word\SeparatorToSeparator::class       => Word\Service\SeparatorToSeparatorFactory::class,
        Word\UnderscoreToCamelCase::class      => InvokableFactory::class,
        Word\UnderscoreToStudlyCase::class     => InvokableFactory::class,
        Word\UnderscoreToDash::class           => InvokableFactory::class,
        Word\UnderscoreToSeparator::class      => InvokableFactory::class,

        // v2 canonical FQCNs

        'zendfiltertoint'                      => InvokableFactory::class,
        'zendfiltertonull'                     => InvokableFactory::class,
        'zendi18nfilteralnum'                  => InvokableFactory::class,
        'zendi18nfilteralpha'                  => InvokableFactory::class,
        'zendi18nfilternumberformat'           => InvokableFactory::class,
        'zendi18nfilternumberparse'            => InvokableFactory::class,
        'zendfilterbasename'                   => InvokableFactory::class,
        'zendfilterblacklist'                  => InvokableFactory::class,
        'zendfilterboolean'                    => InvokableFactory::class,
        'zendfiltercallback'                   => InvokableFactory::class,
        'zendfiltercompress'                   => InvokableFactory::class,
        'zendfilterdataunitformatter'          => InvokableFactory::class,
        'zendfilterdateselect'                 => InvokableFactory::class,
        'zendfilterdatetimeformatter'          => InvokableFactory::class,
        'zendfilterdatetimeselect'             => InvokableFactory::class,
        'zendfilterdecompress'                 => InvokableFactory::class,
        'zendfilterdecrypt'                    => InvokableFactory::class,
        'zendfilterdigits'                     => InvokableFactory::class,
        'zendfilterdir'                        => InvokableFactory::class,
        'zendfilterencrypt'                    => InvokableFactory::class,
        'zendfilterfiledecrypt'                => InvokableFactory::class,
        'zendfilterfileencrypt'                => InvokableFactory::class,
        'zendfilterfilelowercase'              => InvokableFactory::class,
        'zendfilterfilerename'                 => InvokableFactory::class,
        'zendfilterfilerenameupload'           => InvokableFactory::class,
        'zendfilterfileuppercase'              => InvokableFactory::class,
        'zendfilterhtmlentities'               => InvokableFactory::class,
        'zendfilterinflector'                  => InvokableFactory::class,
        'zendfiltermonthselect'                => InvokableFactory::class,
        'zendfilterpregreplace'                => InvokableFactory::class,
        'zendfilterrealpath'                   => InvokableFactory::class,
        'zendfilterstringtolower'              => InvokableFactory::class,
        'zendfilterstringtoupper'              => InvokableFactory::class,
        'zendfilterstringtrim'                 => InvokableFactory::class,
        'zendfilterstripnewlines'              => InvokableFactory::class,
        'zendfilterstriptags'                  => InvokableFactory::class,
        'zendfilteruppercasewords'             => InvokableFactory::class,
        'zendfilterurinormalize'               => InvokableFactory::class,
        'zendfilterwhitelist'                  => InvokableFactory::class,
        'zendfilterwordcamelcasetodash'        => InvokableFactory::class,
        'zendfilterwordcamelcasetoseparator'   => InvokableFactory::class,
        'zendfilterwordcamelcasetounderscore'  => InvokableFactory::class,
        'zendfilterworddashtocamelcase'        => InvokableFactory::class,
        'zendfilterworddashtoseparator'        => InvokableFactory::class,
        'zendfilterworddashtounderscore'       => InvokableFactory::class,
        'zendfilterwordseparatortocamelcase'   => InvokableFactory::class,
        'zendfilterwordseparatortodash'        => InvokableFactory::class,
        'zendfilterwordseparatortoseparator'   => Word\Service\SeparatorToSeparatorFactory::class,
        'zendfilterwordunderscoretocamelcase'  => InvokableFactory::class,
        'zendfilterwordunderscoretostudlycase' => InvokableFactory::class,
        'zendfilterwordunderscoretodash'       => InvokableFactory::class,
        'zendfilterwordunderscoretoseparator'  => InvokableFactory::class,
    ];

    protected $instanceOf = FilterInterface::class;

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
     * {@inheritdoc}
     */
    public function validate($plugin)
    {
        if ($plugin instanceof $this->instanceOf) {
            // we're okay
            return;
        }

        if (is_callable($plugin)) {
            // also okay
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\FilterInterface or be callable',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }

    /**
     * Validate the plugin (v2)
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
