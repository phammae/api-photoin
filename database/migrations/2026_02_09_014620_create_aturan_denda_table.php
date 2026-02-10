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
        Schema::create('aturan_denda', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_denda', [
                'keterlambatan',
                'kerusakan_ringan',
                'kerusakan_berat',
                'kehilangan_alat',
                'kehilangan_kelengkapan',
            ])->unique();
            $table->decimal('nominal', 10, 2);
            $table->string('satuan', 20)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index
            $table->index('jenis_denda');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aturan_denda');
    }
};
    