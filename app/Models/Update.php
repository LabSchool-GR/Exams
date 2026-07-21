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

    /**
     * Return only browser-safe external links, including for legacy records.
     */
    public function safeExternalLink(): ?string
    {
        $link = trim((string) $this->link);
        $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));

        return $link !== '' && in_array($scheme, ['http', 'https'], true)
            ? $link
            : null;
    }
}
