<?php

namespace App\Imports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SubjectsImport implements ToModel,WithHeadingRow
{
    use Importable;

    public function __construct() {
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Subject([
            'subject_name'     => $row['name'],
        ]);
    }
}
