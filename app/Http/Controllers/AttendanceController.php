<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lecture;
use App\Models\Lecturer;
use App\Models\MasterTable;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\Period;

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
                'major' => $student->major,
                'level' => $student->level,
                'state' => $student->state,
                'batch_type' => $student->batch_type
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
        // $attend = Attendance::all()->where('student_id', $student_id)->where('lecture_id', $lecture_id)->where('week_no', $week_no)->first()->update(['state' => 1]);
        // $attend = Attendance::first()->where('student_id', $student_id)->get();
        if ($request->lecture_id == null) {
            $attend = Attendance::first()->where('student_id', $request->student_id)->get();
        } else {
            $attend = Attendance::first()->where('student_id', $request->student_id)->where('lecture_id', $request->lecture_id)->get();
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
        $lecrure =  Lecture::create($data);

        return response()->json([
            'lecture_data' => $lecrure,
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
        $lecrure =  Lecturer::create($data);

        return response()->json([
            'lecture_data' => $lecrure,
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
        $lecrure =  Subject::create($data);

        return response()->json([
            'lecture_data' => $lecrure,
            'message' => 'The subject has been successfully added',
            'status_code' => 201
        ]);
    }
    public function deleteSubject($subject_id)
    {
        // Send on body => [  Subject_name  ]

        $data = Subject::where('id', $subject_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'The subject has been successfully deleted',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'This record not found',
                'status_code' => 404
            ]);
        }
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
        $lecrure =  MasterTable::create($data);

        return response()->json([
            'lecture_data' => $lecrure,
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
        // $lecrure =  MasterTable::create($data);

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
    public function getLecturesForStudentTest($student_id)
    {

        $student_data = Student::find($student_id);

        $studentLectureList = [];
        // $tables_list = MasterTable::first()->where('level', $student_data->level)->where('major', $student_data->major)->where('batch_type', $student_data->batch_type)->get();
        // $tables_list = MasterTable::first()->where('id', $student_data->master_table_id)->get();
        $lectures_data = MasterTable::find($student_data->master_table_id)->lectures;

        // $lectures_list = $tables_list[0]->lectures;
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
            'data' => $studentLectureList
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

    public function batchAttendance($lecture_id)
    {

        // $lecture = Attendance::where('lecture_id',$lecture_id)->get();
        // $students_batch = Student::where('master_table_id',$lecture->master_table_id)
        // // $attendance =
        // return $lecture;








        // $batchAttendance2 = Attendance::where('lecture_id', $lecture_id)->get();
        // $result = [];
        // foreach ($batchAttendance2 as $record) {

        //     if () {
        //         # code...
        //     }
        //     $result['attendace ' . $record->id] = [

        //         'student_name' => $record->student->student_name,



        //     ];
        //     foreach ($batchAttendance2 as $record2) {

        //         if ($record2->student_id == $record->student_id) {


        //             array_push($result['attendace ' . $record->id], [
        //                 'week_no ' . $record2->week_no => $record->state

        //             ]);
        //         }
        //     }
        // };



        // // return $batchAttendance2;
        // return $result;








    }

    // ==================================  DASHBOARD  =================================


    public function getAllStudentsDashboard()
    {
        $result = [];
        $students = Student::all();
        foreach ($students as $student) {
            array_push($result, [
                'id' => $student->id,
                'value' => $student->student_name,
                'master_table_id' => $student->master_table_id,
                'state' => $student->state,
            ]);
        }
        return response()->json([
            'students_data' => $result
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

    public function addStudentDashboard()
    {
        $data = request()->all();
        $student =  Student::create($data);

        return response()->json([
            'student_data' => $student,
            'message' => 'The student has been successfully added',
            'status_code' => 201
        ]);
    }

    public function updateStudentDashboard(Request $request)
    {
        $student = Student::find($request->id);
        $student->update([
            'student_name' => $request->student_name,
            'master_table_id' => $request->master_table_id,
            'state' => $request->state,
        ]);
        $student->save();

        return response()->json([
            'student_data' => $student,
            'message' => 'The student has been successfully updated',
            'status_code' => 204
        ]);
    }

    public function getLectureData(Request $request)
    {
        $tableResult = [];
        $subjectResult = [];
        $lecturerResult = [];



        $tables = MasterTable::all();
        foreach ($tables as $table) {
            array_push($tableResult, [
                'id' => $table->id,
                'title' => $table->title,
            ]);
        }

        $subjects = Subject::all();
        foreach ($subjects as $subject) {
            array_push($subjectResult, [
                'id' => $subject->id,
                'subject_name' => $subject->subject_name,
            ]);
        }

        $lecturers = Lecturer::all();
        foreach ($lecturers as $lecturer) {
            array_push($lecturerResult, [
                'id' => $lecturer->id,
                'lecturer_name' => $lecturer->lecturer_name,
            ]);
        }

        return response()->json([
            'table_data' => $tableResult,
            'subject_data' => $subjectResult,
            'lecturer_data' => $lecturerResult,
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
                'message' => 'The period has been successfully added',
                'status_code' => 201
            ]);
        } else {
            return response()->json([
                'period_data' => $period,
                'message' => 'The period already Exist',
                'status_code' => 201
            ]);
        }
    }
    public function deletePeriod($period_id)
    {
        // Send on body => [  Subject_name  ]

        $data = Period::where('id', $period_id)->delete();

        if ($data == 1) {
            return response()->json([
                'message' => 'The period has been successfully deleted',
                'status_code' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'This record not found',
                'status_code' => 404
            ]);
        }
    }

    public function addLectureDashboard(Request $request)
    {

        $lectureExist = Lecture::where('master_table_id', $request->master_table_id)->where('period_id', $request->period_id)->where('lecturer_id', $request->lecturer_id)->where('subject_id', $request->subject_id)->first();
        if (!$lectureExist) {
            $lectureSameTime = Lecture::where('master_table_id', $request->master_table_id)->where('period_id', $request->period_id)->first();
            if (!$lectureSameTime) {
                $lecturerTime = Lecture::where('lecturer_id', $request->lecturer_id)->where('period_id', $request->period_id)->first();
                if (!$lecturerTime) {
                    $data = request()->all();
                    $lecture = Lecture::create($data);
                    return response()->json([
                        'message' => 'The lecture was added successfuly',
                    ]);
                } else {
                    return response()->json([
                        'message' => 'The lecturer has lecture at same time in another class',
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'The class has lecture at same time',
                ]);
            }
        } else {
            return response()->json([
                'message' => 'The lecture already exist',
            ]);
        }
    }

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

    public function addLecturerDashboard()
    {
        $data = request()->all();
        $lecturer =  Lecturer::create($data);

        return response()->json([
            'lecturer_data' => $lecturer,
            'message' => 'The lecturer has been successfully added',
            'status_code' => 201
        ]);
    }

    public function addSubjectDashboard()
    {
        $data = request()->all();
        $subject =  Subject::create($data);

        return response()->json([
            'subject_data' => $subject,
            'message' => 'The subject has been successfully added',
            'status_code' => 201
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
            'subject_data' => $subjectResult,
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








        $lecturer->update([
            'lecturer_name' => $request->lecturer_name,
            'state' => $request->state,

        ]);
        $lecturer->save();

        return response()->json([
            'lecturer_data' => $lecturer,
            'message' => 'The lecturer has been successfully updated',
            'status_code' => 204
        ]);
    }

    public function updateSubject(Request $request)
    {
        $subject = Subject::find($request->id);

        $subject->update([
            'subject_name' => $request->subject_name,
        ]);
        $subject->save();

        return response()->json([
            'subject_data' => $subject,
            'message' => 'The subject has been successfully updated',
            'status_code' => 204
        ]);
    }
}
