<?php

namespace App\Models;

use App\Enums\ContentRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $table = 'books';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'volume_number',
        'volume_code',
        'price_single_idr',
        'price_single_usd',
        'accent_hex',
        'titles',
        'subtitles',
        'target_roles',
        'key_frameworks',
        'pdf_r2_keys',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'volume_number' => 'integer',
            'price_single_idr' => 'integer',
            'price_single_usd' => 'decimal:2',
            'titles' => 'array',
            'subtitles' => 'array',
            'target_roles' => 'array',
            'key_frameworks' => 'array',
            'pdf_r2_keys' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function title(string $locale = 'id', string|ContentRegister $register = 'casual'): string
    {
        $regStr = $register instanceof ContentRegister ? $register->value : $register;
        $key = "{$locale}:{$regStr}";

        return $this->titles[$key] ?? $this->titles["{$locale}:standard"] ?? reset($this->titles) ?: $this->volume_code;
    }

    public function subtitle(string $locale = 'id', string|ContentRegister $register = 'casual'): string
    {
        $regStr = $register instanceof ContentRegister ? $register->value : $register;
        $key = "{$locale}:{$regStr}";

        return $this->subtitles[$key] ?? $this->subtitles["{$locale}:standard"] ?? reset($this->subtitles) ?: '';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('volume_number');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'book_id', 'id')->orderBy('chapter_index');
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(UserEntitlement::class, 'book_id', 'id');
    }

    public function checklistProgress(): HasMany
    {
        return $this->hasMany(ChecklistProgress::class, 'book_id', 'id');
    }
}
