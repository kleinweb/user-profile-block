<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Support;

/**
 * Vite asset helper for loading built assets.
 */
final class Vite
{
    private const MANIFEST_PATH = '/public/build/.vite/manifest.json';
    private const BUILD_PATH = '/public/build/';
    private const HOT_FILE = '/public/build/hot';

    /** @var array<string, array{file: string, css?: list<string>}>|null */
    private static ?array $manifest = null;

    /**
     * Check if Vite dev server is running.
     */
    public static function isHot(): bool
    {
        return file_exists(\Kleinweb\UserProfile\PLUGIN_DIR . self::HOT_FILE);
    }

    /**
     * Get the dev server URL.
     */
    public static function hotUrl(): ?string
    {
        if (!self::isHot()) {
            return null;
        }

        $content = file_get_contents(\Kleinweb\UserProfile\PLUGIN_DIR . self::HOT_FILE);

        return $content !== false ? trim($content) : null;
    }

    /**
     * Get the URL for an asset.
     *
     * @param string $entry The entry point (e.g., 'resources/blocks/user-profile/index.tsx')
     */
    public static function asset(string $entry): ?string
    {
        if (self::isHot()) {
            return self::hotUrl() . '/' . $entry;
        }

        $manifest = self::getManifest();
        if (!isset($manifest[$entry])) {
            return null;
        }

        $file = $manifest[$entry]['file'];

        return plugins_url(self::BUILD_PATH . $file, \Kleinweb\UserProfile\PLUGIN_FILE);
    }

    /**
     * Get CSS files for an entry.
     *
     * @param string $entry The entry point
     *
     * @return list<string>
     */
    public static function css(string $entry): array
    {
        if (self::isHot()) {
            return [];
        }

        $manifest = self::getManifest();
        if (!isset($manifest[$entry]['css'])) {
            return [];
        }

        $pluginFile = \Kleinweb\UserProfile\PLUGIN_FILE;

        return array_map(
            static fn (string $file): string => plugins_url(self::BUILD_PATH . $file, $pluginFile),
            $manifest[$entry]['css'],
        );
    }

    /**
     * Get the manifest data.
     *
     * @return array<string, array{file: string, css?: list<string>}>
     */
    private static function getManifest(): array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }

        $manifestPath = \Kleinweb\UserProfile\PLUGIN_DIR . self::MANIFEST_PATH;
        if (!file_exists($manifestPath)) {
            self::$manifest = [];

            return self::$manifest;
        }

        $content = file_get_contents($manifestPath);
        if ($content === false) {
            self::$manifest = [];

            return self::$manifest;
        }

        $decoded = json_decode($content, true);
        self::$manifest = is_array($decoded) ? $decoded : [];

        return self::$manifest;
    }
}
