<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_topic_id',
        'user_id',
        'type',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CommunityTopic::class, 'community_topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
