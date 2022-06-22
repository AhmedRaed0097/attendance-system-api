<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $majors = ['IT', 'CS'];
        $batchs = ['General', 'Parallel'];
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'name' => $this->faker->name(),
            'master_table_id' => rand(1, 5),
            // 'major' => $majors[rand(0, 1)],
            // 'level' => rand(1, 4),
            // 'batch_type' => $batchs[rand(0, 1)],
            'state' =>  1,
            // 'user_id' => rand(1, 4),
        ];
    }
}
