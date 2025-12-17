<?php

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
        Functions\stubs([
            '__' => static fn ($text, $domain = 'default') => $text,
            '_e' => static fn ($text, $domain = 'default') => print($text),
            '_n' => static fn ($single, $plural, $number, $domain = 'default') => $number === 1 ? $single : $plural,
            '_x' => static fn ($text, $context, $domain = 'default') => $text,
            'esc_html__' => static fn ($text, $domain = 'default') => $text,
            'esc_attr__' => static fn ($text, $domain = 'default') => $text,
            'esc_html_e' => static fn ($text, $domain = 'default') => print($text),
        ]);

        // Escaping functions
        Functions\stubs([
            'esc_html' => static fn ($text) => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'),
            'esc_attr' => static fn ($text) => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'),
            'esc_url' => static fn ($url) => filter_var($url, FILTER_SANITIZE_URL),
            'esc_url_raw' => static fn ($url) => filter_var($url, FILTER_SANITIZE_URL),
            'sanitize_text_field' => static fn ($text) => htmlspecialchars(strip_tags((string) $text)),
        ]);

        // Option functions - can be overridden in individual tests
        Functions\stubs([
            'get_option' => static fn ($option, $default = false) => $default,
            'update_option' => static fn ($option, $value) => true,
            'delete_option' => static fn ($option) => true,
        ]);

        // Plugin functions
        Functions\stubs([
            'plugin_basename' => static fn ($file) => basename(dirname($file)) . '/' . basename($file),
            'plugins_url' => static fn ($path, $file) => 'https://example.com/wp-content/plugins/' . $path,
        ]);
    }
}
