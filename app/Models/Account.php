<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasUuids;

    protected $fillable = [
        'is_active',
        'ssl',
        'laravel',
        'domain',
        'slug',
        'email',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ssl'       => 'boolean',
        'laravel'   => 'boolean',
    ];

    public function ftpAccounts()
    {
        return $this->hasMany(FtpAccount::class);
    }
}
