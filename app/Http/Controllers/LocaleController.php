<?php

/**
 * LocaleController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Persist the requested UI locale and return the user to the previous page.
     */
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['el', 'en'], true), 400);

        $request->session()->put('locale', $locale);

        return back();
    }
}
