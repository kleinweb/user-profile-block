<?php

declare(strict_types=1);

namespace Kleinweb\UserProfile;

use DI\Container;
use Kleinweb\UserProfile\Support\Contracts\Bootable;
use Kleinweb\UserProfile\Support\ServiceProvider;
use Kleinweb\UserProfile\Users\UserProfileFields;

final class UserProfileServiceProvider extends ServiceProvider implements Bootable
{
    private UserProfileFields $profileFields;

    public function register(Container $container): void
    {
        $this->profileFields = $container->get(UserProfileFields::class);
    }

    public function boot(): void
    {
        load_plugin_textdomain(
            'user-profile-block',
            false,
            dirname(plugin_basename(PLUGIN_FILE)) . '/languages',
        );

        // Register user profile fields
        $this->profileFields->register();
    }
}
