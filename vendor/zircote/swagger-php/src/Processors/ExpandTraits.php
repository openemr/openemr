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
use Traversable;

class ExpandTraits
{
    public function __invoke(Analysis $analysis)
    {
        $schemas = $analysis->getAnnotationsOfType(Schema::class);
        foreach ($schemas as $schema) {
            if ($schema->_context->is('class') || $schema->_context->is('trait')) {
                $source = $schema->_context->class ?: $schema->_context->trait;
                $traits = $analysis->getTraitsOfClass($schema->_context->fullyQualifiedName($source), true);
                $existing = [];
                foreach ($traits as $trait) {
                    $traitSchema = $analysis->getSchemaForSource($trait['context']->fullyQualifiedName($trait['trait']));
                    if ($traitSchema) {
                        $this->inheritTrait($schema, $trait, $traitSchema);
                    } else {
                        if ($schema->_context->is('class')) {
                            $this->mergeTrait($schema, $trait, $existing);
                        }
                    }
                }
            }
        }
    }

    protected function inheritTrait(Schema $schema, array $trait, Schema $traitSchema): void
    {
        $refPath = $traitSchema->schema !== Generator::UNDEFINED ? $traitSchema->schema : $trait['trait'];
        if ($schema->allOf === Generator::UNDEFINED) {
            $schema->allOf = [];
        }
        $schema->allOf[] = new Schema([
            '_context' => $trait['context']->_context,
            'ref' => Components::SCHEMA_REF.Util::refEncode($refPath),
        ]);
    }

    protected function mergeTrait(Schema $schema, array $trait, array &$existing): void
    {
        if (is_iterable($trait['context']->annotations)) {
            foreach ($trait['context']->annotations as $annotation) {
                if ($annotation instanceof Property && !in_array($annotation->_context->property, $existing)) {
                    $existing[] = $annotation->_context->property;
                    $schema->merge([$annotation], true);
                }
            }
        }

        foreach ($trait['properties'] as $method) {
            if (is_array($method->annotations) || $method->annotations instanceof Traversable) {
                foreach ($method->annotations as $annotation) {
                    if ($annotation instanceof Property && !in_array($annotation->_context->property, $existing)) {
                        $existing[] = $annotation->_context->property;
                        $schema->merge([$annotation], true);
                    }
                }
            }
        }

        foreach ($trait['methods'] as $method) {
            if (is_array($method->annotations) || $method->annotations instanceof Traversable) {
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
