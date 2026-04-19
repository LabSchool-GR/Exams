<?php

/**
 * GuestLayout.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the minimal layout used by guest-facing authentication pages.
 */
class GuestLayout extends Component
{
    /**
     * Return the Blade view used for guest pages.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
