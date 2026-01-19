#!/usr/bin/env php
<?php

/**
 * MCP PHP Starter - HTTP Transport
 *
 * This entrypoint runs the MCP server using HTTP transport with SSE streams,
 * which is ideal for remote deployment and web-based clients.
 *
 * Usage:
 *   php bin/server-http.php
 *   composer start:http
 *
 * For production, use a web server (Apache/Nginx) with PHP-FPM.
 *
 * @see https://modelcontextprotocol.io/docs/develop/transports#streamable-http
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Mcp\Server;

// Server instructions for AI assistants
$instructions = <<<INSTRUCTIONS
# MCP PHP Starter Server

A demonstration MCP server showcasing PHP SDK capabilities.

## Available Tools

### Greeting & Demos
- **hello**: Simple greeting - use to test connectivity
- **get_weather**: Returns simulated weather data
- **long_task**: Demonstrates progress reporting

### Calculations
- **calculate**: Perform arithmetic operations (add, subtract, multiply, divide)

### Utility
- **echo**: Echo back the provided message

## Available Resources

- **info://about**: Server information
- **doc://example**: Example markdown document
- **config://settings**: Server configuration

## Available Prompts

- **greet**: Generates a personalized greeting
- **code_review**: Structured code review prompt
INSTRUCTIONS;

$port = getenv('PORT') ?: '3000';

echo "MCP PHP Starter running on http://localhost:{$port}\n";
echo "  MCP endpoint: http://localhost:{$port}/\n";
echo "\nPress Ctrl+C to exit\n";

try {
    $server = Server::builder()
        ->setServerInfo('mcp-php-starter', '1.0.0')
        ->setInstructions($instructions)
        ->setDiscovery(__DIR__ . '/..', ['src'])
        ->build();

    // For HTTP transport, use the built-in PHP server for development
    // In production, configure your web server to use the index.php file
    
    // Start PHP's built-in server
    $command = sprintf(
        'php -S localhost:%s -t %s %s',
        $port,
        escapeshellarg(__DIR__ . '/../public'),
        escapeshellarg(__DIR__ . '/../public/index.php')
    );
    
    passthru($command);
} catch (Throwable $e) {
    fwrite(STDERR, "Fatal error: " . $e->getMessage() . "\n");
    exit(1);
}
