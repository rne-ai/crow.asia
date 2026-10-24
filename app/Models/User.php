<?php

namespace App\Models;

use App\Enums\ContentRegister;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'preferred_locale',
        'preferred_id_register',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferred_id_register' => ContentRegister::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['editor', 'admin'], true);
    }

    public function hasAccessToBook(string|Book $book): bool
    {
        $bookId = $book instanceof Book ? $book->id : $book;

        return $this->entitlements()
            ->where('book_id', $bookId)
            ->where('is_active', true)
            ->exists();
    }

    public function preferredLocale(): BelongsTo
    {
        return $this->belongsTo(SupportedLocale::class, 'preferred_locale', 'code');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(UserEntitlement::class, 'user_id', 'id');
    }

    public function checklistProgress(): HasMany
    {
        return $this->hasMany(ChecklistProgress::class, 'user_id', 'id');
    }
}
