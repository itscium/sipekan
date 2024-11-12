<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wilayah', function (Blueprint $table) {
            $table->string('english_name')->nullable();
            $table->string('english_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wilayah', function (Blueprint $table) {
            $table->dropColumn('english_name');
            $table->dropColumn('english_code');
        });
    }
};
