<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log aktivitas pengguna SIPekan (masuk, keluar, gagal masuk, membuka halaman, mencetak).
 * Tabel baru; tidak mengubah tabel lain. Log > 90 hari dihapus otomatis (activity:prune).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('email', 255)->nullable();      // hanya untuk gagal masuk
            $table->string('action', 20)->index();          // login, logout, failed, page, print
            $table->string('route_name', 100)->nullable();
            $table->string('label', 150)->nullable();       // nama halaman yang mudah dibaca
            $table->string('ip', 45)->nullable();
            $table->string('device', 100)->nullable();      // mis. "Chrome · Windows"
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
