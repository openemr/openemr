<?php

declare(strict_types=1);

namespace OpenEMR\FHIR;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

abstract class Autoloader
{
    private const _ROOT_NAMESPACE = 'OpenEMR\FHIR\\';

    private const _VERSION_AUTOLOADER_MAP = [
        0 => [
            'OpenEMR\FHIR\Versions\R4\\',
            \OpenEMR\FHIR\Versions\R4\Autoloader::class,
            __DIR__ . '/Versions/R4/Autoloader.php',
        ],
    ];

    private const _CORE_CLASS_MAP = [
        // core types
        \OpenEMR\FHIR\Types\CommentContainerInterface::class => __DIR__ . '/Types/CommentContainerInterface.php',
        \OpenEMR\FHIR\Types\ValueContainerTrait::class => __DIR__ . '/Types/ValueContainerTrait.php',
        \OpenEMR\FHIR\Types\CommentContainerTrait::class => __DIR__ . '/Types/CommentContainerTrait.php',
        \OpenEMR\FHIR\Types\ValueContainerTypeInterface::class => __DIR__ . '/Types/ValueContainerTypeInterface.php',
        \OpenEMR\FHIR\Types\ResourceContainerTypeInterface::class => __DIR__ . '/Types/ResourceContainerTypeInterface.php',
        \OpenEMR\FHIR\Types\ElementTypeInterface::class => __DIR__ . '/Types/ElementTypeInterface.php',
        \OpenEMR\FHIR\Types\ResourceIDTypeInterface::class => __DIR__ . '/Types/ResourceIDTypeInterface.php',
        \OpenEMR\FHIR\Types\ResourceTypeInterface::class => __DIR__ . '/Types/ResourceTypeInterface.php',
        \OpenEMR\FHIR\Types\PrimitiveTypeInterface::class => __DIR__ . '/Types/PrimitiveTypeInterface.php',
        \OpenEMR\FHIR\Types\SourceXMLNamespaceTrait::class => __DIR__ . '/Types/SourceXMLNamespaceTrait.php',
        \OpenEMR\FHIR\Types\TypeInterface::class => __DIR__ . '/Types/TypeInterface.php',
        \OpenEMR\FHIR\Types\ContainedTypeInterface::class => __DIR__ . '/Types/ContainedTypeInterface.php',
        \OpenEMR\FHIR\Types\PrimitiveContainerTypeInterface::class => __DIR__ . '/Types/PrimitiveContainerTypeInterface.php',
        \OpenEMR\FHIR\FHIRVersion::class => __DIR__ . '/FHIRVersion.php',
        \OpenEMR\FHIR\Versions\VersionTypeMapInterface::class => __DIR__ . '/Versions/VersionTypeMapInterface.php',
        \OpenEMR\FHIR\Versions\VersionConfigInterface::class => __DIR__ . '/Versions/VersionConfigInterface.php',
        \OpenEMR\FHIR\Versions\VersionInterface::class => __DIR__ . '/Versions/VersionInterface.php',
        \OpenEMR\FHIR\Versions\VersionConfig::class => __DIR__ . '/Versions/VersionConfig.php',
        \OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait::class => __DIR__ . '/Encoding/XMLSerializationOptionsTrait.php',
        \OpenEMR\FHIR\Encoding\XMLWriter::class => __DIR__ . '/Encoding/XMLWriter.php',
        \OpenEMR\FHIR\Encoding\SerializeConfig::class => __DIR__ . '/Encoding/SerializeConfig.php',
        \OpenEMR\FHIR\Encoding\ResourceParser::class => __DIR__ . '/Encoding/ResourceParser.php',
        \OpenEMR\FHIR\Encoding\SerializeFormatEnum::class => __DIR__ . '/Encoding/SerializeFormatEnum.php',
        \OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait::class => __DIR__ . '/Encoding/JSONSerializationOptionsTrait.php',
        \OpenEMR\FHIR\Encoding\UnserializeConfig::class => __DIR__ . '/Encoding/UnserializeConfig.php',
        \OpenEMR\FHIR\Encoding\ValueXMLLocationEnum::class => __DIR__ . '/Encoding/ValueXMLLocationEnum.php',
        \OpenEMR\FHIR\Constants::class => __DIR__ . '/Constants.php',
        'OpenEMR\FHIR\Client\SortDirectionEnum' => __DIR__ . '/Client/SortDirectionEnum.php',
        'OpenEMR\FHIR\Client\Client' => __DIR__ . '/Client/Client.php',
        'OpenEMR\FHIR\Client\Response' => __DIR__ . '/Client/Response.php',
        'OpenEMR\FHIR\Client\Request' => __DIR__ . '/Client/Request.php',
        'OpenEMR\FHIR\Client\ClientErrorException' => __DIR__ . '/Client/ClientErrorException.php',
        'OpenEMR\FHIR\Client\UnexpectedResponseCodeException' => __DIR__ . '/Client/UnexpectedResponseCodeException.php',
        'OpenEMR\FHIR\Client\ClientInterface' => __DIR__ . '/Client/ClientInterface.php',
        'OpenEMR\FHIR\Client\Config' => __DIR__ . '/Client/Config.php',
        'OpenEMR\FHIR\Client\AbstractClientException' => __DIR__ . '/Client/AbstractClientException.php',
        'OpenEMR\FHIR\Client\HTTPMethodEnum' => __DIR__ . '/Client/HTTPMethodEnum.php',
        'OpenEMR\FHIR\Client\ResponseHeaders' => __DIR__ . '/Client/ResponseHeaders.php',
        \OpenEMR\FHIR\Validation\Validator::class => __DIR__ . '/Validation/Validator.php',
        \OpenEMR\FHIR\Validation\TypeValidationsTrait::class => __DIR__ . '/Validation/TypeValidationsTrait.php',
        \OpenEMR\FHIR\Validation\RuleInterface::class => __DIR__ . '/Validation/RuleInterface.php',
        \OpenEMR\FHIR\Validation\Rules\ValueOneOfRule::class => __DIR__ . '/Validation/Rules/ValueOneOfRule.php',
        \OpenEMR\FHIR\Validation\Rules\ValuePatternMatchRule::class => __DIR__ . '/Validation/Rules/ValuePatternMatchRule.php',
        \OpenEMR\FHIR\Validation\Rules\ValueMaxLengthRule::class => __DIR__ . '/Validation/Rules/ValueMaxLengthRule.php',
        \OpenEMR\FHIR\Validation\Rules\MaxOccursRule::class => __DIR__ . '/Validation/Rules/MaxOccursRule.php',
        \OpenEMR\FHIR\Validation\Rules\MinOccursRule::class => __DIR__ . '/Validation/Rules/MinOccursRule.php',
        \OpenEMR\FHIR\Validation\Rules\ValueMinLengthRule::class => __DIR__ . '/Validation/Rules/ValueMinLengthRule.php',
    ];

