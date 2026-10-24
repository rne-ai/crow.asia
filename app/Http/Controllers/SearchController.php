<?php

namespace App\Http\Controllers;

use App\Enums\ContentRegister;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $locale = $request->query('locale', 'id');
        $regParam = $request->query('register', $request->query('reg', 'casual'));

        // Invariant: Non-ID locales are always 'standard'
        $register = ($locale === 'id')
            ? (($regParam === 'standard') ? ContentRegister::STANDARD->value : ContentRegister::CASUAL->value)
            : ContentRegister::STANDARD->value;

        /** @var User|null $user */
        $user = $request->user();

        $wildcardQuery = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';

        $results = DB::select("
            SELECT 
                c.book_id,
                b.volume_code,
                b.accent_hex,
                c.chapter_index,
                c.slug AS chapter_slug,
                ct.title AS chapter_title,
                c.is_free_sample,
                round(ts_rank_cd(ct.search_vector, websearch_to_tsquery('simple', :q))::numeric, 4) AS rank,
                ts_headline('simple', ct.raw_markdown, websearch_to_tsquery('simple', :q), 'StartSel=<mark>, StopSel=</mark>, MaxWords=35, MinWords=15, ShortWord=3, HighlightAll=FALSE, MaxFragments=1') AS snippet
            FROM chapter_translations ct
            JOIN chapters c ON c.id = ct.chapter_id
            JOIN books b ON b.id = c.book_id
            WHERE ct.translation_status = 'PUBLISHED'
              AND ct.locale = :locale
              AND ct.register = :register
              AND (ct.search_vector @@ websearch_to_tsquery('simple', :q) OR ct.title ILIKE :wildcard_q)
            ORDER BY rank DESC, c.chapter_index ASC
            LIMIT 8
        ", [
            'q' => $query,
            'wildcard_q' => $wildcardQuery,
            'locale' => $locale,
            'register' => $register,
        ]);

        $payload = array_map(function ($row) use ($user, $locale, $register) {
            $isUnlocked = (bool) $row->is_free_sample;
            if (!$isUnlocked && $user) {
                $isUnlocked = $user->isAdmin() || $user->hasAccessToBook($row->book_id);
            }

            $url = "/read/{$row->book_id}/{$locale}/{$row->chapter_slug}";
            if ($locale === 'id') {
                $url .= "?reg={$register}";
            }

            return [
                'book_id' => $row->book_id,
                'volume_code' => $row->volume_code,
                'accent_hex' => $row->accent_hex,
                'chapter_index' => (int) $row->chapter_index,
                'chapter_slug' => $row->chapter_slug,
                'chapter_title' => $row->chapter_title,
                'snippet' => $row->snippet,
                'is_free_sample' => (bool) $row->is_free_sample,
                'is_unlocked' => $isUnlocked,
                'url' => $url,
            ];
        }, $results);

        return response()->json($payload);
    }
}
