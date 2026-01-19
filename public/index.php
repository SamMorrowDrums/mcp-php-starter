<?php

/**
 * MCP PHP Starter - HTTP Entrypoint
 *
 * This file handles HTTP requests for the MCP server.
 * It's designed to work with PHP's built-in server or production web servers.
 *
 * @see https://modelcontextprotocol.io/docs/develop/transports#streamable-http
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Mcp\Server;
use Mcp\Server\Transport\StreamableHttpTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

// Server instructions
$instructions = <<<INSTRUCTIONS
# MCP PHP Starter Server

A demonstration MCP server showcasing PHP SDK capabilities.

## Available Tools
- **hello**: Simple greeting
- **get_weather**: Returns simulated weather data
- **long_task**: Demonstrates progress reporting
- **calculate**: Perform arithmetic operations
- **echo**: Echo back the provided message

## Available Resources
- **info://about**: Server information
- **doc://example**: Example markdown document
- **config://settings**: Server configuration

## Available Prompts
- **greet**: Generates a personalized greeting
- **code_review**: Structured code review prompt
INSTRUCTIONS;

// Handle health check
if ($_SERVER['REQUEST_URI'] === '/health' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'server' => 'mcp-php-starter',
        'version' => '1.0.0',
    ]);
    exit;
}

try {
    $psr17Factory = new Psr17Factory();
    
    $creator = new ServerRequestCreator(
        $psr17Factory,
        $psr17Factory,
        $psr17Factory,
        $psr17Factory
    );
    
    $request = $creator->fromGlobals();

    $server = Server::builder()
        ->setServerInfo('mcp-php-starter', '1.0.0')
        ->setInstructions($instructions)
        ->setDiscovery(__DIR__ . '/..', ['src'])
        ->build();

    $transport = new StreamableHttpTransport(
        $request,
        $psr17Factory,
        $psr17Factory
    );

    $response = $server->run($transport);

    // Send the PSR-7 response
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header("{$name}: {$value}", false);
        }
    }
    echo $response->getBody();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage(),
    ]);
}
