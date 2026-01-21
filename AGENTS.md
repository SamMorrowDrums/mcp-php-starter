# MCP PHP Starter - AGENTS.md

## Building and Testing

- **Install dependencies:**
  ```bash
  composer install
  ```

- **Run the server:**
  ```bash
  php bin/server.php
  ```

- **Run tests:**
  ```bash
  vendor/bin/phpunit
  ```

- **Check code style:**
  ```bash
  vendor/bin/php-cs-fixer fix --dry-run --diff
  ```

- **Fix code style:**
  ```bash
  vendor/bin/php-cs-fixer fix
  ```

- **Static analysis:**
  ```bash
  vendor/bin/phpstan analyse
  ```

## Code Conventions

- Follow PSR-12 coding standards
- Use strict types (`declare(strict_types=1);`)
- Use type hints for all parameters and return types
- Prefer constructor property promotion (PHP 8+)

## Before Committing Checklist

1. ✅ Run `vendor/bin/php-cs-fixer fix` to fix code style
2. ✅ Run `vendor/bin/phpstan analyse` for static analysis
3. ✅ Run `vendor/bin/phpunit` to verify tests pass
4. ✅ Test the server with `php bin/server.php`
