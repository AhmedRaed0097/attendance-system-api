<?php

namespace App\Imports;

use App\Models\Lecturer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LecturersImport implements ToModel,WithHeadingRow
{
    use Importable;

    public function __construct($state) {
        $this->state = $state;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        //dd($row);
        return new Lecturer([
            'lecturer_name'     => $row['name'],
            'email'            => $row['email'],
            'state'  => $this->state,
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}

