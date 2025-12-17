# User Profile Block

A WordPress plugin built with modern PHP practices: Composer, PHP-DI service container, PHP 8.3+ attributes, and Vite for assets.

## Requirements

- PHP 8.3+
- WordPress 6.4+
- Node.js 20+
- Composer 2+

## Installation

### Using DDEV (recommended)

```bash
ddev start
ddev composer install
ddev npm install
ddev npm run build
```

### Manual Setup

```bash
composer install
npm install
npm run build
```

Then symlink or copy the plugin to your WordPress `wp-content/plugins/` directory.

## Development

### Asset Development

Start the Vite dev server for hot module replacement:

```bash
npm run dev
```

Build for production:

```bash
npm run build
```

### PHP Development

Run linting:

```bash
composer lint
composer lint:fix  # Auto-fix issues
```

Run static analysis:

```bash
composer analyze
```

## Testing

### Unit Tests (Brain Monkey)

Fast, isolated tests that mock WordPress functions:

```bash
composer test:unit
```

### Integration Tests (wp-browser)

Tests that run against a real WordPress installation:

```bash
# With DDEV
ddev exec vendor/bin/codecept run wpunit

# Or
composer test:integration
```

### All Tests

```bash
composer test
```

## Architecture

### Service Container

The plugin uses PHP-DI for dependency injection. Services are registered through service providers in `src/Providers/`.

```php
// Access services anywhere
$settings = \Kleinweb\user-profile-block\plugin()->get(SettingsRegistry::class);
```

### Attributes for Registration

Define custom post types, meta fields, and REST fields using PHP 8 attributes:

```php
#[PostType(
    slug: 'project',
    singular: 'Project',
    plural: 'Projects',
)]
final class Project
{
    #[Meta(
        key: 'project_client',
        objectType: 'post',
        objectSubtype: 'project',
        type: 'string',
        label: 'Client Name',
    )]
    public string $client = '';
}
```

### Hook Subscribers

Implement `HookSubscriber` to declare WordPress hooks:

```php
final class MyService implements HookSubscriber
{
    public function getSubscribedHooks(): array
    {
        return [
            'init' => 'onInit',
            'save_post' => ['method' => 'onSavePost', 'args' => 2],
        ];
    }
}
```

### Settings

Settings bypass the WordPress Settings API in favor of a REST-based approach:

- Schema defined in `SettingsRegistry`
- REST endpoints at `/wp-json/plugin-name/v1/settings`
- React UI in `resources/js/settings/`

## Directory Structure

```
plugin-name/
├── config/              # Container configuration
├── public/build/        # Compiled assets (gitignored)
├── resources/
│   ├── css/            # Source CSS
│   └── js/
│       ├── editor/     # Block editor sidebar
│       ├── frontend/   # Public-facing scripts
│       └── settings/   # Settings page React app
├── src/
│   ├── Assets/         # Vite helper
│   ├── Attributes/     # PHP attributes (PostType, Meta, RestField)
│   ├── Container/      # Service container
│   ├── Contracts/      # Interfaces
│   ├── PostTypes/      # Post type definitions
│   ├── Providers/      # Service providers
│   ├── Settings/       # Settings registry and REST controller
│   ├── Taxonomies/     # Term meta definitions
│   └── Users/          # User meta definitions
├── tests/
│   ├── unit/           # Brain Monkey unit tests
│   └── wpunit/         # wp-browser integration tests
└── plugin-name.php     # Main plugin file
```

## Customization

### Adding a New Post Type

1. Create a class in `src/PostTypes/`
2. Add the `#[PostType]` attribute
3. Add any meta fields with `#[Meta]` attributes
4. Register in `PostTypeServiceProvider::$postTypeClasses`
5. Register in `MetaServiceProvider::$metaClasses`

### Adding New Settings

Edit `src/Settings/SettingsRegistry.php` and add fields to the `$schema` array. The React UI will automatically render the appropriate controls.

### Adding Hook Subscribers

1. Create a class implementing `HookSubscriber`
2. Add to the `hook_subscribers` array in `config/container.php`

## License

GPL-2.0-or-later
