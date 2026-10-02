# PHP SDK Patch - No Longer Needed

## Issue

The PHP MCP SDK (v0.3.0) has overly restrictive validation for resource and resource template names that only allows alphanumeric characters, underscores, and hyphens. This prevents using spaces in resource names, which is required by the [Canonical MCP Interface](https://github.com/SamMorrowDrums/mcp-starters/blob/main/CANONICAL_INTERFACE.md).

## Resolution

The project now uses MCP SDK v0.8.1 or newer, which accepts spaces in resource
and resource template names without vendor modifications. The post-install
patch script and Composer hooks have been removed. Regression tests exercise
server construction and names containing spaces against the installed SDK.
