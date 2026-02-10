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
        Schema::create('alat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_alat', 20)->unique();
            $table->string('nama_alat', 100);
            $table->foreignId('id_kategori')->nullable()->constrained('kategori_alat')->onDelete('set null');
            $table->string('merk', 50)->nullable();
            $table->string('tipe', 50)->nullable();
            $table->string('serial_number', 50)->unique()->nullable();
            $table->year('tahun_pembelian')->nullable();
            $table->decimal('harga_beli', 12, 2)->nullable();
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->enum('status', ['tersedia', 'disewa', 'maintenance', 'rusak'])->default('tersedia'); 
            $table->json('foto')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->string('lokasi_penyimpanan', 50)->nullable();
            $table->timestamps();   

            // indexes
            $table->index('kode_alat');
            $table->index('status');
            $table->index('kondisi');
            $table->index('id_kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alat');
    }
};
