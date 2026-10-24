<?php

namespace Database\Seeders;

use App\Enums\ContentRegister;
use App\Models\User;
use App\Models\UserEntitlement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with Admin and Entitled User accounts.
     */
    public function run(): void
    {
        // 1. Super Admin Account
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@crow.asia'],
            [
                'name' => 'Admin crow.asia',
                'password' => Hash::make('janganmasuk'),
                'role' => 'admin',
                'preferred_locale' => 'id',
                'preferred_id_register' => ContentRegister::CASUAL,
                'email_verified_at' => now(),
            ]
        );

        // 2. Harry Rosyan Account with Lifetime All-Books Entitlements
        $harry = User::query()->updateOrCreate(
            ['email' => 'mharryrosyan@gmail.com'],
            [
                'name' => 'Harry Rosyan',
                'password' => Hash::make('janganmasuk'),
                'role' => 'reader',
                'preferred_locale' => 'id',
                'preferred_id_register' => ContentRegister::CASUAL,
                'email_verified_at' => now(),
            ]
        );

        // Grant access to all 3 playbooks
        $allBooks = ['book-1', 'book-2', 'book-3'];
        foreach ($allBooks as $bookId) {
            UserEntitlement::query()->firstOrCreate(
                [
                    'user_id' => $harry->id,
                    'book_id' => $bookId,
                ],
                [
                    'is_active' => true,
                    'granted_at' => now(),
                ]
            );
        }
    }
}
