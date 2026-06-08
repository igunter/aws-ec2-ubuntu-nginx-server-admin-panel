<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

class FtpAccount extends Authenticatable
{
    use HasUuids;

    protected $fillable = [
        'account_id',
        'username',
        'password',
        'root_directory',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    public function getAuthPassword(): string
    {
        return $this->password ?? '';
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
