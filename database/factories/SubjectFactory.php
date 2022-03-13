<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class SubjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subject::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $subjects = [
            "Programming1",  "OOP Programming",  "Database",  "Web devlopment",  "Mobile App devlopment",
            "OS",  "DBA",  "Computer Graphics",  "AI",
        ];
        $random_subject = rand(0,8);
        return [
            // Subject::create($subjects)
            // 'subject_name' =>$subjects[ range(0, 9)],
            'subject_name' =>$subjects[ $random_subject],

        ];
    }
}
