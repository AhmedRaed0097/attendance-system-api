<?php

namespace App\Http\Controllers;
use App\Models\MasterTable;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;



class StudentController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

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
                throw ValidationException::withMessages([
                    'password' => ['المستخدم غير موجود ، الرجاء التأكد من البريد الإلتكروني او كلمة المرور.'],
                ]);
            }

            $token = $student->createToken($request->device_name)->plainTextToken;

            $result['student_id'] = $student->id;
            $result['name'] = $student->student_name;
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
    }

    public function getUser(Request $request)
    {
        $student = $request->user();

        // $student = Student::first();
        // $student['name'] = $student->student_name;
        // $student['major'] = $student->masterTable->major;
        // $student['level'] = $student->masterTable->level;
        // $student['batch_type'] = $student->masterTable->batch_type;

        // $response = [
        //     'user' => $student,
        // ];
        return response()->json([
            'data' => $student,
            'message' => 'تم جلب بيانات المستخدم بنجاح'
        ]);
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

    public function getStudentLectures($student_id)
    {

        $student_data = Student::find($student_id);

        $studentLectureList = [];
        $lectures_data = MasterTable::find($student_data->master_table_id)->lectures;

        foreach ($lectures_data as $lecture) {
            array_push(
                $studentLectureList,
                [
                    'lecture_id' => $lecture->id,
                    'subject_name' => $lecture->subject->subject_name,
                    "lecturer_name" => $lecture->lecturer->lecturer_name,
                    "subject_name" => $lecture->subject->subject_name,
                    "day" => $lecture->period->day,
                    'from' => $lecture->period->from,
                    'to' => $lecture->period->to,

                ]
            );
        }

        return response()->json([
            'data' => $studentLectureList,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
            // 'data' => $student_data
        ]);
        // return $studentLectureList;
        // return $lectures_data;
        // return $student_data;
        // return $$tables_list;
        // return $lecturerLectureList;
        // return $r;
        // 'Row List' => $lectures_list,

    }

    public function getAttendanceTableForStudent(Request $request)
    {
        $result = [];
        if ($request->lecture_id == null) {
            $attend = Attendance::where('student_id', $request->student_id)->get();
        } else {
            $attend = Attendance::where('student_id', $request->student_id)->get();
        }
        if ($attend->count() > 0) {
            foreach ($attend as $att) {
                array_push($result, [
                    'id' => $att->id,
                    'subject_name' => $att->lecture->subject->subject_name,
                    'day' => $att->lecture->period->day,
                    'from' => $att->lecture->period->from,
                    'to' => $att->lecture->period->to,
                    'week_no' => $att->week_no,
                    'state' => $att->state
                ]);
            }
            return response()->json([
                'message' => 'تم جلب البيانات بنجاح',
                'status_code' => 200,
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'message' => 'لايوجد بيانات تحضير لهذه المحاضرة',
                'status_code' => 200,
            ]);
        }
    }

    public function studentScanAttendance($student_id, $lecture_id, $week_no)
    {
        // $attend = Attendance::all()->where('student_id', $student_id)->where('lecture_id', $lecture_id)->where('week_no', $week_no)->first()->update(['state' => 1]);
        $attend = Attendance::first()->where('student_id', $student_id)->where('lecture_id', $lecture_id)->where('week_no', $week_no)->get();

        if ($attend[0]['state'] == 0) {
            // $attend->update(['state' => 1]);
            $attend[0]->state = 1;

            $attend[0]->save();
            return response()->json([
                'attendance_data' => $attend,
                'message' => 'تم تسجيل الحضور بنجاح',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'attendance_data' => $attend,
                'message' => 'تم تسجيل الحضور مسبقاً',
                'status_code' => 202
            ]);
        }
    }
    // ToDo : Set User password for first time
    public function setPasword(Request $request)
    {
        $fields = $request->validate([
            // 'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
        ]);
        $student = Student::where('email', $request->email)->first();
        if (!$student) {
            return response()->json([
                'message' => 'المستخدم غير موجود ، الرجاء التأكد من البريد الإلتكروني او كلمة المرور.',
                'status_code' => 404
            ], 404);
        } else {
            if (!$student->passwrod) {
                $hashedPassword = bcrypt($fields['password']);
                $student->update(['password' => $hashedPassword]);
                return response()->json([
                    'message' => 'تم إعادة تعيين كلمة المرور بنجاح',
                    'status_code' => 200
                ]);
            }else{
                return response()->json([
                    'message' => 'كلمة المرور موجودة مسبقاً',
                    'status_code' => 409
                ]);
            }
        }
    }


}
