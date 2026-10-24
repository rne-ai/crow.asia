<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistProgress extends Model
{
    use HasUuids;

    protected $table = 'checklist_progress';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'book_id',
        'chapter_slug',
        'item_index',
        'is_completed',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'item_index' => 'integer',
            'is_completed' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeForChapter(Builder $query, string $bookId, string $chapterSlug): Builder
    {
        return $query->where('book_id', $bookId)->where('chapter_slug', $chapterSlug);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
