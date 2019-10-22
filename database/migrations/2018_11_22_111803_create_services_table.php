<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');

            $table->unsignedSmallInteger('category_id');
            $table->unsignedSmallInteger('sub_category_id');

            $table->string('title');
            $table->longText('description');
            $table->longText('about');
            $table->longText('key_points');

            $table->float('price', 7, 2);
            $table->integer('delivery_time');
            $table->enum('delivery_time_unit', ['hour', 'week', 'day'])->default('hour');

            $table->float('rating', 2, 1)->default(0.0);
            $table->integer('review_count')->default(0);

            $table->boolean('approved')->default(false);
            $table->boolean('featured')->default(false);

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
        Schema::dropIfExists('services');
    }
}
