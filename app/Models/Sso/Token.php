<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Models\Sso;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Token extends Model
{
    protected $table = 'sso_tokens';

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->client->touch();
        });
    }

    /**
     * Update last accessed date
     */
    public function touch()
    {
        $model = static::find($this->id);
        
        $model->timestamps = false;
        $model->last_accessed_at = now();

        $model->save();
    }
    
    /**
     * Get client for token
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get user for token
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
