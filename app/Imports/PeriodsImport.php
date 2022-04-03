<?php

namespace App\Imports;

use App\Models\Period;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PeriodsImport implements ToModel
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
        return new Period([
            'day'    => $row['day'],
            'from'    => $row['from'],
            'to'    => $row['to'],

        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
}
