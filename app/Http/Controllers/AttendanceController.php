<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Imports\LecturersImport;
use App\Imports\PeriodsImport;
use App\Imports\MajorsImport;
use App\Imports\SubjectsImport;
use Illuminate\Http\Request;
use App\Models\Lecture;
use App\Models\Lecturer;
use App\Models\Attendance;
use App\Models\MasterTable;
use App\Models\Student;
use App\Models\Major;
use App\Models\Subject;
use App\Models\Period;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{


    public function getAllStudents()
    {
        $result = [];
        $students = Student::all();
        foreach ($students as $student) {
            array_push($result, [
                'id' => $student->id,
                'name' => $student->name,
                // 'major' => $student->major,
                // 'level' => $student->level,
                'email' => $student->email,
                'state' => $student->state,
                'batch' => $student->masterTable->title,
                'master_table_id' => $student->master_table_id,
                // 'batch_type' => $student->batch_type
            ]);
        }
        return response()->json([
            'data' => $result,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200,
        ]);
    }
    public function updateStudent(Request $request)
    {
        $input = $request->all();
        $speakUpdate  = Student::findOrFail($input['id']);

        if ($speakUpdate) {
            $speakUpdate->fill($input)->save();
            return response()->json([
                'data' => $input,
                'message' => 'تم تحديث بيانات الطالب بنجاح',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'message' => 'الجدول غير موجود',
                'status_code' => 404
            ]);
        }
    }

    public function deleteStudent($student_id)
    {
        // Send on body => [  Subject_name  ]

        $data = Student::where('id', $student_id)->delete();

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

    public function generateQr($lecture_id, $week_no)
    {
        return response()->json([
            'message' => "تم إنشاء جدول التحضير بنجاح",
            'status_code' => 200
        ]);
    }

    public function getLectureById(Request $request)
    {
        $subject = Lecture::find($request->id)->subject;
        $period = Lecture::find($request->id)->period;
        $lecturer = Lecture::find($request->id)->lecturer;
        $lecture["lecture_id"] = $request->id;
        $lecture["subject_name"] = $subject["subject_name"];
        $lecture["day"] = $period["day"];
        $lecture["from"] = $period["from"];
        $lecture["to"] = $period["to"];
        $lecture["name"] = $lecturer["name"];
        return response()->json([
            'lecture' => $lecture
        ]);
    }
    public function getTableLectures(Request $request)
    {
        $lectures = MasterTable::find($request->id)->lectures;
        $rr["table_title"] = MasterTable::find($request->id)->title;
        foreach ($lectures as $lecture) {
            $rr["Lecture" . $lecture->id] = [
                "subject_name" => $lecture->subject->subject_name,
                "day" => $lecture->period->day,
                "from" => $lecture->period->from,
                "to" => $lecture->period->to,
                "name" => $lecture->lecturer->name,
            ];
        }
        return response()->json([
            'Lecture_Table' => $rr
        ]);
    }

    // هذه الدالة ناقصة المفترض ان يقوم الطالب بإرسال رقم التعريف الخاص به و الخاص بالمحاضرة
    // كي يتم ارجاع جدول حضوره في هذه المحاضرة
    public function checkStateOfAllLecture($student_id)
    {
        $final_result = [];
        // $all_lectures = Student::find($student_id);
        // return $all_lectures->attendances;
        $student = Student::find($student_id);
        $all_lectures = $student->attendances;
        // ====================================================
        foreach ($all_lectures as $lecture) {


            $l['attend_number' . $lecture->id] = [
                'attendance_id' => $lecture->id,
                'lectuer_data' => Lecture::find($lecture->id),
                'state' => $lecture->state
            ];
        }


        foreach ($l as $lecture) {


            array_push($final_result,  [
                "attendance_data" => [
                    "attendance_id" => $lecture['attendance_id'],

                    "lecture_id" => $lecture['lectuer_data']->id,
                    "lecture_data" => [

                        "subject_name" => $lecture['lectuer_data']->subject->subject_name,
                        "day" => $lecture['lectuer_data']->period->day,
                        "from" => $lecture['lectuer_data']->period->from,
                        "to" => $lecture['lectuer_data']->period->to,
                        "name" => $lecture['lectuer_data']->lecturer->name,
                    ],
                    'state' => $lecture['state']
                ]
            ]);
        }

        return $final_result;
    }

    public function checkStateOfOneLecture($student_id, $lecture_id)
    {
        $final_result = [];
        $student = Student::find($student_id);
        $all_lectures = $student->attendances->where('lecture_id', $lecture_id);

        foreach ($all_lectures as $lecture) {
            $l['attend_number' . $lecture->id] = [
                'attendance_id' => $lecture->id,
                'lectuer_data' => Lecture::find($lecture->lecture_id),
                'attendance_date' => $lecture->created_at,
                'state' => $lecture->state,
                'week_no' => $lecture->week_no
            ];
        }

        foreach ($l as $lecture) {
            array_push($final_result,  [
                "attendance_data" => [
                    "attendance_id" => $lecture['attendance_id'],
                    "lecture_id" => $lecture['lectuer_data']->id,
                    "lecture_data" => [
                        "subject_name" => $lecture['lectuer_data']->subject->subject_name,
                        "day" => $lecture['lectuer_data']->period->day,
                        "from" => $lecture['lectuer_data']->period->from,
                        "to" => $lecture['lectuer_data']->period->to,
                        "name" => $lecture['lectuer_data']->lecturer->name,
                        'week_no' => $lecture['week_no']

                    ],
                    // 'student_data' => [
                    //     'student_id' => $student->id,
                    //     'name' => $student->name,
                    //     'major' => $student->major,
                    //     'level' => $student->level,
                    //     'batch_type' => $student->batch_type,

                    // ],

                    'attendance_date' => $lecture['attendance_date'],
                    'state' => $lecture['state']
                ]
            ]);
        }

        // return $all_lectures;
        // return $l;
        // return $rr;
        return $final_result;
    }

    public function addLecture()
    {
        // Send on body => [subject_id , period_id , lecturer_id ,                master_table_id  ]

        $data = request()->all();
        $lecture =  Lecture::create($data);

        return response()->json([
            'lecture_data' => $lecture,
            'message' => 'تم إضافة المحاضرة بنجاح',
            'status_code' => 201
        ]);
    }
    public function deleteLecture($lecture_id)
    {
        // Send on body => [  Subject_name  ]

        $data = Lecture::where('id', $lecture_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'تم حذف المحاضرة بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'This record not found',
                'status_code' => 404
            ]);
        }
    }
    // ==================================================================

    public function uploadLecturers(Request $request)
    {
        if ($request->has('file') && $request->has('state')) {
            (new LecturersImport($request->state))->import($request->file, 'local', \Maatwebsite\Excel\Excel::XLSX);
        }

        return response()->json([
            'message' => 'تم حفظ البيانات بنجاح',
            'status_code' => 200
        ]);
    }

    public function addLecturer()
    {
        // Send on body => [  name  ]
        $data = request()->all();
        $lecturer =  Lecturer::create($data);

        return response()->json([
            'data' => $lecturer,
            'message' => 'تم إضافة المحاضر بنجاح',
            'status_code' => 200
        ]);
    }
    public function deleteLecturer($lecturer_id)
    {
        // Send on body => [  Subject_name  ]

        $data = Lecturer::where('id', $lecturer_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'تم حذف المحاضر بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'المحاضر غير موجود',
                'status_code' => 404
            ]);
        }
    }
    // ========================== Subject ===================================
    public function addSubject()
    {
        $data = request()->all();
        $subject =  Subject::create($data);

        return response()->json([
            'lecture_data' => $subject,
            'message' => 'تم إضافة المادة بنجاح',
            'status_code' => 200
        ]);
    }


    public function uploadSubjects(Request $request)
    {

        if ($request->has('file')) {
            Excel::import(new SubjectsImport, $request->file, 'local', \Maatwebsite\Excel\Excel::XLSX);
        }
        return response()->json([
            'message' => 'تم حفظ البيانات بنجاح',
            'status_code' => 200
        ]);
    }
    public function updateSubject(Request $request)
    {

        $input = $request->all();
        $speakUpdate  = Subject::findOrFail($input['id']);

        if ($speakUpdate) {
            $speakUpdate->fill($input)->save();
            return response()->json([
                'message' => 'تم تحديث بيانات المادة بنجاح',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'message' => 'المادة غير موجودة',
                'status_code' => 404
            ]);
        }
    }

    public function deleteSubject($subject_id)
    {
        // Send on body => [  Subject_name  ]

        $data = Subject::where('id', $subject_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'تم حذف المادة بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'المادة غير موجودة',
                'status_code' => 404
            ]);
        }
    }


    public function getSubjects(Request $request)
    {
        $subjectResult = [];

        $subjects = Subject::all();
        foreach ($subjects as $subject) {
            array_push($subjectResult, [
                'id' => $subject->id,
                'subject_name' => $subject->subject_name,
            ]);
        }

        return response()->json([
            'data' => $subjectResult,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
    }


    public function uploadStudents(Request $request)
    {

        //return $request->all();

        if ($request->has('file') && $request->has('master_table_id')) {
            (new StudentsImport($request->master_table_id, $request->state))->import($request->file, 'local', \Maatwebsite\Excel\Excel::XLSX);
        }


        //Excel::import(new StudentsImport , $request->file());

        return response()->json([
            'message' => 'تم حفظ البيانات بنجاح',
            'status_code' => 200
        ]);
    }
    public function addStudent()
    {
        // Send on body => [  name , major , level , batch_type   ]

        $data = request()->all();
        $student =  Student::create($data);

        return response()->json([
            'student_data' => $student,
            'message' => 'تم إضافة الطالب بنجاح',
            'status_code' => 201
        ]);
    }
    public function addTable()
    {
        // Send on body => [  titile , level , major , batch_type ]
        $data = request()->all();
        $check = MasterTable::where('major', request()->major)->where('level', request()->level)->where('batch_type', request()->batch_type)->get();
        if ($check->count() <  1) {

            $lecture =  MasterTable::create($data);

            return response()->json([
                'lecture_data' => $lecture,
                'message' => 'تم إضافة الجدول بنجاح',
                'status_code' => 200
            ]);
        } else {

            return response()->json([
                'message' => 'الجدول موجود مسبقاً',
                'status_code' => 422
            ]);
        }
    }
    public function updateTable(Request $request)
    {
        // Send on body => [  titile , level , major , batch_type ]

        $input = $request->all();
        $speakUpdate  = MasterTable::findOrFail($input['id']);

        if ($speakUpdate) {
            $speakUpdate->fill($input)->save();
            return response()->json([
                'message' => 'تم تحديث بيانات الجدول بنجاح',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'message' => 'الجدول غير موجود',
                'status_code' => 404
            ]);
        }


        // $data = request()->all();
        // $lecture =  MasterTable::create($data);

    }


    public function deleteTable($table_id)
    {
        // Send on body => [  titile , level , major , batch_type ]

        $data = MasterTable::where('id', $table_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'تم حذف الجدول بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'الجدول غير موجود',
                'status_code' => 404
            ]);
        }
    }

    public function getLecturesForStudent($student_id)
    {
        $studentLectureList = [];
        $student_data = Student::find($student_id);
        $tables_list = MasterTable::first()->where('level', $student_data->level)->where('major', $student_data->major)->where('batch_type', $student_data->batch_type)->get();

        $tbllist = MasterTable::find($student_data->master_table_id)->lectures;

        // $lectures_list = $tables_list[0]->lectures;
        foreach ($tbllist as $lecture) {
            array_push(
                $studentLectureList,
                [
                    'lecture_id' => $lecture->id,
                    'subject_name' => $lecture->subject->subject_name,
                    "name" => $lecture->lecturer->name,
                    "subject_name" => $lecture->subject->subject_name, "day" => $lecture->period->day,
                    'from' => $lecture->period->from,
                    'to' => $lecture->period->to,

                ]
            );
        };;

        return $studentLectureList;
        // return $student_data;
        // return $lecturerLectureList;
        // return $r;
        // 'Row List' => $lectures_list,

    }

    public function getAllTables()
    {
        $tables = MasterTable::all();

        return $tables;
    }

    public function tableLectures($table_id)
    {
        $lectures = Lecture::all()->where('master_table_id', $table_id);


        foreach ($lectures as $lecture) {
            $rr["Lecture" . $lecture->id] = [
                "subject_name" => $lecture->subject->subject_name, "day" => $lecture->period->day,
                "from" => $lecture->period->from,
                "to" => $lecture->period->to,
                "name" => $lecture->lecturer->name,
            ];
        }
        return response()->json([
            'Lecture_Table' => $rr
        ]);
    }

    public function getTables()
    {
        $result = [];
        $tables = MasterTable::all();
        foreach ($tables as $table) {
            array_push($result, [
                'id' => $table->id,
                'title' => $table->title,
                'level' => $table->level,
                'major' => $table->major,
                'batch_type' => $table->batch_type,
            ]);
        }
        return response()->json([
            'data' => $result,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
    }

    public function getLectureData()
    {
        $lecturerResult = [];

        $lectures = Lecture::all();



        // $tables = MasterTable::all();
        foreach ($lectures as $lecture) {

            $last_attendance = null;

            $last_attendance = Attendance::all()->where('lecture_id', $lecture->id)->last();


            array_push($lecturerResult, [
                'id' => $lecture->id,
                'lecture_title' => " [يوم {$lecture->period->day}] [المادة {$lecture->subject->subject_name}] [الفترة {$lecture->period->from} - {$lecture->period->to}]",
                'subject_id' => $lecture->subject->id,
                'lecturer_id' => $lecture->lecturer->id,
                'period_id' => $lecture->period->id,
                'master_table_id' => $lecture->masterTable->id,
                'last_week' => $last_attendance != null ? $last_attendance->week_no : null,
            ]);
        }

        return response()->json([
            'data' => $lecturerResult,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
    }

    public function addPeriod(Request $request)
    {
        $period = Period::where('day', $request->day)->where('from', $request->from)->where('to', $request->to)->first();
        if (!$period) {
            $data = request()->all();
            $period = Period::create($data);
            return response()->json([
                'period_data' => $period,
                'message' => 'تم إضافة الفترة بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'period_data' => $period,
                'message' => 'الفترة موجودة مسبقاً',
                'status_code' => 201
            ]);
        }
    }
    public function updatePeriod(Request $request)
    {

        $period  = Period::findOrFail($request->id);

        if ($period) {
            $period->fill($request->all())->save();
            return response()->json([
                'message' => 'تم تحديث بيانات الفترة بنجاح',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'message' => 'الفترة غير موجود',
                'status_code' => 404
            ]);
        }
    }
    public function getPeriods()
    {
        $periodResult = [];

        $periods = Period::all();
        foreach ($periods as $period) {
            array_push($periodResult, [
                'id' => $period->id,
                'day' => $period->day,
                'from' => $period->from,
                'to' => $period->to,
            ]);
        }

        return response()->json([
            'data' => $periodResult,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
    }
    public function deletePeriod($period_id)
    {
        $data = Period::where('id', $period_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'تم حذف الفترة بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'الفترة غير موجودة',
                'status_code' => 404
            ]);
        }
    }

    public function uploadPeriods(Request $request)
    {

        if ($request->has('file')) {
            Excel::import(new PeriodsImport, $request->file, 'local', \Maatwebsite\Excel\Excel::XLSX);
        }
        return response()->json([
            'message' => 'تم حفظ البيانات بنجاح',
            'status_code' => 200
        ]);
    }

    // ========================= Majors =========================================

    public function uploadMajors(Request $request)
    {

        if ($request->has('file')) {
            Excel::import(new MajorsImport, $request->file, 'local', \Maatwebsite\Excel\Excel::XLSX);
        }
        return response()->json([
            'message' => 'تم حفظ البيانات بنجاح',
            'status_code' => 200
        ]);
    }
    public function addMajor(Request $request)
    {
        $major = Major::where('major', $request->major)->first();
        if (!$major) {
            $data = request()->all();
            $response = Major::create($data);
            return response()->json([
                'data' => $response,
                'message' => 'تم إضافة التخصص بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'التخصص موجودة مسبقاً',
                'status_code' => 404
            ]);
        }
    }
    public function updateMajor(Request $request)
    {

        $major  = Major::findOrFail($request->id);

        if ($major) {
            $major->fill($request->all())->save();
            return response()->json([
                'message' => 'تم تحديث بيانات التخصص بنجاح',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'message' => 'التخصص غير موجود',
                'status_code' => 404
            ]);
        }
    }
    public function getMajors()
    {
        $periodResult = [];

        $majors = Major::all();
        foreach ($majors as $major) {
            array_push($periodResult, [
                'id' => $major->id,
                'major' => $major->major,
                'levels' => $major->levels,
            ]);
        }

        return response()->json([
            'data' => $periodResult,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
    }
    public function deleteMajor($major_id)
    {
        $data = Major::where('id', $major_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'تم حذف الفترة بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'الفترة غير موجودة',
                'status_code' => 404
            ]);
        }
    }

    // ========================= //Majors =========================================

    public function updateLecture(Request $request)
    {
        $lecture = Lecture::find($request->id);

        $lecture->update([
            'subject_id' => $request->subject_id,
            'period_id' => $request->period_id,
            'lecturer_id' => $request->lecturer_id,
            'master_table_id' => $request->master_table_id,
        ]);
        $lecture->save();

        return response()->json([
            'lecture_data' => $lecture,
            'message' => 'تم تحديث بيانات المحاضرة بنجاح',
            'status_code' => 201
        ]);
    }

    public function getMasterTableLectures($table_id)
    {
        $lectures = Lecture::where('master_table_id', $table_id)->get();
        $result = [];
        foreach ($lectures as $lecture) {
            array_push($result, [
                'id' => $lecture->id,
                'subject_id' => $lecture->subject->id,
                'subject_name' => $lecture->subject->subject_name,
                'lecturer_id' => $lecture->lecturer->id,
                'name' => $lecture->lecturer->name,
                'period_id' => $lecture->period->id,
                'day' => $lecture->period->day,
                'from' => $lecture->period->from,
                'to' => $lecture->period->to,
            ]);
        };
        return response()->json([
            'lectures' => $result
        ]);
    }



    public function getLecturers(Request $request)
    {
        $lecturerResult = [];

        $lecturers = Lecturer::all();
        foreach ($lecturers as $lecturer) {
            array_push($lecturerResult, [
                'id' => $lecturer->id,
                'name' => $lecturer->name,
                'email' => $lecturer->email,
                'state' => $lecturer->state,
            ]);
        }

        return response()->json([
            'data' => $lecturerResult,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
    }


    public function updateLecturer(Request $request)
    {

        $lecturer  = Lecturer::findOrFail($request->id);

        if ($lecturer) {
            $lecturer->fill($request->all())->save();
            return response()->json([
                'message' => 'تم تحديث بيانات المحاضر بنجاح',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'message' => 'المحاضر غير موجود',
                'status_code' => 404
            ]);
        }
    }

    public function getReport(Request $request)
    {
        $students_attenance_data = [];
        $report_data = [];
        if ($request->week_no == -1) {
            $attendances = Attendance::all()->where(
                'lecture_id',
                $request->lecture_id
            );
        } else {
            $attendances = Attendance::where(
                'lecture_id',
                $request->lecture_id
            )->where('week_no', $request->week_no)->get();
        }

        $students = Student::where('master_table_id',  $attendances[0]->lecture->master_table_id)->get();

        array_push($report_data, [
            'title' =>  $attendances[0]->lecture->MasterTable->title,
            'major' => $attendances[0]->lecture->MasterTable->major,
            'level' => $attendances[0]->lecture->MasterTable->level,
            'batch_type' => $attendances[0]->lecture->MasterTable->batch_type,
            'subject_name' => $attendances[0]->lecture->subject->subject_name
        ]);



        foreach ($students as $student) {
            $student_attendances = $attendances->where('student_id', $student->id);
            $attend_states = [];
            foreach ($student_attendances as $attend) {
                if ($attend->state == 0) {
                    array_push($attend_states,  'غائب');
                } else {
                    array_push($attend_states,  'حاضر');
                }
            }
            array_push($students_attenance_data,  [
                'student_id' => $student->id,
                'name' => $student->name,
                'attend_states' => $attend_states
            ]);
        }
        $response = [
            'report_data' => $report_data[0],
            'students_attenance_data' => $students_attenance_data
        ];
        return response()->json([
            'data' => $response,
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200
        ]);
    }
    // TO GET ALL LECTURES THAT HAVE ATTENDANCE
    public function getLecturesForReport()
    {
        $lecture_with_attendance = Lecture::has('attendance')->get();

        if ($lecture_with_attendance->count() > 0) {

            $lecturerResult = [];

            foreach ($lecture_with_attendance as $lecture) {
                $last_week = Attendance::all()->where('lecture_id', $lecture->id)->last()->week_no;
                array_push($lecturerResult, [
                    'lecture_id' => $lecture->id,
                    'lecture_title' => " [يوم {$lecture->period->day}] [المادة {$lecture->subject->subject_name}] [الفترة {$lecture->period->from} - {$lecture->period->to}]",
                    'last_week' => $last_week,
                ]);
            }
            return response()->json([
                'data' => $lecturerResult,
                'message' => 'تم جلب البيانات بنجاح',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'لايوجد اي سجل تحضير',
                'status_code' => 404
            ]);
        }
    }
}
