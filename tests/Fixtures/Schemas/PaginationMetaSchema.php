<?php

namespace SenSkysh\SwaggerProcessors\Tests\Fixtures\Schemas;

use SenSkysh\SwaggerProcessors\Attributes\GenerateSchema;

#[GenerateSchema]
class PaginationMetaSchema
{
    public int $perPage;

    public function __construct(
        int         $perPage,
        public int  $currentPage,
        public int  $lastPage,
        public int  $total,
        public ?int $from = null,
        public ?int $to = null,
    )
    {
        $this->perPage = $perPage;
    }
}
