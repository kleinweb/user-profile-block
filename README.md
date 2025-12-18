<!--
SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>

SPDX-License-Identifier: CC-BY-NC-SA-4.0
-->

# User Profile Block

A WordPress Gutenberg block that displays user profile cards with social
media links. Built with modern PHP (8.3+), PHP-DI dependency injection,
and Vite for frontend assets.

## Features

- Gutenberg block for displaying author social links
- Supports 10 social platforms: LinkedIn, Instagram, X (Twitter), Facebook,
  TikTok, YouTube, Threads, Bluesky, Substack, and Medium
- Automatic post author detection with Co-Authors Plus support
- Manual user selection for custom profile displays
- Accessible markup with proper ARIA labels
- Server-side rendered for optimal performance

## Requirements

- PHP 8.3+
- WordPress 6.4+
- Node.js 20+
- Composer 2+

## Installation

```bash
composer install
pnpm install
pnpm build
```

Symlink or copy the plugin to your WordPress `wp-content/plugins/`
directory and activate.

## Usage

### Adding Social Links to Users

Navigate to **Users > Your Profile** in the WordPress admin. You'll find
fields for each supported social platform under the "Social Links" section.
Enter the full URL for each profile.

### Using the Block

1. In the block editor, add the **User Profile** block
   (found in the "Klein College" category)
2. By default, it displays the current post's author(s)
3. Use the block settings to:
   - Toggle "Show post author" on/off
   - Select additional users manually

The block only renders if the selected user(s) have at least one social
link configured.

## Development

### Asset Development

```bash
# Start Vite dev server with HMR
pnpm dev

# Build for production
pnpm build

# Type check
pnpm check
```

### PHP Quality

```bash
# Run all linters (PHPCS + PHPStan)
composer lint

# Auto-fix issues
composer fix

# Static analysis only
composer phpstan
```

### Testing

```bash
# Unit tests (Brain Monkey - fast, no WordPress)
composer test:unit

# Integration tests (wp-browser - requires WordPress)
composer test:integration

# All tests
composer test
```

### Full Check

```bash
just check
```

## Architecture

### Service Container

The plugin uses PHP-DI for dependency injection. Entry point is
`user-profile-block.php` which initializes `ServiceContainer`.

```php
// Access services
$service = \Kleinweb\UserProfile\plugin()->get(SomeService::class);
```

### PHP 8 Attributes

Blocks and meta fields are registered using PHP 8 attributes:

```php
#[Block(name: 'user-profile')]
final class UserProfile
{
    public function render(
        array $attributes,
        string $content,
        WP_Block $block,
    ): string {
        // Server-side render
    }
}
```

```php
#[Meta(
    key: 'linkedin_url',
    objectType: 'user',
    type: 'string',
    showInRest: true,
)]
public string $linkedinUrl = '';
```

### Directory Structure

```text
user-profile-block/
├── config/                  # Container configuration
├── public/build/            # Compiled assets (gitignored)
├── resources/
│   ├── blocks/              # Gutenberg block source
│   │   └── user-profile/    # User Profile block
│   ├── css/                 # Stylesheets
│   └── js/
│       ├── editor/          # Block editor scripts
│       ├── frontend/        # Public-facing scripts
│       └── settings/        # Admin settings page
├── src/
│   ├── Blocks/              # Block PHP classes
│   ├── Container/           # Service container
│   ├── Meta/                # Meta field registration
│   ├── Support/             # Utilities (Vite, ServiceProvider)
│   └── Users/               # User profile fields
├── tests/
│   ├── Integration/         # wp-browser integration tests
│   └── Unit/                # Brain Monkey unit tests
└── user-profile-block.php   # Main plugin file
```

## Supported Social Platforms

| Platform  | Meta Key        |
| --------- | --------------- |
| LinkedIn  | `linkedin_url`  |
| Instagram | `instagram_url` |
| X/Twitter | `twitter_url`   |
| Facebook  | `facebook_url`  |
| TikTok    | `tiktok_url`    |
| YouTube   | `youtube_url`   |
| Threads   | `threads_url`   |
| Bluesky   | `bluesky_url`   |
| Substack  | `substack_url`  |
| Medium    | `medium_url`    |

## License

GPL-2.0-or-later
