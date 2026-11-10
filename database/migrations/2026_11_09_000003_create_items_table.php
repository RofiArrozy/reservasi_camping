<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alat camping yang disewakan.
     * - total_stock       : jumlah unit yang dimiliki
     * - daily_rate        : tarif sewa per hari (Rupiah)
     * - replacement_cost  : biaya ganti rugi per unit jika alat rusak/hilang
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('total_stock')->default(0);
            $table->unsignedInteger('daily_rate');
            $table->unsignedInteger('replacement_cost')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
