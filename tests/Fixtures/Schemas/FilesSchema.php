<?php

namespace SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use SenSkysh\SwaggerProcessors\Attributes\GenerateSchema;

#[GenerateSchema]
class FilesSchema
{
    public function __construct(
        public string $company,
        public string $phone,
        public string $email,
        #[Property(property: 'files[]', type: 'array', items: new Items(type: 'string', format: 'binary'))]
        public array  $files = []
    )
    {
    }
}
