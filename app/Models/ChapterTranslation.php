<?php

namespace App\Models;

use App\Enums\ContentRegister;
use App\Enums\TranslationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterTranslation extends Model
{
    const CREATED_AT = 'compiled_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'chapter_translations';

    protected $fillable = [
        'chapter_id',
        'locale',
        'register',
        'title',
        'illustration_caption',
        'raw_markdown',
        'compiled_html',
        'safe_preview_html',
        'has_scripts',
        'extracted_checklists',
        'remotion_scene_props',
        'translation_status',
        'search_vector',
        'embedding',
        'compiled_at',
    ];

    protected function casts(): array
    {
        return [
            'register' => ContentRegister::class,
            'has_scripts' => 'boolean',
            'extracted_checklists' => 'array',
            'remotion_scene_props' => 'array',
            'translation_status' => TranslationStatus::class,
            'compiled_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('translation_status', TranslationStatus::PUBLISHED);
    }

    public function scopeForLocaleAndRegister(Builder $query, string $locale, string|ContentRegister $register = 'standard'): Builder
    {
        $regStr = $register instanceof ContentRegister ? $register->value : $register;
        $resolvedReg = ($locale === 'id') ? $regStr : 'standard';

        return $query->where('locale', $locale)->where('register', $resolvedReg);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id', 'id');
    }

    public function supportedLocale(): BelongsTo
    {
        return $this->belongsTo(SupportedLocale::class, 'locale', 'code');
    }
}
