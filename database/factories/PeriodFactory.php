<?php

namespace Database\Factories;

use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Period::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Tursday"];
        $from_to = [8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
        return [
            "day" => $days[rand(0, 4)],
            "from" => $from_to[rand(0, 10)],
            "to" => $from_to[rand(0, 10)],
        ];
    }
}
