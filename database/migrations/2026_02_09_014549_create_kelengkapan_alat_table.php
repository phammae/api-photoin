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
        Schema::create('kelengkapan_alat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alat')->constrained('alat')->onDelete('cascade');
            $table->string('nama_kelengkapan', 100);
            $table->integer('jumlah')->default(1);
            $table->enum('kondisi', ['baik', 'rusak'])->default('baik');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // indexes
            $table->index('id_alat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelengkapan_alat');
    }
};
