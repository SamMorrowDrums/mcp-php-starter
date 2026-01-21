# PHP SDK Patch Required

## Issue

The PHP MCP SDK (v0.3.0) has overly restrictive validation for resource and resource template names that only allows alphanumeric characters, underscores, and hyphens. This prevents using spaces in resource names, which is required by the [Canonical MCP Interface](https://github.com/SamMorrowDrums/mcp-starters/blob/main/CANONICAL_INTERFACE.md).

## Files That Need Patching

After running `composer install`, you need to manually patch these SDK files:

### 1. `vendor/mcp/sdk/src/Schema/Resource.php`

Change line 38-40 from:
```php
/**
 * Resource name pattern regex - must contain only alphanumeric characters, underscores, and hyphens.
 */
private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';
```

To:
```php
/**
 * Resource name pattern regex - must contain only alphanumeric characters, underscores, hyphens, and spaces.
 */
private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_ -]+$/';
```

And update the error message at line 71 from:
```php
throw new InvalidArgumentException('Invalid resource name: must contain only alphanumeric characters, underscores, and hyphens.');
```

To:
```php
throw new InvalidArgumentException('Invalid resource name: must contain only alphanumeric characters, underscores, hyphens, and spaces.');
```

### 2. `vendor/mcp/sdk/src/Schema/ResourceTemplate.php`

Make the same changes:

Change line 35-37 from:
```php
/**
 * Resource name pattern regex - must contain only alphanumeric characters, underscores, and hyphens.
 */
private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';
```

To:
```php
/**
 * Resource name pattern regex - must contain only alphanumeric characters, underscores, hyphens, and spaces.
 */
private const RESOURCE_NAME_PATTERN = '/^[a-zA-Z0-9_ -]+$/';
```

And update the error message at line 62 from:
```php
throw new InvalidArgumentException('Invalid resource name: must contain only alphanumeric characters, underscores, and hyphens.');
```

To:
```php
throw new InvalidArgumentException('Invalid resource name: must contain only alphanumeric characters, underscores, hyphens, and spaces.');
```

## Upstream Fix Needed

This issue should be reported to the PHP SDK maintainers at:
https://github.com/modelcontextprotocol/php-sdk/issues

The resource `name` field is documented as "A human-readable name for this resource" which should naturally support spaces.

## Alternative: Use Composer Patches

Consider using [cweagans/composer-patches](https://github.com/cweagans/composer-patches) to automatically apply these patches after `composer install`.
