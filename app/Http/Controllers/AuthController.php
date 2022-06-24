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
            'user_type' => 'required|string',
            'password' => 'required|string|confirmed',
        ]);
        $user = User::create([
            // 'name' => $fields['name'],
            'email' => $fields['email'],
            'user_type' => $fields['user_type'],
            'email_verified_at' => now(),
            'password' => bcrypt($fields['password']),
            'remember_token' => Str::random(10),

        ]);
        $token = $user->createToken('register')->plainTextToken;
        $response = [
            'user' => $user,
            'token' => $token,
        ];
        return response()->json([
            'data' => $response,
            'message' => 'تم إنشاء الحساب بنجاح'

        ], 201);
        return $response;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);
        if ($request->user_type === 'student') {
            $student = Student::where('email', $request->email)->first();
            if (!$student) {
                return response()->json([
                    'message' => 'المستخدم غير موجود ، الرجاء التأكد من البريد الإلتكروني او كلمة المرور.',
                    'status_code' => 404
                ], 404);
            } else if (!$student['password']) {
                return response()->json([

                    'password' => $student['password'],
                    'message' => 'الرجاْ إعادة تعيين كلمة المرور للمرة الاولى',
                    'status_code' => 2010
                ]);
            } else {

                if (!Hash::check($request->password, $student->password)) {
                    return response()->json([
                        'message' => 'المستخدم غير موجود ، الرجاء التأكد من البريد الإلتكروني او كلمة المرور.',
                        'status_code' => 404
                    ], 404);
                }

                $token = $student->createToken($request->user_type)->plainTextToken;

                $result['student_id'] = $student->id;
                $result['name'] = $student->name;
                $result['major'] = $student->masterTable->major;
                $result['level'] = $student->masterTable->level;
                $result['batch_type'] = $student->masterTable->batch_type;
                $result['user_type'] = 'student';

                $response = [
                    'user' => $result,
                    'token' => $token,
                ];
                return response()->json([
                    'data' => $response,
                    'message' => 'تم تسجيل الدخول بنجاح'
                ], 200);
            }
        } else if ($request->user_type === 'lecturer') {
            //
        } else {



            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $token = $user->createToken($request->device_name)->plainTextToken;

            $response = [
                'user' => $user,
                'token' => $token,
            ];
            return response()->json([
                'data' => $response,
                'message' => 'تم تسجيل الدخول بنجاح'
            ], 200);
        }
    }
    public function getUser(Request $request)
    {
        if ($request->user()) {
            $result = [];
            $user = $request->user();
            if (Student::where('email', $user->email)->count() > 0) {
                $student = Student::where('email', $user->email)->first();
                $result['name'] = $student->name;
                $result['major'] = $student->masterTable->major;
                $result['level'] = $student->masterTable->level;
                $result['batch_type'] = $student->masterTable->batch_type;
                $result['user_type'] = 'student';
            } else if (Lecturer::where('email', $user->email)->count() > 0) {
                $lecturer = Lecturer::where('email', $user->email)->first();
                $result['name'] = $lecturer->name;
                $result['user_type'] = 'lecturer';
            } else {
                $result = User::where('email', $user->email)->first();
            }
            $response = [
                'user' => $result,
            ];
            return response()->json([
                'data' => $response,
                'message' => 'تم جلب بيانات المستخدم بنجاح'
            ]);
        } else {
            return response()->json([
                'message' => 'الرجاء تسجيل الدخول'
            ]);
        }
    }

    public function setPasword(Request $request)
    {
        $fields = $request->validate([
            // 'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
        ]);
        if ($request->user_type === 'student') {
            $user = Student::where('email', $request->email)->first();
        } else {
            $user = Lecturer::where('email', $request->email)->first();
        }
        if (!$user) {
            return response()->json([
                'message' => 'المستخدم غير موجود ، الرجاء التأكد من البريد الإلتكروني او كلمة المرور.',
                'status_code' => 404
            ], 404);
        } else {
            $hasPassowrd = $user->where('email', $request->email)->whereNotNull('password')->get();
            if ($hasPassowrd->count() > 0) {
                return response()->json([
                    'message' => 'كلمة المرور موجودة مسبقاً',
                    'status_code' => 409
                ]);
            } else {
                $hashedPassword = bcrypt($fields['password']);
                $user->update(['password' => $hashedPassword]);
                return response()->json([
                    'message' => 'تم إعادة تعيين كلمة المرور بنجاح',
                    'status_code' => 200
                ]);
            }
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json([
            "message" => 'تم تسجيل الخروج بنجاح',
            'status_code' => 200

        ]);
    }
}
