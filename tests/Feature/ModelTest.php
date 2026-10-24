<?php

use App\Enums\ContentRegister;
use App\Enums\PaymentStatus;
use App\Enums\TranslationStatus;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterTranslation;
use App\Models\ChecklistProgress;
use App\Models\Order;
use App\Models\PricingTier;
use App\Models\SupportedLocale;
use App\Models\User;
use App\Models\UserEntitlement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('supported locale model filters active locales and casts dual register correctly', function () {
    $activeLocales = SupportedLocale::active()->get();
    
    expect($activeLocales->count())->toBeGreaterThanOrEqual(2)
        ->and($activeLocales->pluck('code')->all())->toContain('id', 'en');

    $idLocale = SupportedLocale::where('code', 'id')->first();
    expect($idLocale->has_dual_register)->toBeTrue()
        ->and($idLocale->is_active)->toBeTrue();

    $enLocale = SupportedLocale::where('code', 'en')->first();
    expect($enLocale->has_dual_register)->toBeFalse();
});

test('user model casts register enum and provides role and entitlement helpers', function () {
    $user = User::create([
        'name' => 'Harry Rosyan',
        'email' => 'harry@example.com',
        'password' => 'secret123',
        'role' => 'admin',
        'preferred_locale' => 'id',
        'preferred_id_register' => ContentRegister::CASUAL,
    ]);

    expect($user->id)->not->toBeNull()
        ->and($user->preferred_id_register)->toBe(ContentRegister::CASUAL)
        ->and($user->isAdmin())->toBeTrue()
        ->and($user->isEditor())->toBeTrue();

    $book = Book::create([
        'id' => 'book-1',
        'volume_number' => 1,
        'volume_code' => 'VOL. 01',
        'price_single_idr' => 149000,
        'price_single_usd' => 9.99,
        'accent_hex' => '#1D4ED8',
        'titles' => ['id:casual' => 'Mahir Memimpin Tim', 'id:standard' => 'Mahir Memimpin Tim'],
        'subtitles' => ['id:casual' => 'Panduan Taktis', 'id:standard' => 'Panduan Taktis'],
        'target_roles' => ['Team Lead', 'Engineering Manager'],
        'key_frameworks' => ['Rumus 3K', 'Kalibrasi L1-L4'],
        'pdf_r2_keys' => ['a5' => 'pdf/vol-1-a5.pdf'],
        'is_active' => true,
    ]);

    expect($user->hasAccessToBook('book-1'))->toBeFalse();

    UserEntitlement::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'is_active' => true,
    ]);

    expect($user->hasAccessToBook($book))->toBeTrue();
});

test('book and chapter models handle json casts, relationships, and localized titles', function () {
    $book = Book::create([
        'id' => 'book-2',
        'volume_number' => 2,
        'volume_code' => 'VOL. 02',
        'price_single_idr' => 149000,
        'price_single_usd' => 9.99,
        'accent_hex' => '#EA580C',
        'titles' => [
            'id:casual' => 'Napas Panjang Memimpin Tim',
            'id:standard' => 'Napas Panjang Memimpin Tim (Standar)',
            'en:standard' => 'Endurance in Team Leadership',
        ],
        'subtitles' => [
            'id:casual' => 'Biar Nggak Boncos Energi',
            'id:standard' => 'Mencegah Kelelahan Kerja',
            'en:standard' => 'Anti-Burnout Playbook',
        ],
        'target_roles' => ['Senior Leader'],
        'key_frameworks' => ['Beban 30/70'],
        'pdf_r2_keys' => ['a5' => 'pdf/vol-2-a5.pdf'],
        'is_active' => true,
    ]);

    expect($book->title('id', ContentRegister::CASUAL))->toBe('Napas Panjang Memimpin Tim')
        ->and($book->title('id', ContentRegister::STANDARD))->toBe('Napas Panjang Memimpin Tim (Standar)')
        ->and($book->title('en'))->toBe('Endurance in Team Leadership');

    $chapter = Chapter::create([
        'book_id' => $book->id,
        'chapter_index' => 0,
        'slug' => 'ch-0',
        'is_free_sample' => true,
        'motif_icon' => 'compass',
        'remotion_composition_id' => 'ChapterInteractiveStory16x9',
        'remotion_duration_frames' => 540,
        'remotion_fps' => 30,
        'is_youtube_enabled' => false,
    ]);

    $translation = ChapterTranslation::create([
        'chapter_id' => $chapter->id,
        'locale' => 'id',
        'register' => ContentRegister::CASUAL,
        'title' => 'Pendahuluan: Kenapa Tim Lo Lemot?',
        'illustration_caption' => 'Sketsa awal transisi kepemimpinan',
        'raw_markdown' => '## Pendahuluan',
        'compiled_html' => '<p>Pendahuluan...</p>',
        'safe_preview_html' => '<p>Preview...</p>',
        'has_scripts' => true,
        'extracted_checklists' => [['itemIndex' => 0, 'text' => 'Refleksi awal']],
        'remotion_scene_props' => ['steps' => []],
        'translation_status' => TranslationStatus::PUBLISHED,
    ]);

    expect($chapter->book->id)->toBe('book-2')
        ->and($chapter->translations)->toHaveCount(1)
        ->and($chapter->translation('id', 'casual')->id)->toBe($translation->id)
        ->and($translation->register)->toBe(ContentRegister::CASUAL)
        ->and($translation->translation_status)->toBe(TranslationStatus::PUBLISHED)
        ->and($translation->has_scripts)->toBeTrue();
});

