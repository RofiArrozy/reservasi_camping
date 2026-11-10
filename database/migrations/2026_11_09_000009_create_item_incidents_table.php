<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pencatatan alat rusak/hilang saat pengembalian.
     * replacement_cost = total tagihan ganti rugi yang wajib dibayar penyewa
     * (umumnya items.replacement_cost x quantity, dapat disesuaikan staff).
     */
    public function up(): void
    {
        Schema::create('item_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('type', ['damaged', 'lost']);
            $table->unsignedInteger('quantity')->default(1);
            $table->text('description')->nullable();
            $table->unsignedInteger('replacement_cost')->default(0);

            $table->enum('status', ['reported', 'billed', 'paid', 'waived'])->default('reported');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_incidents');
    }
};
