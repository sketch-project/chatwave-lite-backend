<?php

namespace App\Models;

use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'message_type',
        'content',
        'reply_id',
        'media_id',
        'is_forwarded',
    ];

    protected $casts = [
        'message_type' => MessageType::class,
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_id', 'id');
    }

    public function referencedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_id', 'id');
    }
}
