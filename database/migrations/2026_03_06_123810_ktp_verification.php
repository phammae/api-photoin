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
        Schema::table('users', function (Blueprint $table) {
            // KTP Verification table
            $table->string('ktp_photo')->nullable()->after('foto');
            $table->string('ktp_selfie_photo')->nullable()->after('ktp_photo');
            $table->string('nik', 16)->nullable()->unique()->after('ktp_selfie_photo');

            // Verification status
            $table->string('is_verified')->default(false)->after('nik');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->after('verified_at');
            $table->text('verification_notes')->nullable()->after('verified_by');

            // indexes
            $table->index('is_verified');
            $table->index('nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'ktp_photo',
                'ktp_selfie_photo',
                'nik',
                'is_verified',
                'verified_at',
                'verified_by',
                'verification_notes',
            ]);
        });
    }
};
