<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MayarInvoiceService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.mayar.api_key', 'mock_key_crow_dev');
        $this->baseUrl = rtrim((string) config('services.mayar.base_url', 'https://api.mayar.id/hl/v2'), '/');
    }

    public function isMock(): bool
    {
        return empty($this->apiKey) || str_starts_with($this->apiKey, 'mock_');
    }

    /**
     * Create an invoice with Mayar.id or simulate in mock mode.
     *
     * @return array{invoice_id: string, payment_link: string}
     */
    public function createInvoice(Order $order): array
    {
        if ($this->isMock()) {
            $mockInvoiceId = 'inv_mock_' . Str::lower(Str::random(12));
            $mockPaymentLink = url("/checkout/mock-pay/{$order->order_number}");

            return [
                'invoice_id' => $mockInvoiceId,
                'payment_link' => $mockPaymentLink,
            ];
        }

        $tier = $order->pricingTier;
        $description = $tier ? $tier->name('id') : "Pembelian Playbook {$order->order_number}";

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/invoice/create", [
                'name' => Str::before($order->customer_email, '@'),
                'email' => $order->customer_email,
                'amount' => $order->amount_idr,
                'description' => $description,
                'redirectUrl' => url('/dashboard'),
                'mobile' => '081234567890',
            ]);

        if ($response->successful()) {
            $data = $response->json('data') ?? [];
            return [
                'invoice_id' => (string) ($data['id'] ?? 'inv_' . Str::random(10)),
                'payment_link' => (string) ($data['link'] ?? url('/dashboard')),
            ];
        }

        throw new \RuntimeException("Gagal membuat invoice Mayar: " . $response->body());
    }
}
