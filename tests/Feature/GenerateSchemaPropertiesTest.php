<?php

namespace SenSkysh\SwaggerProcessors\Tests\Feature;


use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Generator;
use OpenApi\Processors\AugmentSchemas;
use OpenApi\Processors\MergeIntoComponents;
use OpenApi\Processors\MergeIntoOpenApi;
use SenSkysh\SwaggerProcessors\Processors\GenerateSchemaProperties;
use SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas\FilesSchema;
use SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas\PaginationMetaSchema;

class GenerateSchemaPropertiesTest extends TestCase
{
    public function test_generate(): void
    {
        $analysis = $this->analysisFromFixtures([PaginationMetaSchema::class]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $schema = $analysis->getSchemaForSource(PaginationMetaSchema::class);

        $this->assertSame(Generator::UNDEFINED, $schema->properties);

        $analysis->process(new GenerateSchemaProperties());


        $expectedProperties = [
            new Property(property: 'perPage', type: 'integer'),
            new Property(property: 'currentPage', type: 'integer'),
            new Property(property: 'lastPage', type: 'integer'),
            new Property(property: 'total', type: 'integer'),
            new Property(property: 'from', type: 'integer', nullable: true),
            new Property(property: 'to', type: 'integer', nullable: true),
        ];

        foreach ($schema->properties as $key => $property) {
            $this->assertJsonSerializableEquals($expectedProperties[$key], $property);
        }
    }

    public function test_generate_with_custom_property(): void
    {
        $analysis = $this->analysisFromFixtures([FilesSchema::class]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $schema = $analysis->getSchemaForSource(FilesSchema::class);

        $this->assertCount(1, $schema->properties);

        $analysis->process(new GenerateSchemaProperties());

        $this->assertCount(4, $schema->properties);

        $expectedProperties = [
            new Property(property: 'company', type: 'string'),
            new Property(property: 'phone', type: 'string'),
            new Property(property: 'email', type: 'string'),
            new Property(property: 'files[]', type: 'array', items: new Items(type: 'string', format: 'binary')),
        ];

        foreach ($schema->properties as $key => $property) {
            $this->assertJsonSerializableEquals($expectedProperties[$key], $property);
        }
    }
}
