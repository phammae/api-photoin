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
        Schema::create('peminjaman_alat', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('id_penyewa')->constrained('users')->onDelete('restrict');
            $table->foreignId('id_petugas')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('kode_peminjaman', 20)->unique();
            
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_rencana');
            $table->date('tanggal_kembali_aktual')->nullable();
            
            
            $table->enum('status', [
                'menunggu',
                'disetejui',
                'ditolak',
                'dipinjam',
                'dikembalikan',
                'dibatalkan',
                ])->default('menunggu');

            $table->decimal('total_harga', 12, 2)->default(0);
            $table->decimal('total_denda', 12, 2)->default(0);

            $table->text('catatan_penyewa')->nullable();
            $table->text('catatan_petugas')->nullable();
            $table->text('alasan_ditolak')->nullable();
            
            
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('disetujui_at')->nullable();

            $table->timestamps();

             // Indexes
            $table->index('id_penyewa');
            $table->index('id_petugas');
            $table->index('kode_peminjaman');
            $table->index('status');
            $table->index('tanggal_pinjam');
            $table->index('tanggal_kembali_rencana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_alat');
    }
};
