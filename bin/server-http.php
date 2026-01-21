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
use McpPhpStarter\ServerFactory;

$port = getenv('PORT') ?: '3000';

echo "MCP PHP Starter running on http://localhost:{$port}\n";
echo "  MCP endpoint: http://localhost:{$port}/\n";
echo "\nPress Ctrl+C to exit\n";

try {
    // Note: The actual server configuration happens in public/index.php
    // This just starts PHP's built-in server for development
    
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
