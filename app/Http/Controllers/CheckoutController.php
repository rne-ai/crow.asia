<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PricingTier;
use App\Services\Payment\EntitlementSyncService;
use App\Services\Payment\MayarInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(string $tierId, Request $request): Response
    {
        $tier = PricingTier::query()->where('id', $tierId)->where('is_active', true)->firstOrFail();
        $locale = $request->query('locale', 'id');

        /** @var \App\Models\User|null $user */
        $user = $request->user();

        return Inertia::render('Checkout', [
            'tier' => [
                'id' => $tier->id,
                'name' => $tier->name($locale),
                'description' => $tier->description($locale),
                'priceIdr' => $tier->price_idr,
                'strikePriceIdr' => $tier->strike_price_idr,
                'priceUsd' => (float) $tier->price_usd,
                'features' => $tier->features,
                'isBestValue' => $tier->is_best_value,
            ],
            'locale' => $locale,
            'user' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
            ] : null,
        ]);
    }

    public function store(Request $request, MayarInvoiceService $invoiceService): RedirectResponse
    {
        $validated = $request->validate([
            'pricing_tier_id' => 'required|string|exists:pricing_tiers,id',
            'customer_email' => 'required|email|max:255',
            'customer_name' => 'nullable|string|max:120',
        ]);

        $tier = PricingTier::query()->findOrFail($validated['pricing_tier_id']);

        $orderNumber = 'CROW-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        /** @var \App\Models\User|null $user */
        $user = $request->user();

        $order = Order::query()->create([
            'order_number' => $orderNumber,
            'user_id' => $user?->id,
            'customer_email' => strtolower(trim($validated['customer_email'])),
            'pricing_tier_id' => $tier->id,
            'amount_idr' => $tier->price_idr,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $invoice = $invoiceService->createInvoice($order);

        $order->mayar_invoice_id = $invoice['invoice_id'];
        $order->payment_link = $invoice['payment_link'];
        $order->save();

        return redirect()->away($order->payment_link);
    }

    /**
     * Development mock payment simulator: instantly marks order as PAID and redirects to dashboard.
     */
    public function update(string $orderNumber, EntitlementSyncService $syncService): RedirectResponse
    {
        $order = Order::query()->where('order_number', $orderNumber)->firstOrFail();

        $order->payment_status = PaymentStatus::PAID;
        $order->paid_at = now();
        $order->save();

        $syncService->syncEntitlements($order);

        if (!Auth::check() && $order->user) {
            Auth::login($order->user);
        }

        return redirect()->route('dashboard')->with('success', "Pembayaran simulasi order {$order->order_number} berhasil!");
    }
}
