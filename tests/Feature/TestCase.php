<?php

namespace SenSkysh\SwaggerProcessors\Tests\Feature;

use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use OpenApi\Generator;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param class-string[] $fixtures
     * @return Analysis
     */
    protected function analysisFromFixtures(array $fixtures): Analysis
    {
        $analysis = new Analysis(
            [],
            new Context([
                'version' => OpenApi::DEFAULT_VERSION,
            ])
        );

        $files = [];
        foreach ($fixtures as $fixture) {
            $files[] = (new \ReflectionClass($fixture))->getFileName();
        }

        (new Generator())
            ->generate($files, $analysis, false);

        return $analysis;
    }

    protected function assertJsonSerializableEquals(object $expected, object $actual, string $message = ''): void
    {
        if ($expected instanceof \JsonSerializable) {
            $expected = json_encode($expected);
        }
        if ($actual instanceof \JsonSerializable) {
            $actual = json_encode($actual);
        }
        $this->assertSame($expected, $actual, $message);
    }
}
