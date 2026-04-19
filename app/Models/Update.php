<?php

/**
 * Update.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Stores release-note style announcements shown to authenticated users.
 */
class Update extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'link',
    ];
}
