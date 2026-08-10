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
        'db_name',
        'db_user',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
