<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Subject;
use App\Models\Lecture;
use App\Models\MasterTable;
use App\Models\User;
use App\Http\Controllers\AuthController;
use Illuminate\Routing\RouteGroup;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::get('subject/{id}/lectuers', function ($id) {
//     // $subjects = App\Models\Lecture::find($id)->subjects;
//     // $subjects = App\Models\Lecture::find($id)->;
//     $lectures = Subject::find($id)->lectures;
//     return response()->json([
//         'lectures' => $lectures,
//     ]);
// });




Route::post('update-student', [AttendanceController::class, 'updateStudent']);

Route::get('getstudents', [AttendanceController::class, 'getAllStudents']);


Route::get('master-table/{id}/lectures', [AttendanceController::class, 'getTableLectures']);

Route::get('checkAttendanceForAll/{student_id}', [AttendanceController::class, 'checkStateOfAllLecture']);


Route::get('checkAttendanceForOne/{student_id}/{lecture_id}', [AttendanceController::class, 'checkStateOfOneLecture']);


// =====================   Generate QR code   ========================

Route::post('addStudentForAttendance/{lecture_id}/{week_no}', [AttendanceController::class, 'addStudentForAttendance']);



// ===================== Get Student For Manual Attendance   =========================

Route::get('showStuForMenualAttend/{lecture_id}/{week_no}', [AttendanceController::class, 'showStuForMenualAttend']);

// ==================================================================


// ===================== Get Lectures Table For Student  =========================

// Route::get('getLecturesForStudent/{student_id}', [AttendanceController::class, 'getLecturesForStudent']);
Route::get('getLecturesForStudent/{student_id}', [AttendanceController::class, 'getLecturesForStudentTest']);

// ==================================================================

// ===================== Get Attendance Table For Student  =========================

Route::get('attendance-table', [AttendanceController::class, 'getAttendanceTableForStudent']);

// ==================================================================

// ===================== Student Scan Attendance  =========================
Route::put('studentScanAttendance/{student_id}/{lecture_id}/{week_no}', [AttendanceController::class, 'studentScanAttendance']);
// ===================================================================

// ===================== StudentManual Attendance  =========================
Route::put('studentManualAttendance/{student_id}/{lecture_id}/{week_no}', [AttendanceController::class, 'studentManualAttendance']);
// ===================================================================


// =====================   Report   ========================

Route::get('getAllTables', [AttendanceController::class, 'getAllTables']);

Route::get('getTableLectures', [AttendanceController::class, 'tableLectures']);

Route::get('batchAttendance/{lecture_id}', [AttendanceController::class, 'batchAttendance']);



// ===================================================================








// consumer => student or lecturer

Route::get('account/{id}/consumer', function ($id) {
    $user = User::find($id);
    // $type = $user->user_type;
    if ($user->user_type == "student") {
        return response()->json([
            "consumer" => $user->student
        ]);
    }
    return response()->json([
        "consumer" => $user->lecturer
    ]);
});

// =====================   Add new Lecture  ========================

Route::post('addlecture', [AttendanceController::class, 'addNewLecture']);

// ==================================================================

// =====================   Add new LectureR  ========================

Route::post('addlecturer', [AttendanceController::class, 'addNewLecturer']);

// ==================================================================

// =====================   Add new Student  ========================

Route::post('addstudent', [AttendanceController::class, 'addstudent']);

// ==================================================================

// =====================   Add new Table  ========================

Route::post('addtable', [AttendanceController::class, 'addTable']);

// ==================================================================

// =====================   Add new Table  ========================

Route::post('addsubject', [AttendanceController::class, 'addSubject']);

// ==================================================================
// =====================   Get All Lecturer Lectures  ========================

Route::get('lecturer/{lecturer_id}/lectures', [AttendanceController::class, 'getLecturerLectures']);

// ==================================================================


// ===========     Authentication   ======================




Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
        'message' => 'Get user secussfully'
    ]);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);



    // ===============================================================


    Route::get('lecture/{id}', [AttendanceController::class, 'getLectureById']);



    // ===============================================================
    // Route::get('users/{id}', function (Request $request) {

    //     $user = $request->user();
    //     return response()->json([
    //         'user' => $user
    //     ]);
    // });
});
// =====================================   DASHBOARD  =================================

Route::get('getStudents', [AttendanceController::class, 'getAllStudents']);

Route::get('getTables', [AttendanceController::class, 'getTables']);

Route::post('addStudent', [AttendanceController::class, 'addStudent']);

Route::post('updateStudent', [AttendanceController::class, 'updateStudent']);

Route::get('getLectureData', [AttendanceController::class, 'getLectureData']);

Route::post('addlecture', [AttendanceController::class, 'addLecture']);

Route::post('addPeriod', [AttendanceController::class, 'addPeriod']);

Route::post('updateLecture', [AttendanceController::class, 'updateLecture']);

Route::get('masterTableLecture/{table_id}', [AttendanceController::class, 'getMasterTableLectures']);

Route::post('addLecturer', [AttendanceController::class, 'addLecturer']);

Route::post('addSubject', [AttendanceController::class, 'addSubject']);

Route::post('updateLecturer', [AttendanceController::class, 'updateLecturer']);

Route::post('updateSubject', [AttendanceController::class, 'updateSubject']);

Route::get('getLecturers', [AttendanceController::class, 'getLecturers']);

Route::get('getSubjects', [AttendanceController::class, 'getSubjects']);