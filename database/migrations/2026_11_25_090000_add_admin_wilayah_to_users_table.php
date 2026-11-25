<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin wilayah: admin yang hanya mengelola Business Unit tertentu.
 * admin_wilayah berisi daftar id wilayah (JSON). Kosong = admin utama (semua wilayah), seperti sebelumnya.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('admin_wilayah')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_wilayah');
        });
    }
};
