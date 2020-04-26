<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoloEndorsementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solo_endorsements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('position_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('expiry_date');
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
        Schema::dropIfExists('solo_endorsements');
    }
}
