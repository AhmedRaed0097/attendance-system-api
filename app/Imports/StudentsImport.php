<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel,WithHeadingRow
{
    use Importable;

    public function __construct($masterTableId, $state) {
        $this->masterTableId = $masterTableId;
        $this->state = $state;
    }

    //  /**
    //  * @return array
    //  */
    // public function array(array $array)
    // {
    //     dd($array);
    //     return [
    //             'student_name'     => $array['student_name'],
    //             'email'            => $array['email'],
    //             'master_table_id'  => $this->masterTableId,
    //            ];
    // }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        //dd($row);
        return new Student([
            'student_name'     => $row['student_name'],
            'email'            => $row['email'],
            'master_table_id'  => $this->masterTableId,
            'state'  => $this->state,
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
