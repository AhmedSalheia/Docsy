<?php

namespace Ahmedsalheia\Docsy\Tests\Unit\Exporters;

use Ahmedsalheia\Docsy\Exporters\PostmanExporter;
use PHPUnit\Framework\TestCase;

class PostmanExporterTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testTransformVariablesSimple()
    {
        $variables = [
            'base_url' => 'https://api.test',
            'debug' => true,
        ];

        $result = (new \ReflectionClass(PostmanExporter::class))
            ->getMethod('transformVariables')
            ->invokeArgs(null, [$variables]);

        $this->assertEquals('base_url', $result[0]['key']);
        $this->assertEquals('https://api.test', $result[0]['value']);
        $this->assertEquals('string', $result[0]['type']);
        $this->assertEquals('debug', $result[1]['key']);
        $this->assertTrue($result[1]['value']);
        $this->assertEquals('boolean', $result[1]['type']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testTransformVariablesStructured()
    {
        $variables = [
            'api_key' => [
                'value' => 'abc123',
                'type' => 'secret',
                'description' => 'API key',
            ]
        ];

        $result = (new \ReflectionClass(PostmanExporter::class))
            ->getMethod('transformVariables')
            ->invokeArgs(null, [$variables]);

        $this->assertEquals('api_key', $result[0]['key']);
        $this->assertEquals('abc123', $result[0]['value']);
        $this->assertEquals('secret', $result[0]['type']);
        $this->assertEquals('API key', $result[0]['description']);
    }
}