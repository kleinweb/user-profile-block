<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Meta;

use DI\Container;
use Kleinweb\UserProfile\Meta\Attributes\Meta;
use Kleinweb\UserProfile\Support\Contracts\Bootable;
use Kleinweb\UserProfile\Support\ServiceProvider;
use Kleinweb\UserProfile\Users\UserProfileFields;
use ReflectionClass;
use ReflectionProperty;

final class MetaServiceProvider extends ServiceProvider implements Bootable
{
    /** @var list<class-string> Classes containing meta definitions */
    private array $metaClasses = [
        UserProfileFields::class,
    ];

    /** @var array<string, array<string, Meta>> Parsed meta attributes by object type */
    private array $metaFields = [
        'post' => [],
        'term' => [],
        'user' => [],
        'comment' => [],
    ];

    /** @phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- required by interface */
    public function register(Container $container): void
    {
        unset($container);
        $this->parseMetaAttributes();
    }

    public function boot(): void
    {
        add_action('init', [$this, 'registerMeta'], 15);
    }

    public function registerMeta(): void
    {
        foreach ($this->metaFields as $objectType => $fields) {
            foreach ($fields as $key => $meta) {
                register_meta($objectType, $key, $meta->toArgs());
            }
        }
    }

    /**
     * Get meta UI configurations for the block editor.
     *
     * @return array<string, array<string, array<string, array<string, mixed>>>>
     */
    public function getEditorMetaConfig(): array
    {
        $config = [];

        foreach ($this->metaFields as $objectType => $fields) {
            foreach ($fields as $key => $meta) {
                if (!$meta->showInEditor) {
                    continue;
                }

                $subtype = $meta->objectSubtype ?? '_all';
                $config[$objectType][$subtype][$key] = $meta->getUiConfig();
            }
        }

        return $config;
    }

    /**
     * Get all registered meta fields.
     *
     * @return array<string, array<string, Meta>>
     */
    public function getMetaFields(): array
    {
        return $this->metaFields;
    }

    private function parseMetaAttributes(): void
    {
        foreach ($this->metaClasses as $class) {
            $reflection = new ReflectionClass($class);

            foreach ($reflection->getProperties() as $property) {
                $this->parsePropertyMeta($property);
            }
        }
    }

    private function parsePropertyMeta(ReflectionProperty $property): void
    {
        $attributes = $property->getAttributes(Meta::class);

        foreach ($attributes as $attribute) {
            $meta = $attribute->newInstance();
            $this->metaFields[$meta->objectType][$meta->key] = $meta;
        }
    }
}
