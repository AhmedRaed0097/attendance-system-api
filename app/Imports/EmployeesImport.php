<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToModel,WithHeadingRow
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
        return new User([
            'name'   => $row['name'],
            'email'  => $row['email'],
            'role'   => $row['role'],
            'state'  => $this->state,
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
