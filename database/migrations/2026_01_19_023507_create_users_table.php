<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('username', 50)->unique()->nullable();
            $table->string('password')->nullable();

            $table->string('email', 100)->unique();
            $table->string('nama_lengkap', 100);

            $table->enum('role', ['admin', 'petugas', 'penyewa'])->default('penyewa');
            $table->enum('status', ['aktif', 'nonaktif', 'suspended'])->default('aktif');

            $table->string('no_hp', 15)->nullable();
            $table->string('foto')->nullable();
            $table->text('alamat')->nullable();

            // Email verification
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_token')->unique()->nullable();

            // OAuth
            $table->string('google_id', 100)->unique()->nullable();
            $table->enum('oauth_provider', ['google', 'facebook', 'apple'])->nullable();

            $table->timestamps();

            // non-unique indexes
            $table->index('role');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
