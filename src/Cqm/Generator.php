<?php

namespace OpenEMR\Cqm;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\PropertyTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\PropertyGenerator;

class Generator
{
    protected static $type_lookup = [
        'System.DateTime' => 'BaseTypes\\DateTime',
        'System.Date' => 'BaseTypes\\Date',
        'System.Integer' => 'BaseTypes\\Integer',
        'System.Quantity' => 'BaseTypes\\Quantity',
        'System.Code' => 'BaseTypes\\Code',
        'QDM.Identifier' => 'BaseTypes\\Identifier',
        'System.Any' => 'BaseTypes\\Any',
        'interval<System.DateTime>' => 'BaseTypes\\Interval',
        'interval<System.Quantity>' => 'BaseTypes\\Interval',
        'list<QDM.Component>' => 'array',
        'System.String' => 'string',
        'list<QDM.Id>' => 'array',
        'list<QDM.ResultComponent>' => 'array',
        'list<QDM.FacilityLocation>' => 'array',
        'list<QDM.DiagnosisComponent>' => 'array',
        'list<System.String>' => 'array',
        'list<System.Code>' => 'array',
        'System.Decimal' => 'BaseTypes\\Float',
        'System.Time' => 'BaseTypes\\Time',
        'System.Concept' => 'BaseTypes\\Any'
    ];

    protected static $default_value_lookup = [
        'list<QDM.Component>' => [],
        'list<QDM.Id>' => [],
        'list<QDM.ResultComponent>' => [],
        'list<QDM.FacilityLocation>' => [],
        'list<QDM.DiagnosisComponent>' => [],
        'list<System.String>' => [],
        'list<System.Code>' => [],
        'System.String' => ''
    ];

