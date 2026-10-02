<?php

declare(strict_types=1);

namespace McpPhpStarter\Tests;

use Mcp\Schema\ResourceDefinition;
use Mcp\Schema\ResourceTemplate;
use Mcp\Server;
use Mcp\Server\Transport\StreamableHttpTransport;
use McpPhpStarter\ServerFactory;
use McpPhpStarter\StdioTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPUnit\Framework\TestCase;

final class ServerTest extends TestCase
{
    public function testResourceNamesSupportSpacesWithoutVendorPatches(): void
    {
        $resource = new ResourceDefinition('about://server', 'Server Info');
        $template = new ResourceTemplate('greeting://{name}', 'Personalized Greeting');

        self::assertSame('Server Info', $resource->jsonSerialize()['name']);
        self::assertSame('Personalized Greeting', $template->jsonSerialize()['name']);
    }

    public function testStdioProtocolAndRegisteredCapabilities(): void
    {
        $requests = [
            $this->initializeRequest(),
            ['jsonrpc' => '2.0', 'method' => 'notifications/initialized'],
            ['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list'],
            ['jsonrpc' => '2.0', 'id' => 3, 'method' => 'resources/list'],
            ['jsonrpc' => '2.0', 'id' => 4, 'method' => 'resources/templates/list'],
            ['jsonrpc' => '2.0', 'id' => 5, 'method' => 'prompts/list'],
            ['jsonrpc' => '2.0', 'id' => 6, 'method' => 'tools/call', 'params' => [
                'name' => 'hello', 'arguments' => ['name' => 'Ada'],
            ]],
            ['jsonrpc' => '2.0', 'id' => 7, 'method' => 'resources/read', 'params' => [
                'uri' => 'greeting://Ada',
            ]],
            ['jsonrpc' => '2.0', 'id' => 8, 'method' => 'prompts/get', 'params' => [
                'name' => 'greet', 'arguments' => ['name' => 'Ada', 'style' => 'formal'],
            ]],
            ['jsonrpc' => '2.0', 'id' => 9, 'method' => 'prompts/get', 'params' => [
                'name' => 'code_review', 'arguments' => ['code' => 'echo 1;'],
            ]],
        ];
        $input = fopen('php://temp', 'w+');
        $outputPath = tempnam(sys_get_temp_dir(), 'mcp-test-');
        self::assertNotFalse($outputPath);
        $output = fopen($outputPath, 'w+');
        self::assertIsResource($input);
        self::assertIsResource($output);

        try {
            foreach ($requests as $request) {
                fwrite($input, json_encode($request, JSON_THROW_ON_ERROR) . "\n");
            }
            rewind($input);

            self::assertSame(0, $this->createServer()->run(new StdioTransport($input, $output)));
            $contents = file_get_contents($outputPath);
            self::assertNotFalse($contents);
            $responses = [];
            foreach (explode("\n", trim($contents)) as $line) {
                $response = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                self::assertArrayNotHasKey('error', $response, $line);
                if (isset($response['id'])) {
                    $responses[$response['id']] = $response['result'];
                }
            }

            self::assertCount(9, $responses);
            self::assertSame('mcp-php-starter', $responses[1]['serverInfo']['name']);
            self::assertSame(
                ['hello', 'get_weather', 'long_task', 'load_bonus_tool', 'ask_llm', 'confirm_action', 'get_feedback'],
                array_column($responses[2]['tools'], 'name')
            );
            self::assertSame(['Server Info', 'Example Document'], array_column($responses[3]['resources'], 'name'));
            self::assertSame(
                ['Personalized Greeting', 'Item Data'],
                array_column($responses[4]['resourceTemplates'], 'name')
            );
            self::assertSame(['greet', 'code_review'], array_column($responses[5]['prompts'], 'name'));
            self::assertSame('Hello, Ada! Welcome to MCP.', $responses[6]['content'][0]['text']);
            self::assertSame(
                'Hello, Ada! This is a personalized greeting resource.',
                $responses[7]['contents'][0]['text']
            );
            self::assertSame(
                'Please compose a formal, professional greeting for Ada.',
                $responses[8]['messages'][0]['content']['text']
            );
            self::assertStringContainsString('echo 1;', $responses[9]['messages'][0]['content']['text']);
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }
            unlink($outputPath);
        }
    }

    public function testStdioRejectsServerDiscoverWithRequestIdBeforeInitialize(): void
    {
        $input = fopen('php://temp', 'w+');
        $outputPath = tempnam(sys_get_temp_dir(), 'mcp-test-');
        self::assertNotFalse($outputPath);
        $output = fopen($outputPath, 'w+');
        self::assertIsResource($input);
        self::assertIsResource($output);

        try {
            fwrite($input, json_encode(['jsonrpc' => '2.0', 'id' => 'probe', 'method' => 'server/discover'], JSON_THROW_ON_ERROR) . "\n");
            fwrite($input, json_encode($this->initializeRequest(), JSON_THROW_ON_ERROR) . "\n");
            rewind($input);

            self::assertSame(0, $this->createServer()->run(new StdioTransport($input, $output)));
            $contents = file_get_contents($outputPath);
            self::assertNotFalse($contents);
            $lines = explode("\n", trim($contents));
            self::assertCount(2, $lines);

            $discover = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
            self::assertSame('probe', $discover['id']);
            self::assertSame(-32601, $discover['error']['code']);

            $initialize = json_decode($lines[1], true, 512, JSON_THROW_ON_ERROR);
            self::assertSame(1, $initialize['id']);
            self::assertSame('mcp-php-starter', $initialize['result']['serverInfo']['name']);
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }
            unlink($outputPath);
        }
    }

    public function testHttpTransportInitializes(): void
    {
        $factory = new Psr17Factory();
        $creator = new ServerRequestCreator($factory, $factory, $factory, $factory);
        $request = $creator->fromArrays(
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/', 'SERVER_NAME' => 'localhost', 'SERVER_PORT' => '3000'],
            ['Host' => 'localhost:3000', 'Content-Type' => 'application/json', 'Accept' => 'application/json, text/event-stream'],
            body: json_encode($this->initializeRequest(), JSON_THROW_ON_ERROR)
        );

        $response = $this->createServer()->run(new StreamableHttpTransport($request, $factory, $factory));

        self::assertSame(200, $response->getStatusCode());
        self::assertNotSame('', $response->getHeaderLine('Mcp-Session-Id'));
        self::assertStringContainsString('"mcp-php-starter"', (string) $response->getBody());
        self::assertStringNotContainsString('"error"', (string) $response->getBody());
    }

    private function createServer(): Server
    {
        return ServerFactory::configureBuilder(
            Server::builder()
                ->setServerInfo('mcp-php-starter', '1.0.0')
                ->setInstructions(ServerFactory::getInstructions())
        )->build();
    }

    /**
     * @return array<string, mixed>
     */
    private function initializeRequest(): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-11-25',
                'capabilities' => (object) [],
                'clientInfo' => ['name' => 'dependency-regression-test', 'version' => '1.0.0'],
            ],
        ];
    }
}
