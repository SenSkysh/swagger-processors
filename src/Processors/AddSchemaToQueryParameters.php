<?php

declare(strict_types=1);

namespace SenSkysh\SwaggerProcessors\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

class AddSchemaToQueryParameters
{
    public const REF = 'query-args-$ref';

    public function __invoke(Analysis $analysis): void
    {
        /** @var Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(Operation::class);

        foreach ($operations as $operation) {
            if (!Generator::isDefault($operation->x) && array_key_exists(self::REF, $operation->x)) {
                $this->addSchemas($operation, $analysis);
            }
        }
    }

    private function addSchemas(Operation $operation, Analysis $analysis): void
    {
        $schemas = $operation->x[self::REF];
        $schemas = is_array($schemas) ? $schemas : [$schemas];

        foreach ($schemas as $schemaSource) {

            if (!is_string($schemaSource)) {
                throw new \InvalidArgumentException('Value of `x.' . self::REF . '` must be a string');
            }

            $schema = $analysis->getSchemaForSource($schemaSource);
            if (!$schema instanceof Schema) {
                throw new \InvalidArgumentException('Value of `x.' . self::REF . "` contains reference to unknown schema: `{$operation->x[self::REF]}`");
            }

            $this->addQueryParameters($operation, $schema);
        }

        $this->cleanUp($operation);
    }

    private function addQueryParameters(Operation $operation, Schema $schema): void
    {
        if (Generator::isDefault($schema->properties) || !$schema->properties) {
            return;
        }

        $operation->parameters = Generator::isDefault($operation->parameters) ? [] : $operation->parameters;

        foreach ($schema->properties as $property) {
            $propertySchema = new Schema(
                type: Generator::isDefault($property->format) ? $property->type : $property->format,
            );
            $propertySchema->nullable = Generator::isDefault($property->nullable) ? false : $property->nullable;
            $propertySchema->_context = $operation->_context;
            $propertySchema->items = $property->items;

            $parameter = new Parameter(
                name: $propertySchema->type === 'array' ? $property->property . '[]' : $property->property,
                description: Generator::isDefault($property->description) ? null : $property->description,
                in: 'query',
                required: !Generator::isDefault($schema->required) && in_array($property->property, $schema->required),
                schema: $propertySchema,
                example: $property->example,
            );
            $parameter->_context = $operation->_context;

            $operation->parameters[] = $parameter;
        }
    }

    private function cleanUp(Operation $operation): void
    {
        unset($operation->x[self::REF]);
        if (!$operation->x) {
            $operation->x = Generator::UNDEFINED;
        }
    }
}