    public function execute()
    {
        $datatypes = [];
        $extends = [];
        $hqmfOid_to_datatype_map = [];
        $oids_file = __DIR__ . '/oids_qdm_5.5.json';
        $modelinfo_file = __DIR__ . '/qdm-modelinfo-5.5.xml';
        $modelinfo = simplexml_load_string(file_get_contents($modelinfo_file));
        $oids = json_decode(file_get_contents($oids_file), true);

        // Grab QDM version as defined in the modelinfo file
        $qdm_version = (string)$modelinfo->xpath('//ns4:modelInfo')[0]->attributes()->version;

        // Loop through each typeInfo node (each of these is a QDM datatype)
        foreach ($modelinfo->xpath('//ns4:typeInfo') as $type) {
            // Grab the name of this QDM datatype
            $name_parts = explode('.', (string)$type->attributes()->name);
            $datatype_name = $name_parts[count($name_parts) - 1];

            // Reject irrelevant datatypes
            if (
                strpos($datatype_name, 'Negative') ||
                strpos($datatype_name, 'Positive') ||
                strpos($datatype_name, 'QDMBaseType')
            ) {
                continue;
            }

            // Grab the QDM attributes for this datatype
            $attributes = [];
            foreach ($type->xpath('./ns4:element') as $attribute) {
                // Grab the name of this QDM datatype attribute
                $attribute_name = (string)$attribute->attributes()->name;

                // Grab the type of this QDM datatype attribute
                $attribute_type = (string)$attribute->attributes()->type ? (string)$attribute->attributes()->type : 'System.Any';

                if (empty($attribute_name) || empty($attribute_type)) {
                    continue;
                }

                if (isset(self::$default_value_lookup[$attribute_type])) {
                    $default_value = self::$default_value_lookup[$attribute_type];
                } else {
                    $default_value = null;
                }

                // Store name and type
                $attributes[] = [
                    'name' => $attribute_name,
                    'type' => $attribute_type,
                    'default' => $default_value
                ];
            }

            // Add the label as qdmTitle
            $qdm_title = (string)$type->attributes()->label;
            if (empty($qdm_title)) {
                // If there's no label, check if there is a "positive" profile
                $positive_profile_element = $modelinfo->xpath("/ns4:modelInfo/ns4:typeInfo[@xsi:type='ns4:ProfileInfo'][@identifier='Positive$datatype_name']")[0];
                if ($positive_profile_element !== null) {
                    $positive_profile = $positive_profile_element->attributes();
                    if (!empty((string)$positive_profile->label)) {
                        $qdm_title = (string)$positive_profile->label;
                    }
                }
            }

            if (!empty($qdm_title)) {
                $attributes[] = [
                    'name' => 'qdmTitle',
                    'type' => 'System.String',
                    'default' => $qdm_title
                ];
            }

            $datatype_base_type = (string)$type->attributes()->baseType;
            if (!empty($datatype_base_type)) {
                if (isset(self::$type_lookup[$datatype_base_type])) {
                    $datatype_base_name = self::$type_lookup[$datatype_base_type];
                } else {
                    $base_name_parts = explode('.', (string)$type->attributes()->baseType);
                    $datatype_base_name = $base_name_parts[count($base_name_parts) - 1];
                }
                $extends[$datatype_name] = $datatype_base_name;
            }

            // Add the extra info that is manually maintained in the "oids" file
            $extra_info = null;
            if (isset($oids[$this->underscore($datatype_name)])) {
                $extra_info = $oids[$this->underscore($datatype_name)];
            }
            if ($extra_info !== null) {
                if (isset($extra_info['hqmf_oid'])) {
                    $attributes[] = [
                        'name' => 'hqmfOid',
                        'type' => 'System.String',
                        'default' => $extra_info['hqmf_oid']
                    ];
                }
                if (isset($extra_info['qrda_oid'])) {
                    $attributes[] = [
                        'name' => 'qrdaOid',
                        'type' => 'System.String',
                        'default' => $extra_info['qrda_oid']
                    ];
                }
                if (isset($extra_info['qdm_category'])) {
                    $attributes[] = [
                        'name' => 'qdmCategory',
                        'type' => 'System.String',
                        'default' => $extra_info['qdm_category']
                    ];
                }
                if (isset($extra_info['qdm_status'])) {
                    $attributes[] = [
                        'name' => 'qdmStatus',
                        'type' => 'System.String',
                        'default' => $extra_info['qdm_status']
                    ];
                }
                if (!empty($extra_info['qrda_oid'])) {
                    $hqmfOid_to_datatype_map[$extra_info['qrda_oid']] = $datatype_name;
                }
            }

            // Add the qdmVersion attribute unless the base type is one that will provide it
            if (!in_array((string)$type->attributes()->baseType, ['QDM.QDMBaseType', 'QDM.Entity', 'QDM.Component'])) {
                $attributes[] = [
                    'name' => 'qdmVersion',
                    'type' => 'System.String',
                    'default' => $qdm_version,
                ];
            }

            $datatypes[$datatype_name] = $attributes;
        }

        // Generate PHP models
        $base_module = "OpenEMR\\Cqm\\Qdm";
        foreach ($datatypes as $datatype => $attributes) {
            $class = new ClassGenerator();
            $docblock = DocBlockGenerator::fromArray([
                'shortDescription' => "{$base_module}\\{$datatype}",
                'longDescription'  => 'This is a class generated with Laminas\Code\Generator.',
                'tags'             => [
                    [
                        'name'        => 'QDM Version',
                        'description' => $qdm_version,
                    ],
                    [
                        'name'        => 'author',
                        'description' => 'Ken Chapple <ken@mi-squared.com>',
                    ],
                    [
                        'name'        => 'license',
                        'description' => 'https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3',
                    ],
                ],
            ]);

            $class->setName($datatype)
                ->setNamespaceName($base_module)
                ->setDocblock($docblock);

            if (isset($extends[$datatype])) {
                $class->setExtendedClass($base_module . '\\' . $extends[$datatype]);
            }

            // Add each property along with a docblock that specifies the type
            foreach ($attributes as $attribute) {
                $property = new PropertyGenerator();
                $property->setDocBlock(DocBlockGenerator::fromArray([
                    'shortDescription' => '',
                    'longDescription'  => '',
                    'tags'             => [
                        new PropertyTag(
                            $attribute['name'],
                            [self::$type_lookup[$attribute['type']]]
                        )
                    ]
                ]));
                $property->setName($attribute['name']);
                if (isset($attribute['default'])) {
                    $property->setDefaultValue($attribute['default']);
                }
                $class->addPropertyFromGenerator($property);
            }

            // For cqm-execution, each model must have the _type field set in this format with QDM:: prefix
            $class->addProperty('_type', "QDM::$datatype");

            // Special cases
            if ($datatype == 'Patient') {
                $class->addTrait('Traits\\PatientExtension');
            }

            if ($datatype == 'QDMBaseType') {
                $class->setExtendedClass('\\OpenEMR\\Cqm\\Qdm\\BaseTypes\\DataElement');
            }

            $file = FileGenerator::fromArray([
                'classes' => [$class]
            ]);
            $code = $file->generate();
            $qdm_dir = __DIR__ . DIRECTORY_SEPARATOR . 'Qdm';
            if (!file_exists($qdm_dir)) {
                mkdir($qdm_dir);
            }
            $filename = $qdm_dir . DIRECTORY_SEPARATOR . $datatype . '.php';
            if (false === file_put_contents($filename, $code)) {
                error_log("Error writing to QDM Model file: `$filename`");
            }
        }

        file_put_contents(__DIR__ . '/hqmfOid_to_datatype_map.json', json_encode($hqmfOid_to_datatype_map));
    }

    public function underscore($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}
