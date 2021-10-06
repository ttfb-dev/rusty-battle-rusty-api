<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRobotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('robots', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('owner');
            $table->unsignedBigInteger('battle_id');
            $table->json('modules')->nullable();
            $table->integer('health_base');
            $table->integer('energy_base');
            $table->integer('health_max');
            $table->integer('energy_max');
            $table->integer('health');
            $table->integer('energy');
            $table->string('status', 20);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('robots');
    }
}
