<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom tambahan untuk halaman Profil yang bisa diedit sendiri oleh pegawai.
 * Semua kolom nullable, jadi data lama tidak berubah.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto', 255)->nullable();
            $table->string('pendidikan', 255)->nullable();
            $table->string('keahlian', 255)->nullable();
            $table->text('tentang')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['foto', 'pendidikan', 'keahlian', 'tentang']);
        });
    }
};
