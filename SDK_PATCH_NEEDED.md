# PHP SDK Patch - Automatically Applied

## Issue

The PHP MCP SDK (v0.3.0) has overly restrictive validation for resource and resource template names that only allows alphanumeric characters, underscores, and hyphens. This prevents using spaces in resource names, which is required by the [Canonical MCP Interface](https://github.com/SamMorrowDrums/mcp-starters/blob/main/CANONICAL_INTERFACE.md).

## Solution

This repository includes an automatic patching script (`bin/patch-sdk.php`) that is run automatically after `composer install` and `composer update`. The script patches the following SDK files:

### 1. `vendor/mcp/sdk/src/Schema/Resource.php`

Changes the validation pattern to allow spaces in resource names:
- Pattern: `/^[a-zA-Z0-9_-]+$/` → `/^[a-zA-Z0-9_ -]+$/`
- Error message updated to reflect spaces are allowed

### 2. `vendor/mcp/sdk/src/Schema/ResourceTemplate.php`

Changes the validation pattern to allow spaces in resource template names:
- Pattern: `/^[a-zA-Z0-9_-]+$/` → `/^[a-zA-Z0-9_ -]+$/`
- Error message updated to reflect spaces are allowed

## Manual Patching

If you need to manually apply the patches, run:

```bash
php bin/patch-sdk.php
```

## Upstream Fix Needed

This issue should be reported to the PHP SDK maintainers at:
https://github.com/modelcontextprotocol/php-sdk/issues

The resource `name` field is documented as "A human-readable name for this resource" which should naturally support spaces.

