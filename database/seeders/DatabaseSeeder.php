<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Lecture;
use Illuminate\Database\Seeder;
use Database\Seeders\StudentSeeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(4)->create();
        // \App\Models\Lecturer::factory(10)->create();
        // \App\Models\Period::factory(10)->create();



        // \App\Models\Subject::factory(5)->create();
        \App\Models\Lecture::factory(10)->create();
        // \App\Models\MasterTable::factory(5)->create();
        // $this->call(StudentSeeder::class);
        // $this->call(AttenanceSeeder::class);

        // $s = ["Arabic Language", "Data mining" , "Security"];
        // foreach ($s as $ss) {
        //     DB::table('subjects')->insert([
        //         'subject_name'=>$ss
        //     ]);
        // }
    }
}
