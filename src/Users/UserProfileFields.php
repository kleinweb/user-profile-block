<?php

declare(strict_types=1);

namespace Kleinweb\UserProfile\Users;

use Kleinweb\UserProfile\Meta\Attributes\Meta;
use ReflectionClass;
use WP_User;

/**
 * User social media meta definitions and profile page fields.
 */
final class UserProfileFields
{
    /** @var array<string, Meta>|null Cached meta fields */
    private ?array $metaFields = null;

    #[Meta(
        key: 'linkedin_url',
        objectType: 'user',
        type: 'string',
        label: 'LinkedIn',
        description: 'LinkedIn profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $linkedin = '';

    #[Meta(
        key: 'instagram_url',
        objectType: 'user',
        type: 'string',
        label: 'Instagram',
        description: 'Instagram profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $instagram = '';

    #[Meta(
        key: 'twitter_url',
        objectType: 'user',
        type: 'string',
        label: 'Twitter (X)',
        description: 'Twitter (X) profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $twitter = '';

    #[Meta(
        key: 'facebook_url',
        objectType: 'user',
        type: 'string',
        label: 'Facebook',
        description: 'Facebook profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $facebook = '';

    #[Meta(
        key: 'tiktok_url',
        objectType: 'user',
        type: 'string',
        label: 'TikTok',
        description: 'TikTok profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $tiktok = '';

    #[Meta(
        key: 'youtube_url',
        objectType: 'user',
        type: 'string',
        label: 'YouTube',
        description: 'YouTube profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $youtube = '';

    #[Meta(
        key: 'threads_url',
        objectType: 'user',
        type: 'string',
        label: 'Threads',
        description: 'Threads profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $threads = '';

    #[Meta(
        key: 'bluesky_url',
        objectType: 'user',
        type: 'string',
        label: 'Bluesky',
        description: 'Bluesky profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $bluesky = '';

    #[Meta(
        key: 'substack_url',
        objectType: 'user',
        type: 'string',
        label: 'Substack',
        description: 'Substack profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $substack = '';

    #[Meta(
        key: 'medium_url',
        objectType: 'user',
        type: 'string',
        label: 'Medium',
        description: 'Medium profile URL',
        inputType: 'text',
        showInEditor: true,
    )]
    public string $medium = '';

    /**
     * Register hooks for displaying and saving profile fields.
     */
    public function register(): void
    {
        add_action('show_user_profile', [$this, 'renderFields']);
        add_action('edit_user_profile', [$this, 'renderFields']);
        add_action('personal_options_update', [$this, 'saveFields']);
        add_action('edit_user_profile_update', [$this, 'saveFields']);
    }

    /**
     * Render the social media fields on the user profile page.
     */
    public function renderFields(WP_User $user): void
    {
        ?>
        <h2><?php esc_html_e('Social Media Profiles', 'user-profile-block'); ?></h2>
        <p class="description">
            <?php
            // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
            esc_html_e('Enter your social media profile URLs. These will be displayed in the User Profile block.', 'user-profile-block');
        ?>
        </p>

        <table class="form-table" role="presentation">
            <?php foreach ($this->getMetaFields() as $meta) { ?>
                <?php $this->renderField($user, $meta); ?>
            <?php } ?>
        </table>
        <?php
    }

    /**
     * Render a single social media field.
     */
    private function renderField(WP_User $user, Meta $meta): void
    {
        $value = get_user_meta($user->ID, $meta->key, true);
        $fieldId = 'user_profile_' . $meta->key;
        ?>
        <tr>
            <th>
                <label for="<?php echo esc_attr($fieldId); ?>">
                    <?php echo esc_html($meta->label); ?>
                </label>
            </th>
            <td>
                <?php
                $placeholder = sprintf('https://%s.com/...', strtolower($meta->label));
        ?>
                <input
                    type="url"
                    name="<?php echo esc_attr($meta->key); ?>"
                    id="<?php echo esc_attr($fieldId); ?>"
                    value="<?php echo esc_attr((string) $value); ?>"
                    class="regular-text"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                />
            </td>
        </tr>
        <?php
    }

    /**
     * Save the social media fields when the profile is updated.
     */
    public function saveFields(int $userId): void
    {
        if (!current_user_can('edit_user', $userId)) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by WordPress profile handler
        if (!isset($_POST['_wpnonce'])) {
            return;
        }

        foreach ($this->getMetaFields() as $meta) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by WordPress profile handler
            if (!isset($_POST[$meta->key])) {
                continue;
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by WordPress profile handler
            $value = sanitize_url(wp_unslash($_POST[$meta->key]));

            if ($value === '') {
                delete_user_meta($userId, $meta->key);
            } else {
                update_user_meta($userId, $meta->key, $value);
            }
        }
    }

    /**
     * Get meta field definitions from class attributes.
     *
     * @return array<string, Meta>
     */
    private function getMetaFields(): array
    {
        if ($this->metaFields !== null) {
            return $this->metaFields;
        }

        $this->metaFields = [];
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Meta::class);
            foreach ($attributes as $attribute) {
                $meta = $attribute->newInstance();
                $this->metaFields[$meta->key] = $meta;
            }
        }

        return $this->metaFields;
    }
}
