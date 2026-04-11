<?php

/**
 * AppLayout.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the default authenticated application shell.
 */
class AppLayout extends Component
{
    /**
     * Return the Blade view used for authenticated pages.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}