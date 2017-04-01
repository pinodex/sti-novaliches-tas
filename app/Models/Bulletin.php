<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bulletin extends Model
{
    protected $fillable = [
        'author_id', 'last_author_id', 'title', 'contents'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastAuthor()
    {
        return $this->belongsTo(User::class, 'last_author_id');
    }
}
