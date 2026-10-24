<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->smallInteger('volume_number')->unique();
            $table->string('volume_code', 20);
            $table->integer('price_single_idr');
            $table->decimal('price_single_usd', 6, 2)->default(4.99);
            $table->string('accent_hex', 16)->default('#1D4ED8');
            $table->jsonb('titles');
            $table->jsonb('subtitles');
            $table->jsonb('target_roles');
            $table->jsonb('key_frameworks');
            $table->jsonb('pdf_r2_keys');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('chapters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('book_id', 32);
            $table->smallInteger('chapter_index');
            $table->string('slug', 32);
            $table->boolean('is_free_sample')->default(false);
            $table->string('motif_icon', 32)->default('compass');
            $table->string('illustration_path', 255)->nullable();
            
            // Engine Utama: Remotion Landscape 16:9 (1280x720)
            $table->string('remotion_composition_id', 64)->default('ChapterInteractiveStory16x9');
            $table->integer('remotion_duration_frames')->default(540);
            $table->smallInteger('remotion_fps')->default(30);

            // Modul Video YouTube (OPSIONAL, Default = false)
            $table->boolean('is_youtube_enabled')->default(false);
            $table->string('youtube_unlisted_id', 64)->nullable();

            $table->timestampsTz();

            $table->foreign('book_id')->references('id')->on('books')->cascadeOnDelete();
            $table->unique(['book_id', 'slug'], 'uq_book_chapter_slug');
            $table->unique(['book_id', 'chapter_index'], 'uq_book_chapter_index');
        });

        Schema::create('chapter_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chapter_id');
            $table->string('locale', 16);
            $table->string('register', 16)->default('standard');
            
            $table->string('title', 255);
            $table->text('illustration_caption')->nullable();

            $table->text('raw_markdown');
            $table->text('compiled_html');
            $table->text('safe_preview_html');
            $table->boolean('has_scripts')->default(false);
            $table->jsonb('extracted_checklists')->default('[]');
            $table->jsonb('remotion_scene_props')->default('{}');

            $table->string('translation_status', 20)->default('PUBLISHED');

            $table->timestampTz('compiled_at')->default(DB::raw('NOW()'));
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));

            $table->foreign('chapter_id')->references('id')->on('chapters')->cascadeOnDelete();
            $table->foreign('locale')->references('code')->on('supported_locales');
            $table->unique(['chapter_id', 'locale', 'register'], 'uq_chapter_locale_register');
        });

        DB::statement("ALTER TABLE chapter_translations ADD CONSTRAINT chk_chapter_translations_register CHECK (register IN ('casual', 'standard'));");
        DB::statement("ALTER TABLE chapter_translations ADD CONSTRAINT chk_chapter_translations_status CHECK (translation_status IN ('DRAFT', 'REVIEW', 'PUBLISHED'));");
        
        // Hybrid Search columns (tsvector & vector(768))
        DB::statement("ALTER TABLE chapter_translations ADD COLUMN search_vector tsvector NULL;");
        DB::statement("ALTER TABLE chapter_translations ADD COLUMN embedding vector(768) NULL;");

        // Indexes
        DB::statement("CREATE INDEX idx_chapter_translations_search ON chapter_translations USING GIN (search_vector);");
        DB::statement("CREATE INDEX idx_chapter_translations_embedding ON chapter_translations USING hnsw (embedding vector_cosine_ops);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_translations');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('books');
    }
};
