<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Mock common WordPress functions
        $this->mockWordPressFunctions();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Mock commonly used WordPress functions.
     */
    protected function mockWordPressFunctions(): void
    {
        // Translation functions - return input unchanged
        // Note: _e and esc_html_e echo in WP but we just return for tests
        Functions\stubs([
            '__' => static fn ($text, $_domain = 'default') => $text,
            '_e' => static fn ($text, $_domain = 'default') => $text,
            '_n' => static fn ($s, $p, $n, $_d = 'default') => $n === 1 ? $s : $p,
            '_x' => static fn ($text, $_context, $_domain = 'default') => $text,
            'esc_html__' => static fn ($text, $_domain = 'default') => $text,
            'esc_attr__' => static fn ($text, $_domain = 'default') => $text,
            'esc_html_e' => static fn ($text, $_domain = 'default') => $text,
        ]);

        // Escaping functions
        Functions\stubs([
            'esc_html' => static fn ($t) => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'),
            'esc_attr' => static fn ($t) => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'),
            'esc_url' => static fn ($url) => filter_var($url, FILTER_SANITIZE_URL),
            'esc_url_raw' => static fn ($url) => filter_var($url, FILTER_SANITIZE_URL),
            'sanitize_text_field' => static fn ($t) => htmlspecialchars(strip_tags((string) $t)),
        ]);

        // Option functions - can be overridden in individual tests
        Functions\stubs([
            'get_option' => static fn ($_opt, $default = false) => $default,
            'update_option' => static fn ($_opt, $_val) => true,
            'delete_option' => static fn ($_opt) => true,
        ]);

        // Plugin functions
        Functions\stubs([
            'plugin_basename' => static fn ($f) => basename(dirname($f)) . '/' . basename($f),
            'plugins_url' => static fn ($p, $_f) => 'https://example.com/wp-content/plugins/' . $p,
        ]);
    }
}
