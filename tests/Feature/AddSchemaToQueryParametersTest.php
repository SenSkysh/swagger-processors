<?php

namespace SenSkysh\SwaggerProcessors\Tests\Feature;


use OpenApi\Attributes\Get;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\AugmentSchemas;
use OpenApi\Processors\MergeIntoComponents;
use OpenApi\Processors\MergeIntoOpenApi;
use SenSkysh\SwaggerProcessors\Processors\AddSchemaToQueryParameters;
use SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas\EmptySchema;
use SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas\LoginSchema;

class AddSchemaToQueryParametersTest extends TestCase
{
    public function test_add_schema(): void
    {
        $analysis = $this->analysisFromFixtures([LoginSchema::class]);
        $operation = new Get(
            path: "/api/v1/loginQuery",
            x: [
                AddSchemaToQueryParameters::REF => [LoginSchema::class]
            ]
        );
        $analysis->addAnnotation($operation, new Context());

        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $this->assertSame(Generator::UNDEFINED, $operation->parameters);


        $analysis->process(new AddSchemaToQueryParameters());


        $this->assertIsArray($operation->parameters);
        $expected = [
            new Parameter(name: 'login', in: 'query', required: true, schema: new Schema(type: 'string', nullable: false)),
            new Parameter(name: 'password', in: 'query', required: true, schema: new Schema(type: 'string', nullable: false)),
            new Parameter(name: 'device', in: 'query', required: false, schema: new Schema(type: 'string', nullable: true)),
        ];

        foreach ($operation->parameters as $key => $parameter) {
            $this->assertJsonSerializableEquals($expected[$key], $parameter);
        }
    }

    public function test_no_schema(): void
    {
        $analysis = $this->analysisFromFixtures([LoginSchema::class]);
        $operation = new Get(
            path: "/api/v1/loginQuery",
            x: [
                AddSchemaToQueryParameters::REF => ['noSchema']
            ]
        );
        $analysis->addAnnotation($operation, new Context());

        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $this->assertSame(Generator::UNDEFINED, $operation->parameters);

        $this->expectException(\InvalidArgumentException::class);

        $analysis->process(new AddSchemaToQueryParameters());
    }

    public function test_wrong_schema(): void
    {
        $analysis = $this->analysisFromFixtures([LoginSchema::class]);
        $operation = new Get(
            path: "/api/v1/loginQuery",
            x: [
                AddSchemaToQueryParameters::REF => [new Parameter()]
            ]
        );
        $analysis->addAnnotation($operation, new Context());

        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $this->assertSame(Generator::UNDEFINED, $operation->parameters);

        $this->expectException(\InvalidArgumentException::class);

        $analysis->process(new AddSchemaToQueryParameters());
    }

    public function test_empty_schema(): void
    {
        $analysis = $this->analysisFromFixtures([EmptySchema::class]);
        $operation = new Get(
            path: "/api/v1/loginQuery",
            x: [
                AddSchemaToQueryParameters::REF => [EmptySchema::class]
            ]
        );
        $analysis->addAnnotation($operation, new Context());

        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new AugmentSchemas(),
        ]);

        $this->assertSame(Generator::UNDEFINED, $operation->parameters);

        $analysis->process(new AddSchemaToQueryParameters());

        $this->assertSame(Generator::UNDEFINED, $operation->parameters);
    }
}
