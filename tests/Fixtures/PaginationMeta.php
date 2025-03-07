<?php

namespace SenSkysh\SwaggerProcessors\Tests\Fixtures;

use SenSkysh\SwaggerProcessors\Attributes\GenerateSchema;

#[GenerateSchema]
class PaginationMeta
{
    public function __construct(
        public int  $perPage,
        public int  $currentPage,
        public int  $lastPage,
        public int  $total,
        public ?int $from = null,
        public ?int $to = null,
    )
    {
    }
}
