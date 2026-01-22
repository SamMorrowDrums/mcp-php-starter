#!/usr/bin/env php
<?php

/**
 * Post-install script to patch MCP SDK files.
 * This script applies necessary patches to the vendor directory after composer install.
 */

declare(strict_types=1);

$patches = [
    [
        'file' => __DIR__ . '/../vendor/mcp/sdk/src/Schema/Resource.php',
        'name' => 'Resource.php',
        'replacements' => [
            [
                'search' => "    /**\n     * Resource name pattern regex - must contain only alphanumeric characters, underscores, and hyphens.\n     */\n    private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';",
                'replace' => "    /**\n     * Resource name pattern regex - must contain only alphanumeric characters, underscores, hyphens, and spaces.\n     */\n    private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_ -]+$/';",
            ],
            [
                'search' => "            throw new InvalidArgumentException('Invalid resource name: must contain only alphanumeric characters, underscores, and hyphens.');",
                'replace' => "            throw new InvalidArgumentException('Invalid resource name: must contain only alphanumeric characters, underscores, hyphens, and spaces.');",
            ],
        ],
    ],
    [
        'file' => __DIR__ . '/../vendor/mcp/sdk/src/Schema/ResourceTemplate.php',
        'name' => 'ResourceTemplate.php',
        'replacements' => [
            [
                'search' => "    /**\n     * Resource name pattern regex - must contain only alphanumeric characters, underscores, and hyphens.\n     */\n    private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';",
                'replace' => "    /**\n     * Resource name pattern regex - must contain only alphanumeric characters, underscores, hyphens, and spaces.\n     */\n    private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_ -]+$/';",
            ],
            [
                'search' => "            throw new InvalidArgumentException('Invalid resource name: must contain only alphanumeric characters, underscores, and hyphens.');",
                'replace' => "            throw new InvalidArgumentException('Invalid resource name: must contain only alphanumeric characters, underscores, hyphens, and spaces.');",
            ],
        ],
    ],
];

$allSuccess = true;

foreach ($patches as $patch) {
    if (!file_exists($patch['file'])) {
        echo "Warning: File not found: {$patch['name']}\n";
        $allSuccess = false;
        continue;
    }

    $content = file_get_contents($patch['file']);
    $originalContent = $content;

    foreach ($patch['replacements'] as $replacement) {
        $count = 0;
        $content = str_replace($replacement['search'], $replacement['replace'], $content, $count);

        if ($count === 0) {
            // Check if already patched
            if (strpos($content, $replacement['replace']) === false) {
                echo "Warning: Could not apply patch to {$patch['name']}\n";
                $allSuccess = false;
            }
        }
    }

    if ($content !== $originalContent) {
        file_put_contents($patch['file'], $content);
        echo "✓ Patched {$patch['name']}\n";
    } else {
        echo "✓ {$patch['name']} already patched\n";
    }
}

if ($allSuccess) {
    echo "\nAll patches applied successfully!\n";
    exit(0);
} else {
    echo "\nSome patches failed to apply. Please check the warnings above.\n";
    exit(1);
}
