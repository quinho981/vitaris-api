<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'unsubscription_at',
        'active'
    ];

    public function plan(): BelongsTo 
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class);
    }   
}
