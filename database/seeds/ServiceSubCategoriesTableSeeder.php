<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSubCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subCategories = [
            ['name' => 'Sub category 1.1', 'parent_id' => 1],
            ['name' => 'Sub category 1.2', 'parent_id' => 1],
            ['name' => 'Sub category 1.3', 'parent_id' => 1],

            ['name' => 'Sub category 2.1', 'parent_id' => 2],
            ['name' => 'Sub category 2.2', 'parent_id' => 2],
            ['name' => 'Sub category 2.3', 'parent_id' => 2],

            ['name' => 'Sub category 3.1', 'parent_id' => 3],
            ['name' => 'Sub category 3.2', 'parent_id' => 3],
            ['name' => 'Sub category 3.3', 'parent_id' => 3],

            ['name' => 'Sub category 4.1', 'parent_id' => 4],
            ['name' => 'Sub category 4.2', 'parent_id' => 4],
            ['name' => 'Sub category 4.3', 'parent_id' => 4],

            ['name' => 'Sub category 5.1', 'parent_id' => 5],
            ['name' => 'Sub category 5.2', 'parent_id' => 5],
            ['name' => 'Sub category 5.3', 'parent_id' => 5],

            ['name' => 'Sub category 6.1', 'parent_id' => 6],
            ['name' => 'Sub category 6.2', 'parent_id' => 6],
            ['name' => 'Sub category 6.3', 'parent_id' => 6],

            ['name' => 'Sub category 7.1', 'parent_id' => 7],
            ['name' => 'Sub category 7.2', 'parent_id' => 7],
            ['name' => 'Sub category 7.3', 'parent_id' => 7],

            ['name' => 'Sub category 8.1', 'parent_id' => 8],
            ['name' => 'Sub category 8.2', 'parent_id' => 8],
            ['name' => 'Sub category 8.3', 'parent_id' => 8],
        ];

        foreach ($subCategories as $key => $value) {
            $create = \App\Models\ServiceSubCategory::create($value);
        }
    }
}
