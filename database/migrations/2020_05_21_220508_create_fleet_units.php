<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFleetUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fleet_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('back_look');
            $table->string('fleet_model');
            $table->string('front_look');
            $table->string('no_pol');
            $table->string('service_book');
            $table->string('stnk');
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
        Schema::dropIfExists('fleet_units');
    }
}
