<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_peminjaman')->constrained('peminjaman')->onDelete('restrict');

            // Jenis pembayaran DP/Pelunasan
            $table->enum('jenis_pembayaran', ['dp', 'pelunasan']);
            $table->decimal('jumlah', 12, 2);

            // Metode & status
            $table->string('metode_pembayaran', 50)->nullable(); 
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');

            // Midtrans
            $table->string('midtrans_order_id', 100)->unique()->nullable();
            $table->string('midtrans_transaction_id', 100)->nullable();
            $table->string('midtrans_payment_type', 50)->nullable();
            $table->text('midtrans_snap_token')->nullable();
            $table->text('midtrans_snap_url')->nullable();

            // 
            $table->timestamp('tanggal_bayar')->nullable();
            $table->timestamp('tanggal_expired')->nullable();
            
            $table->text('catatan')->nullable();

            $table->timestamps();

            
            // Indexes
            $table->index('id_peminjaman');
            $table->index('jenis_pembayaran');
            $table->index('status');
            $table->index('midtrans_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
