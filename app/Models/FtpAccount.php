<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtpAccount extends Model
{
    protected $fillable = [
        'account_id',
        'username',
        'password',
        'root_directory',
        'is_active',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
