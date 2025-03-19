<?php

namespace SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    properties: [
        new Property(property: 'required1', type: 'string'),
        new Property(property: 'required2', type: 'string'),
        new Property(property: 'nonRequired', type: 'string', nullable: true),
    ]
)]
class RequiredSchema
{
    public function __construct(
        public string  $required1,
        public string  $required2,
        public ?string $nonRequired,
    )
    {
    }
}
