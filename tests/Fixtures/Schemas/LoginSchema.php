<?php

namespace SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    required: ['login', 'password'],
    properties: [
        new Property(property: 'login', type: 'string'),
        new Property(property: 'password', type: 'string'),
        new Property(property: 'device', type: 'string', nullable: true),
    ]
)]
class LoginSchema
{
    public function __construct(
        public string  $login,
        public string  $password,
        public ?string $device,
    )
    {
    }
}
