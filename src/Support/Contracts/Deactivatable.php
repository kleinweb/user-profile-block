<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Support\Contracts;

interface Deactivatable
{
    public function deactivate(): void;
}
