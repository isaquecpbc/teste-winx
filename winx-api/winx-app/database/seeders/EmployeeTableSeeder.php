<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EmployeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1
        \App\Models\Employee::create([
        	'user_id'           => 2,
        	'responsibility'    => 'Actor',
        	'admission_at'      => '2014-08-22',
        	'phone'             => '47988771122',
        ]);
        // 2
        \App\Models\Employee::create([
        	'user_id'           => 3,
        	'responsibility'    => 'Mestre Jedi',
        	'admission_at'      => '2012-02-09',
        	'phone'             => '47988771133',
        ]);
    }
}
