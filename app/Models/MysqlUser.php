<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MysqlUser extends Model
{
    use HasUuids;

    protected $fillable = [
        'mysql_database_id',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function mysqlDatabase()
    {
        return $this->belongsTo(MysqlDatabase::class);
    }
}
