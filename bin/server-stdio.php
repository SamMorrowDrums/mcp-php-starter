#!/usr/bin/env php
<?php

/**
 * MCP PHP Starter - stdio Transport
 *
 * This entrypoint runs the MCP server using stdio transport,
 * which is ideal for local development and CLI tool integration.
 *
 * Usage:
 *   php bin/server-stdio.php
 *   composer start:stdio
 *
 * @see https://modelcontextprotocol.io/docs/develop/transports#stdio
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;

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

## Recommended Workflows

1. **Testing Connection**: Call `hello` with your name to verify the server is responding
2. **Weather Demo**: Call `get_weather` with a location to see structured output
3. **Calculator**: Call `calculate` with numbers and an operation
INSTRUCTIONS;

try {
    $server = Server::builder()
        ->setServerInfo('mcp-php-starter', '1.0.0')
        ->setInstructions($instructions)
        ->setDiscovery(__DIR__ . '/..', ['src'])
        ->build();

    $transport = new StdioTransport();
    $server->run($transport);
} catch (Throwable $e) {
    fwrite(STDERR, "Fatal error: " . $e->getMessage() . "\n");
    exit(1);
}