test('pricing tier, order, and progress models handle commerce workflows', function () {
    $tier = PricingTier::create([
        'id' => 'tier-bundle-3',
        'names' => ['id' => 'Paket Trilogi Lengkap', 'en' => 'Complete Trilogy Bundle'],
        'descriptions' => ['id' => 'Akses 3 Buku Sekaligus', 'en' => 'Full access to 3 books'],
        'features' => ['3 Playbook Penuh', 'Mode Animasi 16:9', 'Update Seumur Hidup'],
        'badges' => ['best_value' => true],
        'price_idr' => 299000,
        'strike_price_idr' => 447000,
        'price_usd' => 19.99,
        'included_book_ids' => ['book-1', 'book-2', 'book-3'],
        'sort_order' => 1,
        'is_best_value' => true,
        'is_active' => true,
    ]);

    expect($tier->name('id'))->toBe('Paket Trilogi Lengkap')
        ->and($tier->includesBook('book-1'))->toBeTrue()
        ->and($tier->includesBook('book-4'))->toBeFalse();

    $order = Order::create([
        'order_number' => 'CROW-2026-0001',
        'customer_email' => 'buyer@example.com',
        'pricing_tier_id' => $tier->id,
        'amount_idr' => 299000,
        'payment_status' => PaymentStatus::PAID,
        'paid_at' => now(),
    ]);

    expect($order->id)->not->toBeNull()
        ->and($order->isPaid())->toBeTrue()
        ->and(Order::paid()->count())->toBe(1);

    $user = User::create([
        'email' => 'buyer@example.com',
        'role' => 'reader',
        'preferred_locale' => 'id',
        'preferred_id_register' => 'casual',
    ]);

    $book = Book::create([
        'id' => 'book-1',
        'volume_number' => 1,
        'volume_code' => 'VOL. 01',
        'price_single_idr' => 149000,
        'price_single_usd' => 9.99,
        'accent_hex' => '#1D4ED8',
        'titles' => ['id:casual' => 'Mahir Memimpin Tim'],
        'subtitles' => ['id:casual' => 'Panduan Taktis'],
        'target_roles' => ['Team Lead'],
        'key_frameworks' => ['Rumus 3K'],
        'pdf_r2_keys' => ['a5' => 'pdf/vol-1-a5.pdf'],
        'is_active' => true,
    ]);

    $progress = ChecklistProgress::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'chapter_slug' => 'ch-7',
        'item_index' => 1,
        'is_completed' => true,
    ]);

    expect($progress->id)->not->toBeNull()
        ->and(ChecklistProgress::forChapter('book-1', 'ch-7')->count())->toBe(1);
});
