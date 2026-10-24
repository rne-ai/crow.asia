<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingTier extends Model
{
    protected $table = 'pricing_tiers';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'names',
        'descriptions',
        'features',
        'badges',
        'price_idr',
        'strike_price_idr',
        'price_usd',
        'included_book_ids',
        'sort_order',
        'is_best_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'names' => 'array',
            'descriptions' => 'array',
            'features' => 'array',
            'badges' => 'array',
            'price_idr' => 'integer',
            'strike_price_idr' => 'integer',
            'price_usd' => 'decimal:2',
            'included_book_ids' => 'array',
            'sort_order' => 'integer',
            'is_best_value' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function name(string $locale = 'id'): string
    {
        return $this->names[$locale] ?? $this->names['id'] ?? $this->id;
    }

    public function description(string $locale = 'id'): string
    {
        return $this->descriptions[$locale] ?? $this->descriptions['id'] ?? '';
    }

    /**
     * @return array<int, string>
     */
    public function features(string $locale = 'id'): array
    {
        $raw = $this->getAttribute('features');
        if (!is_array($raw)) {
            return [];
        }

        // Handle legacy flat array: ["feature 1", "feature 2"]
        if (array_is_list($raw)) {
            return $raw;
        }

        // Handle multi-locale object: {"id": [...], "en": [...]}
        $features = $raw[$locale] ?? $raw['id'] ?? (is_array(reset($raw)) ? reset($raw) : []);
        return is_array($features) ? array_values($features) : [];
    }

    public function includesBook(string $bookId): bool
    {
        return in_array($bookId, $this->included_book_ids ?? [], true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'pricing_tier_id', 'id');
    }
}
