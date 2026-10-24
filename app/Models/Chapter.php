<?php

namespace App\Models;

use App\Enums\ContentRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $table = 'chapters';

    protected $fillable = [
        'book_id',
        'chapter_index',
        'slug',
        'is_free_sample',
        'motif_icon',
        'illustration_path',
        'remotion_composition_id',
        'remotion_duration_frames',
        'remotion_fps',
        'is_youtube_enabled',
        'youtube_unlisted_id',
    ];

    protected function casts(): array
    {
        return [
            'chapter_index' => 'integer',
            'is_free_sample' => 'boolean',
            'remotion_duration_frames' => 'integer',
            'remotion_fps' => 'integer',
            'is_youtube_enabled' => 'boolean',
        ];
    }

    public function scopeFreeSamples(Builder $query): Builder
    {
        return $query->where('is_free_sample', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('chapter_index');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ChapterTranslation::class, 'chapter_id', 'id');
    }

    public function translation(string $locale, string|ContentRegister $register = 'standard'): ?ChapterTranslation
    {
        $regStr = $register instanceof ContentRegister ? $register->value : $register;
        $resolvedReg = ($locale === 'id') ? $regStr : 'standard';

        return $this->translations
            ->where('locale', $locale)
            ->where('register', ContentRegister::from($resolvedReg))
            ->first();
    }
}
