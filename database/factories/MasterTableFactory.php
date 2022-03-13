<?php

namespace Database\Factories;

use App\Models\MasterTable;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterTableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MasterTable::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $majors = ['IT','CS'];
        $batch_types= ['General','Parallel'];
        $random_level = rand(1,4);
        $random_index = rand(1,0);
        return [
            'title' => 'Lectures Table for '.$majors[$random_index].' major at level '.$random_level .' '. $batch_types[$random_index] .' type',
            'level' => $random_level,
            'major' => $majors[$random_index],
            'batch_type' => $batch_types[$random_index],


        ];
    }
}
