<?php

namespace App\Services\Payment;

use App\Enums\ContentRegister;
use App\Models\Order;
use App\Models\User;
use App\Models\UserEntitlement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EntitlementSyncService
{
    /**
     * Synchronize book entitlements for a paid order, supporting guest auto-claim.
     *
     * @return array<int, string> List of granted book IDs
     */
    public function syncEntitlements(Order $order): array
    {
        // 1. Resolve or create user if order was placed as a guest
        $user = $order->user;
        if (!$user) {
            $user = User::query()->where('email', $order->customer_email)->first();
            if (!$user) {
                $user = User::query()->create([
                    'email' => $order->customer_email,
                    'name' => Str::before($order->customer_email, '@'),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'reader',
                    'preferred_locale' => 'id',
                    'preferred_id_register' => ContentRegister::CASUAL,
                    'email_verified_at' => now(),
                ]);
            }

            $order->user_id = $user->id;
            $order->save();
        }

        // 2. Grant access for each book included in the pricing tier
        $tier = $order->pricingTier;
        $includedBookIds = $tier ? ($tier->included_book_ids ?? []) : [];
        $granted = [];

        foreach ($includedBookIds as $bookId) {
            UserEntitlement::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'book_id' => $bookId,
                ],
                [
                    'order_id' => $order->id,
                    'is_active' => true,
                    'granted_at' => now(),
                ]
            );

            $granted[] = $bookId;
        }

        return $granted;
    }
}
