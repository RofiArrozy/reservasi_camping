<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel pivot paket <-> alat, lengkap dengan jumlah unit per alat dalam paket.
     */
    public function up(): void
    {
        Schema::create('package_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);

            $table->unique(['package_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_item');
    }
};
