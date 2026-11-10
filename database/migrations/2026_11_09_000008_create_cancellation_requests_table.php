<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengajuan pembatalan oleh user dan keputusan staff.
     * Pengembalian dana hanya berupa pencatatan deposit standar (tanpa persentase).
     */
    public function up(): void
    {
        Schema::create('cancellation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('staff_note')->nullable();

            // Catatan pengembalian deposit (tidak ada transfer nyata)
            $table->unsignedInteger('deposit_refund_amount')->default(0);
            $table->enum('deposit_refund_status', ['none', 'refunded'])->default('none');

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_requests');
    }
};
