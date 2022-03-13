<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $fields = $request->validate([
            // 'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);
        $user = User::create([
            // 'name' => $fields['name'],
            'email' => $fields['email'],
            'email_verified_at' => now(),
            'password' => bcrypt($fields['password']),
            'remember_token' => Str::random(10),

        ]);
        // $token = $user->createToken($request->device_name)->plainTextToken;
        $response = [
            'user' => $user,
            // 'token' => $token,
        ];
        return response($response, 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;
        if ($user->user_type == "student") {
        $student = Student::where('user_id', $user->id)->first();

            $user['student_id'] = $student->id;
            $user['student_name'] = $student->student_name;
            // $user['name'] = $student->student_name;
            // $state= $user->student->student_name;
            // $user['state'] =$state;
        } else if ($user->user_type == "lecturer") {
        $lecturer = Lecturer::where('user_id', $user->id)->first();

        $user['lecturer_id'] = $lecturer->id;
        $user['lecturer_name'] = $lecturer->lecturer_name;
        }

        $response = [
            'user' => $user,
            'token' => $token,
        ];
        return response()->json([
            'data' => $response,
            'status_code' => 200
        ]);
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json([
            "message" => 'tokens are deleted',
            'status_code' => 200

        ]);
    }
}
