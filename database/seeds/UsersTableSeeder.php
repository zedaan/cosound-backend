<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['email' => 'user1@example.com', 'password' => '123456', 'first_name' => 'first1', 'last_name' => 'last1', 'type' => 'musician', 'artist_name' => 'artist1', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user2@example.com', 'password' => '123456', 'first_name' => 'first2', 'last_name' => 'last2', 'type' => 'musician', 'artist_name' => 'artist2', 'country_id' => '14', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user3@example.com', 'password' => '123456', 'first_name' => 'first3', 'last_name' => 'last3', 'type' => 'professional', 'artist_name' => 'artist3', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user4@example.com', 'password' => '123456', 'first_name' => 'first4', 'last_name' => 'last4', 'type' => 'musician', 'artist_name' => 'artist4', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user5@example.com', 'password' => '123456', 'first_name' => 'first5', 'last_name' => 'last5', 'type' => 'musician', 'artist_name' => 'artist5', 'country_id' => '22', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user6@example.com', 'password' => '123456', 'first_name' => 'first6', 'last_name' => 'last6', 'type' => 'musician', 'artist_name' => 'artist6', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user7@example.com', 'password' => '123456', 'first_name' => 'first7', 'last_name' => 'last7', 'type' => 'musician', 'artist_name' => 'artist7', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user8@example.com', 'password' => '123456', 'first_name' => 'first8', 'last_name' => 'last8', 'type' => 'musician', 'artist_name' => 'artist8', 'country_id' => '14', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user9@example.com', 'password' => '123456', 'first_name' => 'first9', 'last_name' => 'last9', 'type' => 'musician', 'artist_name' => 'artist9', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user10@example.com', 'password' => '123456', 'first_name' => 'first10', 'last_name' => 'last10', 'type' => 'musician', 'artist_name' => 'artist10', 'country_id' => '22', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user11@example.com', 'password' => '123456', 'first_name' => 'first11', 'last_name' => 'last11', 'type' => 'professional', 'artist_name' => 'artist11', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user12@example.com', 'password' => '123456', 'first_name' => 'first12', 'last_name' => 'last12', 'type' => 'musician', 'artist_name' => 'artist12', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user13@example.com', 'password' => '123456', 'first_name' => 'first13', 'last_name' => 'last13', 'type' => 'musician', 'artist_name' => 'artist13', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user14@example.com', 'password' => '123456', 'first_name' => 'first14', 'last_name' => 'last14', 'type' => 'musician', 'artist_name' => 'artist14', 'country_id' => '22', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user15@example.com', 'password' => '123456', 'first_name' => 'first15', 'last_name' => 'last15', 'type' => 'musician', 'artist_name' => 'artist15', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user16@example.com', 'password' => '123456', 'first_name' => 'first16', 'last_name' => 'last16', 'type' => 'professional', 'artist_name' => 'artist16', 'country_id' => '14', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user17@example.com', 'password' => '123456', 'first_name' => 'first17', 'last_name' => 'last17', 'type' => 'musician', 'artist_name' => 'artist17', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user18@example.com', 'password' => '123456', 'first_name' => 'first18', 'last_name' => 'last18', 'type' => 'professional', 'artist_name' => 'artist18', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user19@example.com', 'password' => '123456', 'first_name' => 'first19', 'last_name' => 'last19', 'type' => 'musician', 'artist_name' => 'artist19', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user20@example.com', 'password' => '123456', 'first_name' => 'first20', 'last_name' => 'last20', 'type' => 'musician', 'artist_name' => 'artist20', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user21@example.com', 'password' => '123456', 'first_name' => 'first21', 'last_name' => 'last21', 'type' => 'professional', 'artist_name' => 'artist21', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user22@example.com', 'password' => '123456', 'first_name' => 'first22', 'last_name' => 'last22', 'type' => 'musician', 'artist_name' => 'artist22', 'country_id' => '14', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user23@example.com', 'password' => '123456', 'first_name' => 'first23', 'last_name' => 'last23', 'type' => 'musician', 'artist_name' => 'artist23', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user24@example.com', 'password' => '123456', 'first_name' => 'first24', 'last_name' => 'last24', 'type' => 'professional', 'artist_name' => 'artist24', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user25@example.com', 'password' => '123456', 'first_name' => 'first25', 'last_name' => 'last25', 'type' => 'musician', 'artist_name' => 'artist25', 'country_id' => '14', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user26@example.com', 'password' => '123456', 'first_name' => 'first26', 'last_name' => 'last26', 'type' => 'professional', 'artist_name' => 'artist26', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user27@example.com', 'password' => '123456', 'first_name' => 'first27', 'last_name' => 'last27', 'type' => 'musician', 'artist_name' => 'artist27', 'country_id' => '14', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user28@example.com', 'password' => '123456', 'first_name' => 'first28', 'last_name' => 'last28', 'type' => 'professional', 'artist_name' => 'artist28', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user29@example.com', 'password' => '123456', 'first_name' => 'first29', 'last_name' => 'last29', 'type' => 'musician', 'artist_name' => 'artist29', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
            ['email' => 'user30@example.com', 'password' => '123456', 'first_name' => 'first30', 'last_name' => 'last20', 'type' => 'professional', 'artist_name' => 'artist30', 'country_id' => '103', 'postal_code' => '123456', 'avatar' => null],
        ];
        
        foreach ($users as $key => $value) {
            $create = User::create($value);            
        }
    }
}
