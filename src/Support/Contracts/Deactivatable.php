<?php

declare(strict_types=1);

namespace Kleinweb\UserProfile\Support\Contracts;

interface Deactivatable
{
    public function deactivate(): void;
}
