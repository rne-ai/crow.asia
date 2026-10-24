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
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');

        Schema::create('supported_locales', function (Blueprint $table) {
            $table->string('code', 16)->primary();
            $table->string('name', 64);
            $table->string('native_name', 64);
            $table->string('flag_emoji', 16);
            $table->boolean('has_dual_register')->default(false);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_llm_translated')->default(false);
            $table->smallInteger('sort_order')->default(1);
            $table->timestamps();
        });

        DB::table('supported_locales')->insert([
            ['code' => 'id', 'name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag_emoji' => '🇮🇩', 'has_dual_register' => true, 'is_active' => true, 'is_llm_translated' => false, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag_emoji' => '🌐', 'has_dual_register' => false, 'is_active' => true, 'is_llm_translated' => false, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ms', 'name' => 'Malay', 'native_name' => 'Bahasa Melayu', 'flag_emoji' => '🇲🇾', 'has_dual_register' => false, 'is_active' => false, 'is_llm_translated' => false, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'flag_emoji' => '🇯🇵', 'has_dual_register' => false, 'is_active' => false, 'is_llm_translated' => false, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'flag_emoji' => '🇰🇷', 'has_dual_register' => false, 'is_active' => false, 'is_llm_translated' => false, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'zh-CN', 'name' => 'Simplified Chinese', 'native_name' => '简体中文', 'flag_emoji' => '🇨🇳', 'has_dual_register' => false, 'is_active' => false, 'is_llm_translated' => false, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'tl', 'name' => 'Tagalog', 'native_name' => 'Tagalog', 'flag_emoji' => '🇵🇭', 'has_dual_register' => false, 'is_active' => false, 'is_llm_translated' => false, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'flag_emoji' => '🇻🇳', 'has_dual_register' => false, 'is_active' => false, 'is_llm_translated' => false, 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'th', 'name' => 'Thai', 'native_name' => 'ภาษาไทย', 'flag_emoji' => '🇹🇭', 'has_dual_register' => false, 'is_active' => false, 'is_llm_translated' => false, 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supported_locales');
    }
};
