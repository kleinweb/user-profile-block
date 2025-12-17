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
    }

    #[Test]
    public function renderReturnsEmptyStringWhenNoUsers(): void
    {
        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(0);

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render(['usePostAuthor' => true, 'selectedUserIds' => []], '', $wpBlock);

        self::assertSame('', $result);
    }

    #[Test]
    public function renderOutputsUserCardWhenAuthorExists(): void
    {
        $user = $this->createMockUser(1, 'Test User', 'A bio');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')->andReturn('');
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');
        Functions\expect('get_avatar')->andReturn('<img src="avatar.jpg" />');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
            'showAvatar' => true,
            'showName' => true,
            'showBio' => true,
        ], '', $wpBlock);

        self::assertStringContainsString('wp-block-kleinweb-user-profile', $result);
        self::assertStringContainsString('Test User', $result);
        self::assertStringContainsString('A bio', $result);
        self::assertStringContainsString('<article', $result);
    }

    #[Test]
    public function renderHidesAvatarWhenDisabled(): void
    {
        $user = $this->createMockUser(1, 'Test User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')->andReturn('');
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
            'showAvatar' => false,
            'showName' => true,
            'showBio' => false,
        ], '', $wpBlock);

        self::assertStringNotContainsString('wp-block-kleinweb-user-profile__avatar', $result);
    }

    #[Test]
    public function renderHidesNameWhenDisabled(): void
    {
        $user = $this->createMockUser(1, 'Hidden Name');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')->andReturn('');
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');
        Functions\expect('get_avatar')->andReturn('<img src="avatar.jpg" />');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
            'showAvatar' => true,
            'showName' => false,
            'showBio' => false,
        ], '', $wpBlock);

        self::assertStringNotContainsString('Hidden Name', $result);
        self::assertStringNotContainsString('wp-block-kleinweb-user-profile__name', $result);
    }

    #[Test]
    public function renderIncludesSocialLinksWhenPresent(): void
    {
        $user = $this->createMockUser(1, 'Social User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing(static function (int $userId, string $key) {
                if ($key === 'linkedin_url') {
                    return 'https://linkedin.com/in/testuser';
                }
                if ($key === 'twitter_url') {
                    return 'https://twitter.com/testuser';
                }

                return '';
            });
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');
        Functions\expect('get_avatar')->andReturn('<img src="avatar.jpg" />');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
            'showAvatar' => true,
            'showName' => true,
            'showBio' => false,
            'showLabels' => false,
            'iconSize' => 'medium',
        ], '', $wpBlock);

        self::assertStringContainsString('wp-block-kleinweb-user-profile__social', $result);
        self::assertStringContainsString('https://linkedin.com/in/testuser', $result);
        self::assertStringContainsString('https://twitter.com/testuser', $result);
        self::assertStringContainsString('<svg', $result);
    }

    #[Test]
    public function renderShowsLabelsWhenEnabled(): void
    {
        $user = $this->createMockUser(1, 'Label User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing(static fn (int $userId, string $key) => $key === 'instagram_url' ? 'https://instagram.com/testuser' : '');
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');
        Functions\expect('get_avatar')->andReturn('<img src="avatar.jpg" />');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
            'showAvatar' => true,
            'showName' => true,
            'showBio' => false,
            'showLabels' => true,
            'iconSize' => 'medium',
        ], '', $wpBlock);

        self::assertStringContainsString('wp-block-kleinweb-user-profile__social-label', $result);
        self::assertStringContainsString('Instagram', $result);
    }

    #[Test]
    public function renderSupportsSelectedUserIds(): void
    {
        $selectedUser = $this->createMockUser(99, 'Selected User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_userdata')->with(99)->andReturn($selectedUser);
        Functions\expect('get_user_meta')->andReturn('');
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');
        Functions\expect('get_avatar')->andReturn('<img src="avatar.jpg" />');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => false,
            'selectedUserIds' => [99],
            'showAvatar' => true,
            'showName' => true,
            'showBio' => false,
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
        Functions\expect('get_user_meta')->andReturn('');
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');
        Functions\expect('get_avatar')->andReturn('<img src="avatar.jpg" />');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            // Same as post author
            'selectedUserIds' => [1],
            'showAvatar' => true,
            'showName' => true,
            'showBio' => false,
        ], '', $wpBlock);

        // Should only appear once
        self::assertSame(1, substr_count($result, 'Duplicate User'));
    }

    #[Test]
    public function renderUsesContextPostId(): void
    {
        $user = $this->createMockUser(5, 'Context User');

        Functions\expect('get_post_field')->with('post_author', 42)->andReturn(5);
        Functions\expect('get_userdata')->with(5)->andReturn($user);
        Functions\expect('get_user_meta')->andReturn('');
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');
        Functions\expect('get_avatar')->andReturn('<img src="avatar.jpg" />');

        // Block context provides post ID 42
        $wpBlock = $this->createWpBlock(['postId' => 42]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
            'showAvatar' => true,
            'showName' => true,
            'showBio' => false,
        ], '', $wpBlock);

        self::assertStringContainsString('Context User', $result);
    }

    #[Test]
    public function renderAppliesIconSizeClass(): void
    {
        $user = $this->createMockUser(1, 'Size User');

        Functions\expect('get_the_ID')->andReturn(1);
        Functions\expect('get_post_field')->with('post_author', 1)->andReturn(1);
        Functions\expect('get_userdata')->with(1)->andReturn($user);
        Functions\expect('get_user_meta')
            ->andReturnUsing(static fn (int $userId, string $key) => $key === 'facebook_url' ? 'https://facebook.com/testuser' : '');
        Functions\expect('get_block_wrapper_attributes')->andReturn('class="wp-block-kleinweb-user-profile"');
        Functions\expect('get_avatar')->andReturn('<img src="avatar.jpg" />');

        $wpBlock = $this->createWpBlock([]);
        $result = $this->block->render([
            'usePostAuthor' => true,
            'selectedUserIds' => [],
            'showAvatar' => true,
            'showName' => true,
            'showBio' => false,
            'showLabels' => false,
            'iconSize' => 'large',
        ], '', $wpBlock);

        self::assertStringContainsString('wp-block-kleinweb-user-profile__social-link--large', $result);
    }

    /**
     * Create a mock WP_Block object.
     *
     * @param array<string, mixed> $context
     */
    private function createWpBlock(array $context): WP_Block
    {
        $block = Mockery::mock(WP_Block::class);
        $block->context = $context;

        return $block;
    }

    /**
     * Create a mock WP_User object.
     */
    private function createMockUser(int $id, string $displayName, string $description = ''): WP_User
    {
        $user = Mockery::mock(WP_User::class);
        $user->ID = $id;
        $user->display_name = $displayName;
        $user->description = $description;

        return $user;
    }
}
