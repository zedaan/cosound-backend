<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $genres = [
            ['name' => 'Blues'],
            ['name' => 'Classic'],
            ['name' => 'Country'],
            ['name' => 'Jazz'],
            ['name' => 'Pop'],
            ['name' => 'Rock'],
        ];
        
        DB::table('genres')->insert($genres);
    }
}
