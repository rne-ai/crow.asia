<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ChecklistProgress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $allBooks = Book::active()->get()->map(function ($b) use ($user) {
            $isUnlocked = $user->isAdmin() || $user->hasAccessToBook($b->id);
            return [
                'id' => $b->id,
                'volumeCode' => $b->volume_code,
                'title' => $b->title('id', 'casual'),
                'subtitle' => $b->subtitle('id', 'casual'),
                'accentHex' => $b->accent_hex,
                'priceSingleIdr' => $b->price_single_idr,
                'isUnlocked' => $isUnlocked,
            ];
        })->all();

        $recentOrders = Order::query()
            ->where('user_id', $user->id)
            ->with('pricingTier')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($o) => [
                'orderNumber' => $o->order_number,
                'tierName' => $o->pricingTier?->name('id') ?? 'Paket Playbook',
                'amountIdr' => $o->amount_idr,
                'status' => $o->payment_status->value,
                'paidAt' => $o->paid_at?->format('d M Y H:i'),
            ])->all();

        $checklistSummary = ChecklistProgress::query()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->selectRaw('book_id, count(*) as completed_count')
            ->groupBy('book_id')
            ->pluck('completed_count', 'book_id')
            ->all();

        return Inertia::render('Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'allBooks' => $allBooks,
            'recentOrders' => $recentOrders,
            'checklistSummary' => $checklistSummary,
        ]);
    }
}
