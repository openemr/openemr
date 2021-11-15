<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use OpenApi\Util;

class ExpandInterfaces
{
    public function __invoke(Analysis $analysis)
    {
        $schemas = $analysis->getAnnotationsOfType(Schema::class);
        foreach ($schemas as $schema) {
            if ($schema->_context->is('class')) {
                $interfaces = $analysis->getInterfacesOfClass($schema->_context->fullyQualifiedName($schema->_context->class), true);
                $existing = [];
                foreach ($interfaces as $interface) {
                    $interfaceSchema = $analysis->getSchemaForSource($interface['context']->fullyQualifiedName($interface['interface']));
                    if ($interfaceSchema) {
                        $this->inheritInterface($schema, $interface, $interfaceSchema);
                    } else {
                        $this->mergeInterface($schema, $interface, $existing);
                    }
                }
            }
        }
    }

    protected function inheritInterface(Schema $schema, array $interface, Schema $interfaceSchema): void
    {
        if ($schema->allOf === Generator::UNDEFINED) {
            $schema->allOf = [];
        }
        $refPath = $interfaceSchema->schema !== Generator::UNDEFINED ? $interfaceSchema->schema : $interface['interface'];
        $schema->allOf[] = new Schema([
            '_context' => $interface['context']->_context,
            'ref' => Components::SCHEMA_REF.Util::refEncode($refPath),
        ]);
    }

    protected function mergeInterface(Schema $schema, array $interface, array &$existing): void
    {
        if (is_iterable($interface['context']->annotations)) {
            foreach ($interface['context']->annotations as $annotation) {
                if ($annotation instanceof Property && !in_array($annotation->_context->property, $existing)) {
                    $existing[] = $annotation->_context->property;
                    $schema->merge([$annotation], true);
                }
            }
        }

        foreach ($interface['methods'] as $method) {
            if (is_iterable($method->annotations)) {
                foreach ($method->annotations as $annotation) {
                    if ($annotation instanceof Property && !in_array($annotation->_context->property, $existing)) {
                        $existing[] = $annotation->_context->property;
                        $schema->merge([$annotation], true);
                    }
                }
            }
        }
    }
}
