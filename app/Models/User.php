<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone_number',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function password(): Attribute
    {
        return Attribute::make(
            set: function (string $value) {
                $hasPassword = isset($this->original['password']);
                $rehashOnLogin = config('hashing.rehash_on_login');
                if ($hasPassword && $rehashOnLogin && Hash::needsRehash($this->original['password'])) {
                    // prevent double hashing when enable auto rehash on login
                    return $value;
                }

                return Hash::make($value);
            }
        );
    }

    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_participants', 'user_id', 'chat_id');
    }
}
