<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function(Blueprint $t){
            $t->integer('id');
            $t->string('title', 1024);
        });

        Schema::create('regions', function(Blueprint $t){
            $t->integer('id');
            $t->integer('country_id');
            $t->string('title', 1024);
        });

        Schema::create('cities', function(Blueprint $t){
            $t->integer('id');
            $t->integer('country_id');
            $t->integer('region_id');
            $t->string('title', 1024);
        });

        Schema::create('universities', function(Blueprint $t){
            $t->integer('id');
            $t->integer('country_id');
            $t->integer('city_id');
            $t->string('title', 1024);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
