<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Lecturer;
use App\Models\MasterTable;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Major;
use App\Models\Subject;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LecturerController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = Lecturer::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'المستخدم غير موجود ، الرجاء التأكد من البريد الإلتكروني او كلمة المرور.',
                'status_code' => 404
            ], 404);
        } else if (!$user->passowrd) {
            return response()->json([
                'message' => 'الرجاْ إعادة تعيين كلمة المرور للمرة الاولى',
                'status_code' => 2010
            ]);
        } else {

            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['المستخدم غير موجود ، الرجاء التأكد من البريد الإلتكروني او كلمة المرور.'],
                ]);
            }

            $token = $user->createToken($request->device_name)->plainTextToken;

            $user['lecturer_id'] = $user->id;
            $user['lecturer_name'] = $user->lecturer_name;

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

    // For Lecturer Lectures Dropdown
    public function getLecturerLectures($lecturer_id)
    {
        $lecturerLectureList = [];
        $lectures_list = Lecture::all()->where('lecturer_id', $lecturer_id);
        foreach ($lectures_list as $lecture) {
            $att = Attendance::all()->where('lecture_id', $lecture->id)->last();

            $rr["Lecture" . $lecture->id] = [
                "lecture_id" => $lecture->id,
                // for dropdown menu
                "subject_title" => $lecture->subject->subject_name . '( ' . $lecture->period->from . ' - ' . $lecture->period->to . ' )',
                "subject_name" => $lecture->subject->subject_name,
                "day" => $lecture->period->day,
                "from" => $lecture->period->from,
                "to" => $lecture->period->to
                // "lecturer_name" => $lecture->lecturer->lecturer_name,
            ];
            if ($att == null) {

                $rr["Lecture" . $lecture->id]['last_week'] =  null;
            } else {
                $rr["Lecture" . $lecture->id]['last_week'] =  $att->week_no;
            }
            array_push($lecturerLectureList, $rr["Lecture" . $lecture->id]);
        }


        // return $lecturerLectureList;
        return response()->json([
            'data' => $lecturerLectureList,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
        // 'Row List' => $lectures_list,

    }

    // ON GENERATE LECTURE QR CODE
    public function addStudentForAttendance($lecture_id, $week_no)
    {
        $check = Attendance::where('lecture_id', $lecture_id)->where('week_no', $week_no)->get();
        if ($check->count() <  1) {

            $table = Lecture::find($lecture_id)->masterTable;
            // $students = Student::all()->where('level', $table->level)->where('major', $table->major)->where('batch_type', $table->batch_type);

            // $lec = Lecture::find($lecture_id);
            $students = Student::where('master_table_id', $table->id)->get();


            // Add all student to attendance table
            foreach ($students as $student) {
                Attendance::create([
                    'student_id' => $student->id,
                    'lecture_id' => $lecture_id,
                    'state' => 0,
                    'week_no' => $week_no
                ]);
            }
        } else {

            return response()->json([
                'message' => "هناك جدول تحضير موجود مسبقا  لهذا الأسبوع",
                'status_code' => 401


            ]);
        }

        return response()->json([
            'message' => "تم إنشاء جدول التحضير بنجاح",
            'status_code' => 200
        ]);
    }

    // ON UNDO GENERTE QR CODE
    public function removeBatchFormAttendance($lecture_id, $week_no)
    {

        $data = Attendance::where('lecture_id', $lecture_id)->where('week_no', $week_no)->delete();

        if ($data > 1) {
            return response()->json([
                'message' => 'تم حذف جدول التحضير بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'لايوجد كشف تحضير للدفعة في هذا الاسبوع',
                'status_code' => 404
            ]);
        }
    }

    public function showStuForMenualAttend($lecture_id, $week_no)
    {
        $attendance_data_list = [];
        $student_data_list = [];
        $data = [];

        $attendRecords = Attendance::all()->where('lecture_id', $lecture_id)->where('week_no', $week_no);
        array_push($attendance_data_list, [

            'attendance_date' => $attendRecords->first()->created_at,
            'lecture_id' => $attendRecords->first()->lecture->id,
            "major" => $attendRecords[0]->student->masterTable->major,
            "level" => $attendRecords[0]->student->masterTable->level,
            "batch_type" => $attendRecords[0]->student->masterTable->batch_type,
            'subject_name' => $attendRecords->first()->lecture->subject->subject_name,
            'week_no' => $attendRecords->first()->week_no

        ]);
        foreach ($attendRecords as $record) {
            array_push($student_data_list, [


                "id" => $record->student->id,
                "student_name" => $record->student->student_name,
                'state' => $record->state


            ]);
        }
        $data['attendance_data'] = $attendance_data_list[0];
        $data['students_data'] = $student_data_list;
        return response()->json([
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200,
            'data' => $data
        ]);
    }

    public function studentManualAttendance($student_id, $lecture_id, $week_no)
    {
        // $attend = Attendance::all()->where('student_id', $student_id)->where('lecture_id', $lecture_id)->where('week_no', $week_no)->first()->update(['state' => 1]);
        $attend = Attendance::first()->where('student_id', $student_id)->where('lecture_id', $lecture_id)->where('week_no', $week_no)->first();
        if ($attend->state == 1) {
            $attend->update(['state' => 0]);
            return response()->json([
                'message' => 'تم حذف تسجيل الحضور بنجاح',
                'status_code' => 200
            ]);
        } else {
            $attend->update(['state' => 1]);
            return response()->json([
                'message' => 'تم تسجيل الحضور بنجاح',
                'status_code' => 200
            ]);
        }
    }
}