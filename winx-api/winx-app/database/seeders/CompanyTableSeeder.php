<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1
        \App\Models\Company::create([
        	'name'      => 'Winx',
        ]);
        // 2
        \App\Models\Company::create([
        	'name'      => 'República Galáctica',
        ]);
    }
}
