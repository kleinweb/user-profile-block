<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-2.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Support\Contracts;

interface Activatable
{
    public function activate(): void;
}
