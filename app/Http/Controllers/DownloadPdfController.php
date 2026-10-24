<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Storage;

class DownloadPdfController extends Controller
{
    public function __invoke(string $bookId, Request $request): RedirectResponse|HttpResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu untuk mengunduh master PDF.');
        }

        $book = Book::query()->where('id', $bookId)->where('is_active', true)->firstOrFail();

        if (!$user->isAdmin() && !$user->hasAccessToBook($bookId)) {
            abort(403, 'Anda belum memiliki hak akses resmi untuk mengunduh PDF playbook ini.');
        }

        $pdfKey = $book->pdf_r2_keys['a5'] ?? "playbooks/{$bookId}-a5.pdf";

        // If Cloudflare R2 disk is configured and key exists, return 15-minute Presigned URL
        try {
            if (config('filesystems.disks.r2')) {
                $presignedUrl = Storage::disk('r2')->temporaryUrl($pdfKey, now()->addMinutes(15));
                return redirect()->away($presignedUrl);
            }
        } catch (\Throwable) {
            // Fallback for local development
        }

        // Development fallback: return downloadable PDF text attachment
        $filename = "crow-asia-{$bookId}-a5.pdf";
        return response("PDF Master Playbook: {$book->volume_code} — {$book->title('id', 'standard')}", 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
