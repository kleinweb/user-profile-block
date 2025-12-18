<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Blocks\Attributes;

use Attribute;

/**
 * Marks a class as a Gutenberg block handler.
 *
 * The block's configuration comes from block.json. This attribute:
 * - Links the PHP class to a block.json file
 * - Optionally specifies the render callback method
 * - Allows additional PHP-side configuration
 *
 * Block registration uses register_block_type() with the block.json path,
 * which WordPress reads automatically for all block metadata.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Block
{
    /**
     * @param string      $name           Block name without namespace (must match block.json "name" without prefix)
     * @param string|null $renderCallback Method name for server-side rendering (null = use 'render' if exists)
     * @param string|null $blockJsonPath  Custom path to block.json (null = auto-detect from resources/js/blocks/{name})
     */
    public function __construct(
        public string $name,
        public ?string $renderCallback = null,
        public ?string $blockJsonPath = null,
    ) {}

    /**
     * Get the full block name with plugin namespace.
     */
    public function getFullName(): string
    {
        return 'kleinweb/' . $this->name;
    }
}
