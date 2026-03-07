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
        Schema::table('peminjaman', function (Blueprint $table) {
            // Payment tracking
            $table->enum('status_pembayaran', [
                'belum_bayar',
                'dp_dibayar', 
                'lunas'
            ])->default('belum_bayar')->after('status');
            
            $table->decimal('total_dp', 12, 2)->default(0)->after('total_harga');
            $table->decimal('total_dibayar', 12, 2)->default(0)->after('total_dp');
            $table->decimal('sisa_bayar', 12, 2)->default(0)->after('total_dibayar');

            // indexes
            $table->index('status_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn([
                'status_pembayaran',
                'total_dp',
                'total_dibayar',
                'sisa_bayar',
            ]);
        });
    }
};
