<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - announcements: pengumuman di Beranda, ditujukan ke Business Unit tertentu.
 * - finance_locks: Business Unit yang laporan keuangannya ditutup (satu baris = ditutup).
 * Hanya menambah tabel baru; tidak mengubah tabel lain.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('body');
            $table->string('button_text', 100)->nullable();
            $table->string('button_url', 500)->nullable();
            $table->string('level', 20)->default('penting');   // penting | info
            $table->string('display', 20)->default('both');    // both (pop-up + banner) | banner
            $table->text('target_wilayah');                     // JSON array ID wilayah
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_locks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wilayah_id')->unique();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('finance_locks');
        Schema::dropIfExists('announcements');
    }
};
