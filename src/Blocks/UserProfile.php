<?php

declare(strict_types=1);

namespace Kleinweb\UserProfile\Blocks;

use Kleinweb\UserProfile\Blocks\Attributes\Block;
use WP_Block;
use WP_User;

/**
 * User Profile Block.
 *
 * Displays user profile cards with social media icon links.
 * Supports Co-Authors Plus for multi-author posts.
 */
#[Block(name: 'user-profile')]
final class UserProfile
{
    /**
     * Render the block on the frontend.
     *
     * @param array<string, mixed> $attributes Block attributes from block.json
     * @param string               $content    Inner block content (unused)
     * @param WP_Block             $block      Block instance with context
     *
     * @phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- required by render_callback signature
     */
    public function render(array $attributes, string $content, WP_Block $block): string
    {
        unset($content);
        $users = $this->getUsers($attributes, $block);

        if ($users === []) {
            return '';
        }

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => 'wp-block-kleinweb-user-profile',
        ]);

        ob_start();
        ?>
        <div <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- from get_block_wrapper_attributes
            echo $wrapperAttributes;
        ?>>
            <?php foreach ($users as $user) { ?>
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderUserCard
                echo $this->renderUserCard($user, $attributes);
                ?>
            <?php } ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Get all users to display in the block.
     *
     * @param array<string, mixed> $attributes Block attributes
     * @param WP_Block             $block      Block instance
     *
     * @return list<WP_User>
     */
    private function getUsers(array $attributes, WP_Block $block): array
    {
        $users = [];
        $seenIds = [];
        $postId = $block->context['postId'] ?? get_the_ID();

        // Get post author(s) if enabled
        if ($attributes['usePostAuthor'] ?? true) {
            foreach ($this->getPostAuthors((int) $postId) as $author) {
                if (in_array($author->ID, $seenIds, true)) {
                    continue;
                }

                $users[] = $author;
                $seenIds[] = $author->ID;
            }
        }

        // Add specifically selected users
        $selectedIds = $attributes['selectedUserIds'] ?? [];
        foreach ($selectedIds as $userId) {
            if (in_array((int) $userId, $seenIds, true)) {
                continue;
            }

            $user = get_userdata((int) $userId);
            if (!($user instanceof WP_User)) {
                continue;
            }

            $users[] = $user;
            $seenIds[] = $user->ID;
        }

        return $users;
    }

    /**
     * Get authors for a post, with Co-Authors Plus support.
     *
     * @param int $postId The post ID
     *
     * @return list<WP_User>
     */
    private function getPostAuthors(int $postId): array
    {
        // Co-Authors Plus integration
        if (function_exists('get_coauthors')) {
            $coauthors = get_coauthors($postId);

            // Filter to only WP_User objects (CAP can return guest authors)
            return array_values(array_filter(
                $coauthors,
                static fn ($author): bool => $author instanceof WP_User,
            ));
        }

        // Fallback to standard WordPress author
        $authorId = (int) get_post_field('post_author', $postId);
        if ($authorId === 0) {
            return [];
        }

        $author = get_userdata($authorId);

        return $author instanceof WP_User ? [$author] : [];
    }

    /**
     * Render a single user card.
     *
     * @param WP_User              $user       The user to render
     * @param array<string, mixed> $attributes Block attributes
     */
    private function renderUserCard(WP_User $user, array $attributes): string
    {
        $socialLinks = $this->getUserSocialLinks($user);

        $shouldRender = $socialLinks !== [];
        if (!$shouldRender) {
            return '';
        }

        ob_start();
        ?>
        <article class="wp-block-kleinweb-user-profile__card">
            <h3 class="wp-block-kleinweb-user-profile__name">
                <?php echo __('Connect with', 'user-profile-block'); ?>
                <a href="<?php echo esc_url(get_author_posts_url($user->ID)); ?>">
                    <?php echo esc_html($user->display_name); ?>
                </a>
            </h3>

            <nav class="wp-block-kleinweb-user-profile__social"
                 aria-label="<?php echo esc_attr(
                     sprintf(
                         /* translators: %s: user display name */
                         __('Social links for %s', 'user-profile-block'),
                         $user->display_name,
                     ),
                 ); ?>">
                <?php foreach ($socialLinks as $metaKey => $url) { ?>
                    <?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderSocialLink
                    echo $this->renderSocialLink($metaKey, $url, $attributes);
                    ?>
                <?php } ?>
            </nav>
        </article>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Get social media links for a user.
     *
     * @param WP_User $user The user
     *
     * @return array<string, string> Map of meta key to URL
     */
    private function getUserSocialLinks(WP_User $user): array
    {
        $links = [];

        foreach (SocialIcons::getPlatformKeys() as $metaKey) {
            $url = get_user_meta($user->ID, $metaKey, true);
            if (is_string($url) && $url !== '') {
                $links[$metaKey] = $url;
            }
        }

        return $links;
    }

    /**
     * Render a single social media link.
     *
     * @param string               $metaKey    The meta key (e.g., 'linkedin_url')
     * @param string               $url        The URL
     * @param array<string, mixed> $attributes Block attributes
     */
    private function renderSocialLink(string $metaKey, string $url, array $attributes): string
    {
        if (!SocialIcons::isSupported($metaKey)) {
            return '';
        }

        $label = SocialIcons::getLabel($metaKey);
        $svg = SocialIcons::getSvg($metaKey);

        $baseClass = 'wp-block-kleinweb-user-profile__social-link';

        ob_start();
        ?>
        <a href="<?php echo esc_url($url); ?>"
           class="<?php echo esc_attr($baseClass); ?>"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="<?php echo esc_attr($label); ?>">
            <span class="wp-block-kleinweb-user-profile__social-icon">
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is from trusted source
                echo $svg;
        ?>
            </span>

            <span class="wp-block-kleinweb-user-profile__social-label screen-reader-text">
                <?php echo esc_html($label); ?>
            </span>
        </a>
        <?php

        return (string) ob_get_clean();
    }
}
