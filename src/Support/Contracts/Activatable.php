<?php

declare(strict_types=1);

namespace Kleinweb\UserProfile\Support\Contracts;

interface Activatable
{
    public function activate(): void;
}
