<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportedLocale extends Model
{
    protected $table = 'supported_locales';

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag_emoji',
        'has_dual_register',
        'is_active',
        'is_llm_translated',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'has_dual_register' => 'boolean',
            'is_active' => 'boolean',
            'is_llm_translated' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function chapterTranslations(): HasMany
    {
        return $this->hasMany(ChapterTranslation::class, 'locale', 'code');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'preferred_locale', 'code');
    }
}
