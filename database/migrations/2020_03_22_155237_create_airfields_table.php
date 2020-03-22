<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirfieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airfields', function (Blueprint $table) {
            $table->string('icao', 4);
            $table->string('iata', 3);
            $table->string('display_name');
            $table->primary('icao');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->string('airfield')->nullable();
            $table->foreign('airfield')->references('icao')->on('positions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('airfields');
    }
}
