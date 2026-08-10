<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseModel extends Model
{
    use HasFactory;

    protected $table = 'databases';

    protected $fillable = [
        'user_id',
        'website_id',
        'name',
        'username',
        'host',
        'port',
        'db_name',
        'db_user',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function getNameAttribute($value)
    {
        return $value ?: ($this->attributes['db_name'] ?? '');
    }

    public function getUsernameAttribute($value)
    {
        return $value ?: ($this->attributes['db_user'] ?? '');
    }

    public function getHostAttribute($value)
    {
        return $value ?: '127.0.0.1';
    }

    public function getPortAttribute($value)
    {
        return $value ?: 3306;
    }
}
