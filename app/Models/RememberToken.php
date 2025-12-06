<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RememberToken extends Model
{

    protected $table = 'remember_tokens';

    protected $fillable = [
        'user_id',
        'selector',
        'token_hash',
        'user_agent',
        'ip',
        'expires_at',
        'last_used_at',
        'revoked_at',
    ];

    protected $dates = [
        'expires_at',
        'last_used_at',
        'revoked_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
