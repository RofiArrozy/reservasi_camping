<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Header reservasi/penyewaan.
     * user_id nullable agar pengunjung tanpa akun tetap bisa memesan
     * (dilacak lewat booking_code + email/telepon).
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->string('qr_token', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Data kontak penyewa
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20);

            // Periode sewa
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('total_days');

            // Biaya (Rupiah)
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('deposit_amount')->default(0);
            $table->unsignedInteger('total_amount');

            // Status
            $table->enum('status', ['pending', 'confirmed', 'picked_up', 'completed', 'cancelled'])
                  ->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable(); // dummy / sandbox

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indeks untuk validasi stok per rentang tanggal & pelacakan booking
            $table->index(['start_date', 'end_date', 'status']);
            $table->index('customer_email');
            $table->index('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