    private static bool $_registered = false;

    private static array $_versionRegistered = [
        0 => false,
    ];

    public static function register(): bool
    {
        if (!self::$_registered) {
            self::$_registered = spl_autoload_register(self::class . '::loadClass');
        }
        return self::$_registered;
    }

    public static function unregister(): bool
    {
        if (self::$_registered) {
            if (spl_autoload_unregister(self::class . '::loadClass')) {
                self::$_registered = false;
                return true;
            }
        }
        return false;
    }

    public static function loadClass(string $class): null|bool
    {
        if (isset(self::_CORE_CLASS_MAP[$class])) {
            return (bool)require self::_CORE_CLASS_MAP[$class];
        } else if (!str_starts_with($class, self::_ROOT_NAMESPACE)) {
            return null;
        } else if (str_starts_with($class, self::_VERSION_AUTOLOADER_MAP[0][0])) {
            if (self::$_versionRegistered[0]) {
                return null;
            }
            require self::_VERSION_AUTOLOADER_MAP[0][2];
            \OpenEMR\FHIR\Versions\R4\Autoloader::register();
            self::$_versionRegistered[0] = true;
            if ($class !== self::_VERSION_AUTOLOADER_MAP[0][1]) {
                return \OpenEMR\FHIR\Versions\R4\Autoloader::loadClass($class);
            } else {
                return true;
            }
        } else {
            return null;
        }
    }
}

Autoloader::register();
