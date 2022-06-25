<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\EmployeesImport;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
class EmployeeController extends Controller
{
    
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'role' => 'required|string',
            'state' => 'required|boolean',
            // 'password' => 'string|confirmed',
        ]);
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'role' => $fields['role'],
            'state' => $fields['state'],
            // 'password' => bcrypt($fields['password']),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),

        ]);
        $token = $user->createToken('addNewAdmin')->plainTextToken;
        $response = [
            'user' => $user,
            'token' => $token,
        ];
        return response()->json([
            'data' => $response,
            'message' => 'تم إنشاء الحساب بنجاح',
            'status_code' => 200


        ]);
        return $response;
    }

    public function update(Request $request)
    {
        $input = $request->all();
        $speakUpdate  = User::findOrFail($input['id']);

        if ($speakUpdate) {
            $speakUpdate->fill($input)->save();
            return response()->json([
                'data'=>$input,
                'message' => 'تم تحديث بيانات الموظف بنجاح',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'message' => 'الموظف غير موجود',
                'status_code' => 404
            ]);
        }
    }
    public function uploadEmployees(Request $request){

        //return $request->all();

        if($request->has('file')) {
            (new EmployeesImport($request->state))->import($request->file, 'local', \Maatwebsite\Excel\Excel::XLSX);
        }

        //Excel::import(new EmployeesImport , $request->file());

        return response()->json([
            'message' => 'تم حفظ البيانات بنجاح',
            'status_code' => 200
        ]);
    }
    public function getEmployeesList(Request $request)
    {
        $users = User::all();
      
        return response()->json([
            'data' => $users,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
    }

    public function delete($id)
    {
        // Send on body => [  Subject_name  ]

        $data = User::where('id', $id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'تم حذف الطالب بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'الطالب غير موجود',
                'status_code' => 404
            ]);
        }
    }
}
