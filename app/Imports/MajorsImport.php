<?php

namespace App\Imports;

use App\Models\Major;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MajorsImport implements ToModel,WithHeadingRow
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
        //dd($row);
        return new Major([
            'major'     => $row['major'],
            'levels'    => $row['levels'],
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}