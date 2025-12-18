<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Tests\Unit\Blocks;

use Kleinweb\UserProfile\Blocks\SocialIcons;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SocialIcons::class)]
final class SocialIconsTest extends TestCase
{
    #[Test]
    public function getPlatformKeysReturnsAllPlatforms(): void
    {
        $keys = SocialIcons::getPlatformKeys();

        self::assertNotEmpty($keys);
        self::assertContains('linkedin_url', $keys);
        self::assertContains('instagram_url', $keys);
        self::assertContains('twitter_url', $keys);
        self::assertContains('facebook_url', $keys);
        self::assertContains('tiktok_url', $keys);
        self::assertContains('youtube_url', $keys);
        self::assertContains('threads_url', $keys);
        self::assertContains('bluesky_url', $keys);
        self::assertContains('substack_url', $keys);
        self::assertContains('medium_url', $keys);
    }

    #[Test]
    #[DataProvider('platformDataProvider')]
    public function getSvgReturnsValidSvgForSupportedPlatforms(string $metaKey): void
    {
        $svg = SocialIcons::getSvg($metaKey);

        self::assertNotEmpty($svg);
        self::assertStringContainsString('<svg', $svg);
        self::assertStringContainsString('</svg>', $svg);
        self::assertStringContainsString('viewBox="0 0 24 24"', $svg);
    }

    #[Test]
    #[DataProvider('platformDataProvider')]
    public function getLabelReturnsNonEmptyStringForSupportedPlatforms(string $metaKey): void
    {
        $label = SocialIcons::getLabel($metaKey);

        self::assertNotEmpty($label);
    }

    #[Test]
    #[DataProvider('platformDataProvider')]
    public function isSupportedReturnsTrueForSupportedPlatforms(string $metaKey): void
    {
        self::assertTrue(SocialIcons::isSupported($metaKey));
    }

    #[Test]
    public function isSupportedReturnsFalseForUnsupportedPlatform(): void
    {
        self::assertFalse(SocialIcons::isSupported('unsupported_platform'));
        self::assertFalse(SocialIcons::isSupported(''));
        self::assertFalse(SocialIcons::isSupported('random_key'));
    }

    #[Test]
    public function getSvgReturnsEmptyStringForUnsupportedPlatform(): void
    {
        self::assertSame('', SocialIcons::getSvg('unsupported_platform'));
    }

    #[Test]
    public function getLabelReturnsEmptyStringForUnsupportedPlatform(): void
    {
        self::assertSame('', SocialIcons::getLabel('unsupported_platform'));
    }

    #[Test]
    public function labelsAreHumanReadable(): void
    {
        self::assertSame('LinkedIn', SocialIcons::getLabel('linkedin_url'));
        self::assertSame('Instagram', SocialIcons::getLabel('instagram_url'));
        self::assertSame('X', SocialIcons::getLabel('twitter_url'));
        self::assertSame('Facebook', SocialIcons::getLabel('facebook_url'));
        self::assertSame('TikTok', SocialIcons::getLabel('tiktok_url'));
        self::assertSame('YouTube', SocialIcons::getLabel('youtube_url'));
        self::assertSame('Threads', SocialIcons::getLabel('threads_url'));
        self::assertSame('Bluesky', SocialIcons::getLabel('bluesky_url'));
        self::assertSame('Substack', SocialIcons::getLabel('substack_url'));
        self::assertSame('Medium', SocialIcons::getLabel('medium_url'));
    }

    #[Test]
    public function svgsHaveAccessibilityAttributes(): void
    {
        foreach (SocialIcons::getPlatformKeys() as $key) {
            $svg = SocialIcons::getSvg($key);
            $message = "SVG for {$key} should have role='img'";
            self::assertStringContainsString('role="img"', $svg, $message);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function platformDataProvider(): array
    {
        return [
            'linkedin' => ['linkedin_url'],
            'instagram' => ['instagram_url'],
            'twitter' => ['twitter_url'],
            'facebook' => ['facebook_url'],
            'tiktok' => ['tiktok_url'],
            'youtube' => ['youtube_url'],
            'threads' => ['threads_url'],
            'bluesky' => ['bluesky_url'],
            'substack' => ['substack_url'],
            'medium' => ['medium_url'],
        ];
    }
}
