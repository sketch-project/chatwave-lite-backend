<?php

namespace App\Models;

use App\Enums\ChatType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Chat extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'type',
        'name',
        'description',
    ];

    protected $casts = [
        'type' => ChatType::class,
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'chat_id', 'user_id')
            ->as('chatParticipants')
            ->withPivot('is_admin');
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $attributes['avatar']
                ? Storage::url($attributes['avatar'])
                : null
        );
    }
}
