<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\EntitlementSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MayarWebhookController extends Controller
{
    public function __invoke(Request $request, EntitlementSyncService $syncService): JsonResponse
    {
        // 1. Verify Secret Callback Token using constant-time comparison
        $configuredToken = (string) config('services.mayar.webhook_token', 'crow_webhook_secret_123');
        $incomingToken = (string) $request->header('X-Callback-Token', $request->header('x-callback-token', ''));

        if (!hash_equals($configuredToken, $incomingToken)) {
            Log::warning('Mayar webhook signature mismatch', [
                'ip' => $request->ip(),
                'incoming_header_length' => strlen($incomingToken),
            ]);

            return response()->json(['error' => 'Unauthorized: Invalid callback token'], 401);
        }

        // 2. Extract Event & Payload details
        $payload = $request->all();
        $data = $request->input('data') ?? [];

        $invoiceId = $data['invoiceId'] ?? $data['id'] ?? null;
        $orderNumber = $data['orderNumber'] ?? $data['orderId'] ?? $data['externalId'] ?? null;
        $status = strtoupper((string) ($data['status'] ?? $request->input('event', '')));

        // 3. Locate Order
        $order = null;
        if ($orderNumber) {
            $order = Order::query()->where('order_number', $orderNumber)->first();
        }
        if (!$order && $invoiceId) {
            $order = Order::query()->where('mayar_invoice_id', $invoiceId)->first();
        }

        if (!$order) {
            Log::warning('Mayar webhook: Order not found', ['orderNumber' => $orderNumber, 'invoiceId' => $invoiceId]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        // 4. Redis Idempotency Lock (15s TTL)
        $lockKey = "mayar:webhook:{$order->id}";
        $lock = Cache::lock($lockKey, 15);

        return $lock->get(function () use ($order, $status, $data, $payload, $syncService) {
            // Idempotency check: if already PAID, return 200 without duplicate grants
            if ($order->isPaid()) {
                return response()->json([
                    'message' => 'Order already processed',
                    'order_number' => $order->order_number,
                    'status' => 'PAID',
                    'idempotent' => true,
                ], 200);
            }

            // Check if status is successful (PAID, SETTLED, SUCCESS, PAYMENT.RECEIVED)
            $isPaidEvent = in_array($status, ['PAID', 'SETTLED', 'SUCCESS', 'PAYMENT.RECEIVED', 'PAYMENT_SUCCESS'], true);

            if ($isPaidEvent) {
                $order->payment_status = PaymentStatus::PAID;
                $order->paid_at = now();
                $order->mayar_transaction_id = $data['transactionId'] ?? $order->mayar_transaction_id;
                $order->raw_webhook_payload = $payload;
                $order->save();

                // Sync entitlements (and auto-claim user if guest)
                $grantedBooks = $syncService->syncEntitlements($order);

                Log::info("Mayar webhook: Order {$order->order_number} marked as PAID. Entitlements granted: " . implode(',', $grantedBooks));

                return response()->json([
                    'status' => 'success',
                    'order_number' => $order->order_number,
                    'granted_books' => $grantedBooks,
                ], 200);
            }

            // Other statuses (EXPIRED, CLOSED, etc.)
            if (in_array($status, ['EXPIRED', 'FAILED', 'CANCELLED'], true)) {
                $order->payment_status = PaymentStatus::EXPIRED;
                $order->raw_webhook_payload = $payload;
                $order->save();
            }

            return response()->json([
                'status' => 'received',
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status->value,
            ], 200);
        });
    }
}
