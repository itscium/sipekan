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
        Schema::create('fp_machine_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fp_machine_id');
            $table->string('pin');
            $table->dateTime('datetime');
            $table->string('verified');
            $table->string('status');
            $table->string('workcode');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fp_machine_log');
    }
};
