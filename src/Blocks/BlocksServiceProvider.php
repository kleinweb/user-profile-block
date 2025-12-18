<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Blocks;

use DI\Container;
use Kleinweb\UserProfile\Blocks\Attributes\Block;
use Kleinweb\UserProfile\Support\Contracts\Bootable;
use Kleinweb\UserProfile\Support\ServiceProvider;
use Kleinweb\UserProfile\Support\Vite;
use ReflectionClass;
use WP_Block_Editor_Context;
use WP_Block_Type_Registry;

/**
 * Registers Gutenberg blocks using block.json files.
 *
 * Blocks are discovered from:
 * 1. PHP classes with #[Block] attribute (for server-side rendering)
 * 2. block.json files in resources/blocks/{block-name}/
 *
 * The block.json file is the source of truth for block configuration.
 * PHP classes provide optional server-side render callbacks.
 */
final class BlocksServiceProvider extends ServiceProvider implements Bootable
{
    private Container $container;

    /**
     * PHP classes that handle server-side rendering for blocks.
     * Each class should have a #[Block] attribute.
     *
     * @var list<class-string>
     */
    private array $blockClasses = [
        UserProfile::class,
    ];

    /**
     * Additional block directories to scan for block.json files
     * that don't have a PHP handler class.
     *
     * @var list<string>
     */
    private array $additionalBlockDirs = [];

    /** @var array<string, array{class: class-string, attribute: Block}> */
    private array $blockHandlers = [];

    public function register(Container $container): void
    {
        $this->container = $container;
        $this->parseBlockAttributes();
    }

    public function boot(): void
    {
        add_action('init', [$this, 'registerBlockScripts'], 5);
        add_action('init', [$this, 'registerBlocks']);
        add_filter('block_categories_all', [$this, 'registerBlockCategory'], 10, 2);
    }

    /**
     * Register all blocks from block.json files.
     */
    public function registerBlocks(): void
    {
        $blocksDir = \Kleinweb\UserProfile\PLUGIN_DIR . '/resources/blocks';

        // Register blocks that have PHP handlers
        foreach ($this->blockHandlers as $blockName => $config) {
            $this->registerBlockWithHandler($blockName, $config);
        }

        // Register any additional blocks without PHP handlers
        foreach ($this->additionalBlockDirs as $dir) {
            $this->registerBlockFromDirectory($dir);
        }

        // Auto-discover blocks in the blocks directory
        $this->discoverAndRegisterBlocks($blocksDir);
    }

    /**
     * Register block editor scripts before blocks are registered.
     */
    public function registerBlockScripts(): void
    {
        $scriptUrl = Vite::asset('resources/blocks/user-profile/index.tsx');
        if ($scriptUrl === null) {
            return;
        }

        wp_register_script(
            'kleinweb-user-profile-block-editor',
            $scriptUrl,
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data'],
            \Kleinweb\UserProfile\VERSION,
            true,
        );
    }

    /**
     * Add a custom block category for this plugin.
     *
     * @param array<int, array{slug: string, title: string, icon?: string}> $categories
     *
     * @return array<int, array{slug: string, title: string, icon?: string}>
     *
     * @phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- required by filter signature
     */
    public function registerBlockCategory(
        array $categories,
        ?WP_Block_Editor_Context $context = null,
    ): array {
        unset($context);

        return array_merge(
            [
                [
                    'slug' => 'kleinweb',
                    'title' => __('Klein College', 'user-profile-block'),
                    'icon' => 'admin-users',
                ],
            ],
            $categories,
        );
    }

    /**
     * Register a block that has a PHP handler class.
     *
     * Uses block.json as the source of truth, adding PHP render callback.
     *
     * @param array{class: class-string, attribute: Block} $config
     */
    private function registerBlockWithHandler(string $blockName, array $config): void
    {
        unset($blockName);
        $attribute = $config['attribute'];
        $instance = $this->container->get($config['class']);
        $renderMethod = $attribute->renderCallback ?? 'render';

        if (!method_exists($instance, $renderMethod)) {
            return;
        }

        // Build path to block.json
        $blockJsonPath = sprintf(
            '%s/resources/blocks/%s/block.json',
            \Kleinweb\UserProfile\PLUGIN_DIR,
            $attribute->name,
        );

        if (!file_exists($blockJsonPath)) {
            return;
        }

        // Register using block.json (for styles) with PHP render callback
        register_block_type($blockJsonPath, [
            'render_callback' => $instance->$renderMethod(...),
            'editor_script_handles' => ['kleinweb-user-profile-block-editor'],
        ]);
    }

    /**
     * Register a block from a directory containing block.json.
     */
    private function registerBlockFromDirectory(string $dir): void
    {
        $blockJsonPath = $dir . '/block.json';

        if (!file_exists($blockJsonPath)) {
            return;
        }

        register_block_type($blockJsonPath);
    }

    /**
     * Discover and register blocks that aren't explicitly listed.
     */
    private function discoverAndRegisterBlocks(string $blocksDir): void
    {
        if (!is_dir($blocksDir)) {
            return;
        }

        $dirs = glob($blocksDir . '/*/block.json');
        if ($dirs === false) {
            return;
        }

        foreach ($dirs as $blockJson) {
            $blockDir = dirname($blockJson);
            $blockName = basename($blockDir);
            $fullName = 'kleinweb/' . $blockName;

            // Skip if already registered via PHP handler
            if (isset($this->blockHandlers[$fullName])) {
                continue;
            }

            // Skip if block is already registered
            if (WP_Block_Type_Registry::get_instance()->is_registered($fullName)) {
                continue;
            }

            register_block_type($blockJson);
        }
    }

    /**
     * Parse #[Block] attributes from registered classes.
     */
    private function parseBlockAttributes(): void
    {
        foreach ($this->blockClasses as $class) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(Block::class);

            if ($attributes === []) {
                continue;
            }

            $blockAttribute = $attributes[0]->newInstance();
            $fullName = $blockAttribute->getFullName();

            $this->blockHandlers[$fullName] = [
                'class' => $class,
                'attribute' => $blockAttribute,
            ];
        }
    }
}
