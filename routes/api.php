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

Route::post('generate-qr/{lecture_id}/{week_no}', [AttendanceController::class, 'addStudentForAttendance']);



// ===================== Get Student For Manual Attendance   =========================

Route::get('students-list-manual-attendance/{lecture_id}/{week_no}', [AttendanceController::class, 'showStuForMenualAttend']);

// ==================================================================


// ===================== Get Lectures Table For Student  =========================

// Route::get('getLecturesForStudent/{student_id}', [AttendanceController::class, 'getLecturesForStudent']);
Route::get('getLecturesForStudent/{student_id}', [AttendanceController::class, 'getLecturesForStudentTest']);

// ==================================================================

// ===================== Get Attendance Table For Student  =========================

Route::get('attendance-table', [AttendanceController::class, 'getAttendanceTableForStudent']);

// ==================================================================

// ===================== Student Scan Attendance  =========================
    //put
Route::post('studentScanAttendance/{student_id}/{lecture_id}/{week_no}', [AttendanceController::class, 'studentScanAttendance']);
// ===================================================================

// ===================== StudentManual Attendance  =========================
    //put
Route::post('student-attend/{student_id}/{lecture_id}/{week_no}', [AttendanceController::class, 'studentManualAttendance']);
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

Route::post('add-lecture', [AttendanceController::class, 'addNewLecture']);

// ==================================================================
// =====================   Add new Lecture  ========================

Route::delete('delete-lecture/{lecture_id}', [AttendanceController::class, 'deleteLecture']);

// ==================================================================

// =====================   Add new LectureR  ========================

Route::post('add-lecturer', [AttendanceController::class, 'addNewLecturer']);

// ==================================================================

// =====================   Add new LectureR  ========================

Route::delete('delete-lecturer/{lecturer_id}', [AttendanceController::class, 'deleteLecturer']);

// ==================================================================

// =====================   Add new Student  ========================

Route::post('add-student', [AttendanceController::class, 'addstudent']);

// ==================================================================

// =====================   Add new Table  ========================

Route::post('add-table', [AttendanceController::class, 'addTable']);

// ==================================================================

// =====================   Add new Table  ========================

Route::post('update-table', [AttendanceController::class, 'updateTable']);

// ==================================================================
// =====================   Add new Table  ========================

Route::delete('delete-table/{table_id}', [AttendanceController::class, 'deleteTable']);

// ==================================================================

// =====================   Add new Table  ========================

Route::post('add-subject', [AttendanceController::class, 'addSubject']);

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

Route::get('get-students', [AttendanceController::class, 'getAllStudents']);

Route::get('get-tables', [AttendanceController::class, 'getTables']);

Route::post('add-student', [AttendanceController::class, 'addStudent']);

Route::post('update-Student', [AttendanceController::class, 'updateStudent']);

Route::get('get-lectureData', [AttendanceController::class, 'getLectureData']);

Route::post('add-lecture', [AttendanceController::class, 'addLecture']);

Route::post('add-period', [AttendanceController::class, 'addPeriod']);

Route::post('update-lecture', [AttendanceController::class, 'updateLecture']);

Route::get('table-lectures/{table_id}', [AttendanceController::class, 'getMasterTableLectures']);

Route::post('add-lecturer', [AttendanceController::class, 'addLecturer']);

Route::post('add-subject', [AttendanceController::class, 'addSubject']);

Route::post('update-lecturer', [AttendanceController::class, 'updateLecturer']);

Route::post('update-subject', [AttendanceController::class, 'updateSubject']);

Route::get('get-lecturers', [AttendanceController::class, 'getLecturers']);

Route::get('get-subjects', [AttendanceController::class, 'getSubjects']);
