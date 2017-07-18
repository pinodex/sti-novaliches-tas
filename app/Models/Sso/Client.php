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

use DateTime;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidKey;
use App\Models\Group;
use App\Models\User;

class Client extends Model
{
    use UuidKey;

    protected $table = 'sso_clients';

    public $incrementing = false;

    protected $fillable = [
        'name'
    ];

    /**
     * Generate client secret
     * 
     * @return string
     */
    public function generateSecret()
    {
        $secret = hash('sha1', random_bytes(16));

        $this->secret = $secret;

        return $secret;
    }

    /**
     * Generate client token
     * 
     * @var \App\Models\User $user User object
     * @var \DateTime $expiresAt Token expiration
     * 
     * @return \App\Models\Sso\Token
     */
    public function generateToken(User $user, DateTime $expiresAt = null)
    {
        $token = new Token();

        $token->client_id = $this->id;
        $token->user_id = $user->id;
        $token->code = hash('sha1', random_bytes(32));

        if ($expiresAt) {
            $token->expires_at = $expiresAt->format('Y-m-d H:i:s');
        }

        return $token;
    }

    /**
     * Validate client secret
     * 
     * @param string $secret Client secret
     * 
     * @return true
     */
    public function validateSecret($secret)
    {
        return $this->secret == $secret;
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
     * Get the associated token models
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens()
    {
        return $this->hasMany(Token::class);
    }

    /**
     * Get client allowed groups
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allowedGroups()
    {
        return $this->belongsToMany(Group::class, 'sso_client_acls');
    }
}
