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
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->jsonb('names');
            $table->jsonb('descriptions');
            $table->jsonb('features');
            $table->jsonb('badges')->nullable();
            $table->integer('price_idr');
            $table->integer('strike_price_idr')->nullable();
            $table->decimal('price_usd', 6, 2);
            $table->jsonb('included_book_ids');
            $table->smallInteger('sort_order')->default(1);
            $table->boolean('is_best_value')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuidv7()'));
            $table->string('order_number', 64)->unique();
            $table->uuid('user_id')->nullable();
            $table->string('customer_email', 255)->index('idx_orders_customer_email');
            $table->string('pricing_tier_id', 32);
            $table->integer('amount_idr');
            $table->string('mayar_invoice_id', 128)->nullable()->unique();
            $table->string('mayar_transaction_id', 128)->nullable()->unique();
            $table->text('payment_link')->nullable();
            $table->string('payment_status', 20)->default('PENDING');
            $table->jsonb('raw_webhook_payload')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers');
        });

        DB::statement("ALTER TABLE orders ADD CONSTRAINT chk_orders_payment_status CHECK (payment_status IN ('PENDING', 'PAID', 'CLOSED', 'EXPIRED'));");

        Schema::create('user_entitlements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuidv7()'));
            $table->uuid('user_id');
            $table->string('book_id', 32);
            $table->uuid('order_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampTz('granted_at')->default(DB::raw('NOW()'));

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('book_id')->references('id')->on('books')->restrictOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->unique(['user_id', 'book_id'], 'uq_user_book');
        });

        Schema::create('checklist_progress', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuidv7()'));
            $table->uuid('user_id');
            $table->string('book_id', 32);
            $table->string('chapter_slug', 32);
            $table->smallInteger('item_index');
            $table->boolean('is_completed')->default(false);
            $table->timestampTz('updated_at')->default(DB::raw('NOW()'));

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('book_id')->references('id')->on('books')->cascadeOnDelete();
            $table->unique(['user_id', 'book_id', 'chapter_slug', 'item_index'], 'uq_user_checklist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_progress');
        Schema::dropIfExists('user_entitlements');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('pricing_tiers');
    }
};
