<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MysqlDatabase extends Model
{
    use HasUuids;

    protected $fillable = [
        'account_id',
        'name',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function mysqlUsers()
    {
        return $this->hasMany(MysqlUser::class);
    }
}
