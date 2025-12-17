<?php

declare(strict_types=1);

namespace Kleinweb\UserProfile\Container;

use DI\Container;
use DI\ContainerBuilder;
use Kleinweb\UserProfile\Support\Contracts\Activatable;
use Kleinweb\UserProfile\Support\Contracts\Bootable;
use Kleinweb\UserProfile\Support\Contracts\Deactivatable;
use Kleinweb\UserProfile\Support\ServiceProvider;

final class ServiceContainer
{
    private Container $container;

    /** @var list<class-string<ServiceProvider>> */
    private array $providers = [
        \Kleinweb\UserProfile\UserProfileServiceProvider::class,
        \Kleinweb\UserProfile\Meta\MetaServiceProvider::class,
        \Kleinweb\UserProfile\Blocks\BlocksServiceProvider::class,
    ];

    /** @var list<ServiceProvider> */
    private array $loadedProviders = [];

    private bool $booted = false;

    public function __construct()
    {
        $this->container = $this->buildContainer();
        $this->registerProviders();
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->loadedProviders as $provider) {
            if ($provider instanceof Bootable) {
                $provider->boot();
            }
        }

        $this->booted = true;
    }

    public function activate(): void
    {
        foreach ($this->loadedProviders as $provider) {
            if ($provider instanceof Activatable) {
                $provider->activate();
            }
        }

        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
        flush_rewrite_rules();
    }

    public function deactivate(): void
    {
        foreach ($this->loadedProviders as $provider) {
            if ($provider instanceof Deactivatable) {
                $provider->deactivate();
            }
        }

        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
        flush_rewrite_rules();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    public function get(string $id): object
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    private function buildContainer(): Container
    {
        $builder = new ContainerBuilder();

        $builder->useAutowiring(true);
        $builder->useAttributes(true);

        $definitionsFile = \Kleinweb\UserProfile\PLUGIN_DIR . '/config/container.php';
        if (file_exists($definitionsFile)) {
            $builder->addDefinitions($definitionsFile);
        }

        return $builder->build();
    }

    private function registerProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = $this->container->get($providerClass);
            $provider->register($this->container);
            $this->loadedProviders[] = $provider;
        }
    }
}
