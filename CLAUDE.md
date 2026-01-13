<!--
SPDX-FileCopyrightText: 2025-2026 Temple University <kleinweb@temple.edu>

SPDX-License-Identifier: CC-BY-NC-SA-4.0
-->

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

User Profile Block is a WordPress plugin built with PHP 8.3+, PHP-DI dependency injection, and Vite for frontend assets. It provides a Gutenberg block for displaying user profiles.

## Commands

### Development

```bash
# Install dependencies
composer install
pnpm install

# Start Vite dev server (HMR)
pnpm dev

# Build assets for production
pnpm build

# Type checking
pnpm check
```

### PHP Quality

```bash
# Linting (PHPCS + PHPStan)
composer lint

# Auto-fix with PHP CS Fixer and PHPCBF
composer fix

# Static analysis only
composer phpstan
```

### Testing

```bash
# Unit tests (Brain Monkey, fast, no WordPress)
composer test:unit

# Run a single unit test file
vendor/bin/phpunit tests/Unit/SomeTest.php

# Run a single test method
vendor/bin/phpunit --filter testMethodName

# Integration tests (wp-browser, requires WordPress)
composer test:integration

# All tests
composer test
```

### Just Tasks

```bash
just check   # Full lint + format check
just lint    # Linting only
just fix     # Auto-fix everything
just fmt     # Safe formatting only
```

## Architecture

### Service Container

The plugin uses PHP-DI for dependency injection. Entry point is `user-profile-block.php` which initializes `ServiceContainer`.

- **Service Providers** are registered in `ServiceContainer::$providers`
- **Container definitions** live in `config/container.php`
- Access services via `\Kleinweb\UserProfile\plugin()->get(ServiceClass::class)`

### Key Abstractions

- `src/Support/Contracts/Bootable` - Services that need initialization on `plugins_loaded`
- `src/Support/Contracts/HookSubscriber` - Declarative WordPress hook registration
- `src/Attributes/` - PHP 8 attributes for registering meta fields, post types, etc.

### Hook Subscribers

Add classes implementing `HookSubscriber` to the `hook_subscribers` array in `config/container.php`. The `getSubscribedHooks()` method returns hook configurations:

```php
['hook_name' => 'methodName']
['hook_name' => ['method' => 'methodName', 'args' => 2, 'priority' => 10, 'type' => 'filter']]
```

### Namespace

PHP: `Kleinweb\UserProfile\`
Tests: `Kleinweb\UserProfile\Tests\`

## Testing Notes

- Unit tests use Brain Monkey to mock WordPress functions
- Integration tests use lucatume/wp-browser with WPLoader module
- Integration test config requires `.env.testing` (copy from `.env.testing.example`)
