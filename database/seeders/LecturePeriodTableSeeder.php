<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class LecturePeriodTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(1, 20) as $v) {
            DB::table('lectures')->insert([
                'subject_id' => rand(1, 10),
                'period_id' => rand(1, 20),
            ]);
        }
    }
}
