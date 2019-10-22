<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id');

            $table->string('email');
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name')->nullable();

            $table->enum('type', ['musician', 'professional']);
            $table->string('artist_name');
            $table->longText('bio')->nullable();

            $table->date('dob')->nullable();
            $table->text('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('postal_code', 20);

            $table->longText('phone_numbers')->nullable();
            $table->longText('social_links')->nullable();
            
            $table->string('avatar')->nullable();
            $table->longText('thumbnail')->nullable();

            $table->boolean('admin')->default(false);
            
            $table->string('stripe_id')->nullable()->collation('utf8mb4_bin');
            $table->string('card_brand')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            
            $table->dateTime('confirmed_at')->nullable();
            $table->string('confirmation_code')->nullable();

            $table->rememberToken();
            $table->timestamps();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
