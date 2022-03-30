<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use App\Models\Lecture;
use App\Models\Lecturer;
use App\Models\MasterTable;
use App\Models\Student;
use App\Models\Attendance;
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
                'student_name' => $student->student_name,
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
            'message'=>'تم جلب البيانات بنجاح',
            'status_code'=>200,
        ]);
    }
    public function updateStudent(Request $request)
    {
        $input = $request->all();
        $speakUpdate  = Student::findOrFail($input['id']);

        if ($speakUpdate) {
            $speakUpdate->fill($input)->save();
            return response()->json([
                'data'=>$input,
                'message' => 'تم تحديث الجدول بنجاح',
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

            return response()->json([
                'message' => "Lecture and Students are added to attendance table",
                'status_code' => 200
            ]);
        } else {

            return response()->json([
                'message' => "This batch was already add to this lecture and week!!",
                'status_code' => 401


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
            'subject_name' => $attendRecords->first()->lecture->subject->subject_name,
            'week_no' => $attendRecords->first()->week_no

        ]);
        foreach ($attendRecords as $record) {
            array_push($student_data_list, [


                "id" => $record->student->id,
                "student_name" => $record->student->student_name,
                "major" => $record->student->masterTable->major,
                "level" => $record->student->masterTable->level,
                "batch_type" => $record->student->masterTable->batch_type,
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
        $lecture["lecturer_name"] = $lecturer["lecturer_name"];
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
                "lecturer_name" => $lecture->lecturer->lecturer_name,
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
                        "lecturer_name" => $lecture['lectuer_data']->lecturer->lecturer_name,
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
                        "lecturer_name" => $lecture['lectuer_data']->lecturer->lecturer_name,
                        'week_no' => $lecture['week_no']

                    ],
                    // 'student_data' => [
                    //     'student_id' => $student->id,
                    //     'student_name' => $student->student_name,
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
                'message' => 'Your attendance has been successfully registered',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'attendance_data' => $attend,
                'message' => 'Your are already registered',
                'status_code' => 2010
            ]);
        }
    }
    public function studentManualAttendance($student_id, $lecture_id, $week_no)
    {
        // $attend = Attendance::all()->where('student_id', $student_id)->where('lecture_id', $lecture_id)->where('week_no', $week_no)->first()->update(['state' => 1]);
        $attend = Attendance::first()->where('student_id', $student_id)->where('lecture_id', $lecture_id)->where('week_no', $week_no)->first();
        if ($attend->state == 1) {
            $attend->update(['state' => 0]);
            return response()->json([
                'attendance_data' => $attend,
                'message' => 'Your attendance has been successfully deleted',
                'status_code' => 201
            ]);
        } else {
            $attend->update(['state' => 1]);
            return response()->json([
                'attendance_data' => $attend,
                'message' => 'Your attendance has been successfully registered',
                'status_code' => 201
            ]);
        }
    }
    public function getAttendanceTableForStudent(Request $request)
    {
        $result = [];
        if ($request->lecture_id == null) {
            $attend = Attendance::where('student_id', $request->student_id)->get();
        } else {
            $attend = Attendance::where('student_id', $request->student_id)->where('lecture_id', $request->lecture_id)->get();
        }
        if ($attend->count() > 0) {
            foreach ($attend as $att) {

                $result['id'] = $att->id;
                $result['subject_name'] = $att->student->student_name;
                $result['day'] = $att->lecture->period->day;
                $result['from'] = $att->lecture->period->from;
                $result['to'] = $att->lecture->period->to;
                $result['week_no'] = $att->week_no;
                $result['state'] = $att->state;
            }
            return response()->json([
                'message' => 'تم جلب البيانات بنجاح',
                'status_code' => 200,
                'data' => [$result],
            ]);
        } else {
            return response()->json([
                'message' => 'لايوجد بيانات تحضير لهذه المحاضرة',
                'status_code' => 200,
            ]);
        }
    }

    public function addLecture()
    {
        // Send on body => [subject_id , period_id , lecturer_id ,                master_table_id  ]

        $data = request()->all();
        $lecture =  Lecture::create($data);

        return response()->json([
            'lecture_data' => $lecture,
            'message' => 'The lecture has been successfully added',
            'status_code' => 201
        ]);
    }
    public function deleteLecture($lecture_id)
    {
        // Send on body => [  Subject_name  ]

        $data = Lecture::where('id', $lecture_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'The lecture has been successfully deleted',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'This record not found',
                'status_code' => 404
            ]);
        }
    }

    public function addLecturer()
    {
        // Send on body => [  Lecturer_name  ]
        $data = request()->all();
        $lecturer =  Lecturer::create($data);

        return response()->json([
            'lecture_data' => $lecturer,
            'message' => 'The lecturer has been successfully added',
            'status_code' => 201
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

    public function addSubject()
    {
        // Send on body => [  Subject_name  ]

        $data = request()->all();
        $subject =  Subject::create($data);

        return response()->json([
            'lecture_data' => $subject,
            'message' => 'تم إضافة المادة بنجاح',
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
                'message' => 'تم تحديث المادة بنجاح',
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


    public function uploadStudents(Request $request){
        Excel::import(new StudentsImport , $request->file());

        return response()->json([
            'message' => 'تم حفظ البيانات بنجاح',
            'status_code' => 200
        ]);
    }
    public function addStudent()
    {
        // Send on body => [  Student_name , major , level , batch_type   ]

        $data = request()->all();
        $student =  Student::create($data);

        return response()->json([
            'student_data' => $student,
            'message' => 'The student has been successfully added',
            'status_code' => 201
        ]);
    }
    public function addTable()
    {
        // Send on body => [  titile , level , major , batch_type ]

        $data = request()->all();
        $lecture =  MasterTable::create($data);

        return response()->json([
            'lecture_data' => $lecture,
            'message' => 'The table has been successfully added',
            'status_code' => 201
        ]);
    }
    public function updateTable(Request $request)
    {
        // Send on body => [  titile , level , major , batch_type ]

        $input = $request->all();
        $speakUpdate  = MasterTable::findOrFail($input['id']);

        if ($speakUpdate) {
            $speakUpdate->fill($input)->save();
            return response()->json([
                'message' => 'تم تحديث الجدول بنجاح',
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
                'message' => 'The table has been successfully deleted',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'This record not found',
                'status_code' => 404
            ]);
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
            'data' => $lecturerLectureList
        ]);
        // 'Row List' => $lectures_list,

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
                    "lecturer_name" => $lecture->lecturer->lecturer_name,
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
            'message' => 'تم جلب البيانات بنجاح',
            'status_code' => 200,
            'data' => $student_data
            // 'data' => $studentLectureList
        ]);
        // return $studentLectureList;
        // return $lectures_data;
        // return $student_data;
        // return $$tables_list;
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
                "lecturer_name" => $lecture->lecturer->lecturer_name,
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
            array_push($lecturerResult, [
                'id' => $lecture->id,
                'lecture_title' => " [يوم {$lecture->period->day}] [المادة {$lecture->subject->subject_name}] [الفترة {$lecture->period->from} - {$lecture->period->to}]",
                'subject_id' => $lecture->subject->id,
                'lecturer_id' => $lecture->lecturer->id,
                'period_id' => $lecture->period->id,
                'master_table_id' => $lecture->masterTable->id,
            ]);
        }

        return response()->json([
            'data'=>$lecturerResult,
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
    public function updatePeriod(Request $request){

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


// ========================= Majors =========================================


public function addMajor(Request $request)
{
    $major = Major::where('major', $request->major)->first();
    if (!$major) {
        $data = request()->all();
        $response = Major::create($data);
        return response()->json([
            'data' => $response,
            'message' => 'تم إضافة الفترة بنجاح',
            'status_code' => 200
        ]);
    } else {
        return response()->json([
            'message' => 'الفترة موجودة مسبقاً',
            'status_code' => 404
        ]);
    }
}
public function updateMajor(Request $request){

    $major  = Major::findOrFail($request->id);

    if ($major) {
        $major->fill($request->all())->save();
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
            'message' => 'The lecture has been successfully updated',
            'status_code' => 204
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
                'lecturer_name' => $lecture->lecturer->lecturer_name,
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
                'lecturer_name' => $lecturer->lecturer_name,
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


}
