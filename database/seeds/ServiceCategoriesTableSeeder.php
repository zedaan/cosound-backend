<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Graphic Design'],
            ['name' => 'Marketing and Promotion'],
            ['name' => 'Publishing'],
            ['name' => 'Legal'],
            ['name' => 'Music & Audio'],
            ['name' => 'Music Business'],
            ['name' => 'Video'],
            ['name' => 'Merchandise'],
        ];
        
        foreach ($categories as $key => $value) {
            $create = \App\Models\ServiceCategory::create($value);
        }
    }
}