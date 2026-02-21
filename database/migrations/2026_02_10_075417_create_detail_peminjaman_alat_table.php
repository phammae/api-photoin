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
        Schema::create('detail_peminjaman_alat', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_peminjaman')->constrained('peminjaman_alat')->onDelete('cascade');
            $table->foreignId('id_alat')->constrained('alat')->onDelete('restrict');
            $table->foreignId('id_harga_sewa')->nullable()->constrained('harga_sewa')->onDelete('set null');

            $table->integer('durasi_hari');
            $table->decimal('harga_per_hari', 10, 2);
            $table->decimal('subtotal', 12, 2);

            $table->enum('kondisi_awal', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->text('catatan_kondisi_awal')->nullable();

            $table->timestamps();

             // Indexes
            $table->index('id_peminjaman');
            $table->index('id_alat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_peminjaman_alat');
    }
};
