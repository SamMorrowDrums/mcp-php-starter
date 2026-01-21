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
use McpPhpStarter\ServerFactory;

try {
    $server = ServerFactory::configureBuilder(
        Server::builder()
            ->setServerInfo('mcp-php-starter', '1.0.0')
            ->setInstructions(ServerFactory::getInstructions())
    )->build();

    $transport = new StdioTransport();
    $server->run($transport);
} catch (Throwable $e) {
    fwrite(STDERR, 'Fatal error: ' . $e->getMessage() . "\n");
    exit(1);
}
