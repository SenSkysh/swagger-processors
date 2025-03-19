<?php

namespace SenSkysh\SwaggerProcessors\Tests\Feature;


use OpenApi\Generator;
use OpenApi\Processors\AugmentSchemas;
use OpenApi\Processors\MergeIntoComponents;
use OpenApi\Processors\MergeIntoOpenApi;
use SenSkysh\SwaggerProcessors\Processors\EnsureRequiredProperties;
use SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas\RequiredSchema;

class EnsureRequiredPropertiesTest extends TestCase
{
    public function test_ensure(): void
    {
        $analysis = $this->analysisFromFixtures([RequiredSchema::class]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $schema = $analysis->getSchemaForSource(RequiredSchema::class);

        $this->assertSame(Generator::UNDEFINED, $schema->required);

        $analysis->process(new EnsureRequiredProperties());

        $this->assertNotEquals(Generator::UNDEFINED, $schema->required);
        $this->assertSame($schema->required, ['required1', 'required2']);
    }
}
