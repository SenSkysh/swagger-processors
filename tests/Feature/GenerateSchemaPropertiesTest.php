<?php

namespace SenSkysh\SwaggerProcessors\Tests\Feature;


use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Attributes\Items;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\AugmentSchemas;
use OpenApi\Processors\MergeIntoComponents;
use OpenApi\Processors\MergeIntoOpenApi;
use SenSkysh\SwaggerProcessors\Processors\GenerateSchemaProperties;
use SenSkysh\SwaggerProcessors\Tests\Fixtures\FilesForm;
use SenSkysh\SwaggerProcessors\Tests\Fixtures\PaginationMeta;

class GenerateSchemaPropertiesTest extends TestCase
{
    public function test_generate(): void
    {
        $analysis = $this->analysisFromFixtures([PaginationMeta::class]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $schema = $analysis->getSchemaForSource(PaginationMeta::class);

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

        $this->assertSchemaProperties($schema, $expectedProperties);

        $this->assertTrue(true);
    }

    public function test_generate_with_custom_property(): void
    {
        $analysis = $this->analysisFromFixtures([FilesForm::class]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $schema = $analysis->getSchemaForSource(FilesForm::class);

        $this->assertCount(1, $schema->properties);

        $analysis->process(new GenerateSchemaProperties());

        $this->assertCount(4, $schema->properties);

        $expectedProperties = [
            [
                'property' => 'company',
                'type' => 'string',
            ],
            [
                'property' => 'phone',
                'type' => 'string',
            ],
            [
                'property' => 'email',
                'type' => 'string',
            ],
            [
                'property' => 'files[]',
                'type' => 'array',
                'items' => new Items(type: 'string', format: 'binary')
            ],
        ];

        $this->assertSchemaProperties($schema, $expectedProperties);

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
        foreach ($expectedValues as $key => $expected) {
            $actual = $property->$key;

            if ($expected instanceof \JsonSerializable){
                $expected = json_encode($expected);
            }
            if ($actual instanceof \JsonSerializable){
                $actual = json_encode($actual);
            }
            $this->assertSame($expected, $actual, "Property $property->property -> $key expect $expected");
        }
    }

    protected function assertSchemaProperties(Schema $schema, array $expectedValues): void
    {
        foreach ($schema->properties as $key => $property){
            $this->assertName($property, $expectedValues[$key]);
        }
    }

}
