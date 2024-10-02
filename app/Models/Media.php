<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'file_path',
        'file_name',
        'mime_type',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $attributes['file_path']
                ? route('static-asset', $attributes['file_path'])
                : null
        );
    }
}
