<?php

declare(strict_types=1);

namespace SenSkysh\SwaggerProcessors\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;

class EnsureRequiredProperties
{
    public function __invoke(Analysis $analysis): void
    {
        /**
         * @var Schema[] $schemas
         */
        $schemas = $analysis->getAnnotationsOfType(Schema::class);
        foreach ($schemas as $schema) {
            $this->ensureRequired($schema);
        }
    }

    private function ensureRequired(Schema $schema): void
    {
        if (!Generator::isDefault($schema->required) || Generator::isDefault($schema->properties)) {
            return;
        }
        $schema->required = [];
        foreach ($schema->properties as $property) {
            $isNullable = Generator::isDefault($property->nullable) ? false : $property->nullable;
            if (!$isNullable) {
                $schema->required[] = $property->property;
            }
        }
    }
}
