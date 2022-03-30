<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;

class StudentsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Student([
            'student_name'     => $row['name'],
            'email'            => $row['email'],
            'master_table_id'  => $row['master_table_id'],
        ]);
    }
}
