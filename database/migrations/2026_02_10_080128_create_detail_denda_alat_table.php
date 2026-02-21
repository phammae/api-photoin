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
        Schema::create('detail_denda_alat', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_pengembalian')->constrained('pengembalian_alat')->onDelete('cascade');
            $table->foreignId('id_alat')->constrained('alat')->onDelete('restrict');
            $table->foreignId('id_aturan_denda')->nullable()->constrained('aturan_denda')->onDelete('set null');

            $table->enum('jenis_denda', [
                'keterlambatan',
                'kerusakan_ringan',
                'kerusakan_berat',
                'kehilangan_alat',
                'kehilangan_kelengkapan',
            ]);

            $table->integer('jumlah_hari')->default(0);

            $table->decimal('nominal_per_satuan', 10, 2);
            $table->decimal('total_nominal', 12, 2);

            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('id_pengembalian');
            $table->index('id_alat');
            $table->index('jenis_denda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_denda_alat');
    }
};
