<?php

declare(strict_types=1);

namespace Kleinweb\UserProfile\Tests\Unit\Blocks;

use Brain\Monkey\Functions;
use Kleinweb\UserProfile\Blocks\UserProfile;
use Kleinweb\UserProfile\Tests\Unit\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use WP_Block;
use WP_User;

#[CoversClass(UserProfile::class)]
final class UserProfileTest extends TestCase
{
    private UserProfile $block;

    protected function setUp(): void
    {
        parent::setUp();
        $this->block = new UserProfile();

        // Set up common WordPress function stubs
        Functions\stubs([
            'esc_html' => static fn ($text) => $text,
            'esc_html__' => static fn ($text) => $text,
            'esc_attr' => static fn ($text) => $text,
            'esc_url' => static fn ($url) => $url,
            '__' => static fn ($text) => $text,
            'get_author_posts_url' => static fn ($id) => "https://example.com/author/{$id}/",
        ]);
    }

    #[Test]
    public function renderReturnsEmptyStringWhenNoUsers(): void
    {
        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(0);

        $wpBlock = $this->createWpBlock([]);
        $attributes = ['usePostAuthor' => true, 'selectedUserIds' => []];
        $result = $this->block->render($attributes, '', $wpBlock);

        self::assertSame('', $result);
    }

    #[Test]
    public function renderReturnsEmptyStringWhenUserHasNoSocialLinks(): void
    {
        $user = $this->createMockUser(1, 'Test User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')->andReturn('');
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
        ], '', $wpBlock);

        // Card should not render without social links
        self::assertStringNotContainsString('__card', $result);
    }

    #[Test]
    public function renderOutputsUserCardWhenUserHasSocialLinks(): void
    {
        $user = $this->createMockUser(1, 'Test User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing($this->socialLinkCallback('linkedin_url', 'https://li.com/in/test'));
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
        ], '', $wpBlock);

        self::assertStringContainsString('wp-block-kleinweb-user-profile', $result);
        self::assertStringContainsString('wp-block-kleinweb-user-profile__card', $result);
        self::assertStringContainsString('Test User', $result);
        self::assertStringContainsString('<article', $result);
    }

    #[Test]
    public function renderIncludesSocialLinksWhenPresent(): void
    {
        $user = $this->createMockUser(1, 'Social User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing(static fn (int $_userId, string $key): string => match ($key) {
                'linkedin_url' => 'https://linkedin.com/in/testuser',
                'twitter_url' => 'https://twitter.com/testuser',
                default => '',
            });
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
        ], '', $wpBlock);

        self::assertStringContainsString('wp-block-kleinweb-user-profile__social', $result);
        self::assertStringContainsString('https://linkedin.com/in/testuser', $result);
        self::assertStringContainsString('https://twitter.com/testuser', $result);
        self::assertStringContainsString('<svg', $result);
    }

    #[Test]
    public function renderSupportsSelectedUserIds(): void
    {
        $selectedUser = $this->createMockUser(99, 'Selected User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_userdata')->with(99)->andReturn($selectedUser);
        Functions\expect('get_user_meta')
            ->andReturnUsing($this->socialLinkCallback('linkedin_url', 'https://li.com/in/sel'));
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => false,
            'selectedUserIds' => [99],
        ], '', $wpBlock);

        self::assertStringContainsString('Selected User', $result);
    }

    #[Test]
    public function renderDeduplicatesUsers(): void
    {
        $user = $this->createMockUser(1, 'Duplicate User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing($this->socialLinkCallback('linkedin_url', 'https://li.com/in/dupe'));
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            // Same as post author
            'selectedUserIds' => [1],
        ], '', $wpBlock);

        // Should only appear once in the card (appears twice: heading text + link)
        self::assertSame(1, substr_count($result, '__card'));
    }

    #[Test]
    public function renderUsesContextPostId(): void
    {
        $user = $this->createMockUser(5, 'Context User');

        Functions\expect('get_post_field')->with('post_author', 42)->andReturn(5);
        Functions\expect('get_userdata')->with(5)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing($this->socialLinkCallback('instagram_url', 'https://ig.com/ctx'));
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        // Block context provides post ID 42
        $wpBlock = $this->createWpBlock(['postId' => 42]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
        ], '', $wpBlock);

        self::assertStringContainsString('Context User', $result);
    }

    #[Test]
    public function renderShowsConnectWithHeading(): void
    {
        $user = $this->createMockUser(1, 'Heading User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing($this->socialLinkCallback('twitter_url', 'https://x.com/heading'));
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
        ], '', $wpBlock);

        self::assertStringContainsString('Connect with', $result);
        self::assertStringContainsString('wp-block-kleinweb-user-profile__name', $result);
    }

    #[Test]
    public function renderIncludesAccessibleSocialNavigation(): void
    {
        $user = $this->createMockUser(1, 'Accessible User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing($this->socialLinkCallback('facebook_url', 'https://fb.com/acc'));
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
        ], '', $wpBlock);

        self::assertStringContainsString('<nav', $result);
        self::assertStringContainsString('aria-label', $result);
        self::assertStringContainsString('Social links for', $result);
    }

    #[Test]
    public function renderIncludesScreenReaderLabels(): void
    {
        $user = $this->createMockUser(1, 'Screen Reader User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing($this->socialLinkCallback('youtube_url', 'https://yt.com/@sr'));
        Functions\expect('get_block_wrapper_attributes')
            ->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
        ], '', $wpBlock);

        self::assertStringContainsString('screen-reader-text', $result);
        self::assertStringContainsString('wp-block-kleinweb-user-profile__social-label', $result);
    }

    /**
     * Create a mock WP_Block object.
     *
     * @param array<string, mixed> $context
     *
     * @return WP_Block&\Mockery\MockInterface
     */
    private function createWpBlock(array $context): WP_Block
    {
        $block = Mockery::mock(WP_Block::class);
        \assert($block instanceof WP_Block);
        $block->context = $context;

        return $block;
    }

    /**
     * Create a mock WP_User object.
     *
     * @return WP_User&\Mockery\MockInterface
     */
    private function createMockUser(
        int $id,
        string $displayName,
        string $description = '',
    ): WP_User {
        $user = Mockery::mock(WP_User::class);
        \assert($user instanceof WP_User);
        $user->ID = $id;
        $user->display_name = $displayName;
        $user->description = $description;

        return $user;
    }

    /**
     * Create a callback for get_user_meta that returns URL for specific key.
     *
     * @return callable(int, string): string
     */
    private function socialLinkCallback(string $metaKey, string $url): callable
    {
        return static fn (int $_userId, string $key): string => $key === $metaKey ? $url : '';
    }
}
