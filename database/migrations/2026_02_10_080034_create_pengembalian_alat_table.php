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
        Schema::create('pengembalian_alat', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_peminjaman')->unique()->constrained('peminjaman_alat')->onDelete('restrict');
            $table->foreignId('id_petugas')->nullable()->constrained('users')->onDelete('set null');

            $table->date('tanggal_kembali');

            $table->integer('hari_terlambat')->default(0);

            $table->decimal('total_denda', 12, 2)->default(0);

            $table->text('catatan')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('id_peminjaman');
            $table->index('tanggal_kembali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalian_alat');
    }
};
