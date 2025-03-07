<?php

declare(strict_types=1);

namespace SenSkysh\SwaggerProcessors\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Property;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApi\Processors\AugmentProperties;
use SenSkysh\SwaggerProcessors\Attributes\GenerateSchema;

class GenerateSchemaProperties
{
    public function __invoke(Analysis $analysis): void
    {
        /** @var GenerateSchema[] $schemas */
        $schemas = $analysis->getAnnotationsOfType(GenerateSchema::class);
        foreach ($schemas as $schema) {
            $this->fillSchema($analysis, $schema);
        }
        $augmentProperties = new AugmentProperties();
        $augmentProperties($analysis);
    }

    private function fillSchema(Analysis $analysis, GenerateSchema $schema): void
    {
        $properties = $this->addProperties($analysis, $schema);
        $analysis->addAnnotations($properties, $schema->_context);
    }


    /**
     * @param Analysis $analysis
     * @param Schema $schema
     * @return array<string, Property>
     * @throws \ReflectionException
     */
    private function addProperties(Analysis $analysis, Schema $schema): array
    {
        $classFqn = $schema->_context->fullyQualifiedName($schema->_context->class);
        $rc = new \ReflectionClass($classFqn);

        $properties = [];

        $classProperties = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($classProperties as $classProperty) {
            $propertyContext = $analysis->classes[$classFqn]['properties'][$classProperty->getName()] ?? null;
            if (!$propertyContext) {
                continue;
            }

            $schemaProperty = new Property([]);
            $schemaProperty->_context = $propertyContext;
            $properties[$classProperty->getName()] = $schemaProperty;
        }


        $schema->properties = Generator::isDefault($schema->properties) ? [] : $schema->properties;
        $schema->required = Generator::isDefault($schema->required) ? [] : $schema->required;

        foreach ($classProperties as $classProperty) {
            $alreadyDeclared = (bool)$this->arrayFirst($schema->properties, function (Property $value) use ($classProperty) {
                return $value->property === $classProperty->getName();
            });
            if ($alreadyDeclared) {
                continue;
            }

            $schema->properties[] = $properties[$classProperty->getName()];
            if (!$this->propertyHasDefault($classProperty)) {
                $schema->required[] = $classProperty->getName();
            }
        }


        return $schema->properties;
    }

    private function propertyHasDefault(\ReflectionProperty $property)
    {
        if ($property->isPromoted()) {
            $parameters = $property->getDeclaringClass()->getConstructor()->getParameters();
            $parameter = $this->arrayFirst($parameters, fn(\ReflectionParameter $p) => $p->getName() == $property->getName());
            return $parameter->isOptional();
        }
        return $property->isDefault();
    }

    /**
     * @template T
     * @param T[] $array
     * @param callable(T $value, array-key $key): bool $callback
     * @return T|null
     */
    private function arrayFirst(array $array, callable $callback): mixed
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return null;
    }
}
