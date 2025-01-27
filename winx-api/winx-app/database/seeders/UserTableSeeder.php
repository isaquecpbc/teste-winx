<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::create([
        	'name'         => 'Admin Master',
        	'email'        => 'admin@adminstradores.adm',
        	'company_id'   => 1,
            'admin' => true,
        	'password'     => bcrypt('Admin@adm'),
        ]);
        \App\Models\User::create([
            'name'         => 'BoJack Horseman',
            'email'        => 'bojack@horse.men',
        	'company_id'   => 1,
            'password'     => bcrypt('Bo@Jack'),
        ]);
        \App\Models\User::create([
            'name'         => 'Anakin Skywalker',
            'email'        => 'bogan@imperio.tatooine',
        	'company_id'   => 2,
            'admin' => true,
            'password'     => bcrypt('Darth@Vader'),
        ]);
    }
}
