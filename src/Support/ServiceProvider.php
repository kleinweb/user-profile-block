<?php

declare(strict_types=1);

namespace Kleinweb\UserProfile\Support;

use DI\Container;

abstract class ServiceProvider
{
    abstract public function register(Container $container): void;
}
