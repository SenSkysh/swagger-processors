<?php

namespace SenSkysh\SwaggerProcessors\Tests\Feature;


use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\AugmentSchemas;
use OpenApi\Processors\MergeIntoComponents;
use OpenApi\Processors\MergeIntoOpenApi;
use SenSkysh\SwaggerProcessors\Processors\GenerateSchemaProperties;
use SenSkysh\SwaggerProcessors\Tests\Fixtures\PaginationMeta;

class GenerateSchemaPropertiesTest extends TestCase
{
    public function test_generate_schema(): void
    {
        $analysis = $this->analysisFromFixtures([PaginationMeta::class]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $schema = $analysis->openapi->components->schemas[0];

        $this->assertSame(Generator::UNDEFINED, $schema->properties);

        $analysis->process(new GenerateSchemaProperties());


        $expectedProperties = [
            [
                'property' => 'perPage',
                'type' => 'integer',
            ],
            [
                'property' => 'currentPage',
                'type' => 'integer',
            ],
            [
                'property' => 'lastPage',
                'type' => 'integer',
            ],
            [
                'property' => 'total',
                'type' => 'integer',
            ],
            [
                'property' => 'from',
                'type' => 'integer',
                'nullable' => true
            ],
            [
                'property' => 'to',
                'type' => 'integer',
                'nullable' => true
            ],
        ];

        foreach ($schema->properties as $key => $property){
            $this->assertName($property, $expectedProperties[$key]);
        }

        $this->assertTrue(true);
    }

    /**
     * @param class-string[] $fixtures
     * @return Analysis
     */
    public function analysisFromFixtures(array $fixtures): Analysis
    {
        $analysis = new Analysis(
            [],
            new Context([
                'version' => OpenApi::DEFAULT_VERSION,
            ])
        );

        $files = [];
        foreach ($fixtures as $fixture){
            $files[] = (new \ReflectionClass($fixture))->getFileName();
        }

        (new Generator())
            ->generate($files, $analysis, false);

        return $analysis;
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function assertName(Property $property, array $expectedValues): void
    {
        foreach ($expectedValues as $key => $val) {
            $this->assertSame($val, $property->$key, "Property $property->property -> $key expect $val");
        }
    }

}
