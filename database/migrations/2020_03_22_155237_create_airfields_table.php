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
            $table->string('airfield_icao')->nullable();
            $table->foreign('airfield_icao')->references('icao')->on('airfields');
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
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign('aircraft_icao');
            $table->dropColumn('aircraft_icao');
        });
    }
}
