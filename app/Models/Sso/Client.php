<?php

namespace App\Models\Sso;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidKey;

class Client extends Model
{
    use UuidKey;

    protected $table = 'sso_clients';

    public $incrementing = false;

    protected $fillable = [
        'name'
    ];

    public function generateSecret()
    {
        $secret = hash('sha1', random_bytes(16));

        $this->secret = $secret;

        return $secret;
    }
}
