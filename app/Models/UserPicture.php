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
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPicture extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'user_id';

    protected $touches = ['user'];

    protected $fillable = [
        'user_id', 'image_path', 'thumbnail_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
