<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZarplatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zarplatas', function (Blueprint $table) {
            $table->id();
            $table->string('opv');
            $table->string('vosms');
            $table->string('osms');
            $table->string('so');
            $table->string('zp_na_ruki');
            $table->string('zp');
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
        Schema::dropIfExists('zarplatas');
    }
}
