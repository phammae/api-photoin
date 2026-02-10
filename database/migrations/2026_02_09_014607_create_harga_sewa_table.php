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
        Schema::create('harga_sewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alat')->constrained('alat')->onDelete('cascade');
            $table->integer('durasi_hari');
            $table->decimal('harga', 10, 2);
            $table->boolean('is_paket')->default(false);
            $table->string('nama_paket', 100)->nullable();
            $table->text('deskripsi_paket')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // indexes
            $table->index('id_alat');
            $table->index('durasi_hari');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_sewa');
    }
};
