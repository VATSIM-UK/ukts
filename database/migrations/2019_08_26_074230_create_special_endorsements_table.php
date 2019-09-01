<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialEndorsementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_endorsements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('special_endorsement_assignments', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('endorsement_id');
            $table->primary(['user_id', 'endorsement_id']);

            $table->unsignedInteger('granted_by');
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
        Schema::dropIfExists('special_endorsements');
        Schema::dropIfExists('special_endorsement_assignments');
    }
}
