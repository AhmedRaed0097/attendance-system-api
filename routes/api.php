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


// ===========    *** Authentication ***   ======================

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
});
// ===============================================================


// ===================================== *** STUDENTS CRUD ***  =================================

Route::get('get-students', [AttendanceController::class, 'getAllStudents']);

Route::post('add-student', [AttendanceController::class, 'addStudent']);

Route::post('update-Student', [AttendanceController::class, 'updateStudent']);

Route::post('update-student', [AttendanceController::class, 'updateStudent']);

Route::get('getstudents', [AttendanceController::class, 'getAllStudents']);

// =====================================   //STUDENTS CRUD  =================================


// =====================================  *** TABLES CRUD *** =================================
Route::post('add-table', [AttendanceController::class, 'addTable']);

Route::post('update-table', [AttendanceController::class, 'updateTable']);

Route::delete('delete-table/{table_id}', [AttendanceController::class, 'deleteTable']);

Route::get('get-tables', [AttendanceController::class, 'getTables']);
// ملخبط شوي
Route::get('table-lectures/{table_id}', [AttendanceController::class, 'getMasterTableLectures']);

// مرتب اكثر
Route::get('master-table/{id}/lectures', [AttendanceController::class, 'getTableLectures']);


// =====================================   // TABLES CRUD  =================================

// =====================================  *** LECTURES CRUD ***  =================================

Route::post('add-lecture', [AttendanceController::class, 'addLecture']);

Route::post('update-lecture', [AttendanceController::class, 'updateLecture']);

Route::delete('delete-lecture/{lecture_id}', [AttendanceController::class, 'deleteLecture']);

Route::get('get-lectureData', [AttendanceController::class, 'getLectureData']);

Route::get('lecture/{id}', [AttendanceController::class, 'getLectureById']);


// ===================== Get Lectures Table For Student  =========================

Route::get('getLecturesForStudent/{student_id}', [AttendanceController::class, 'getLecturesForStudentTest']);

// ==============================================================================


// =====================================   // LECTURES CRUD  =================================

// =====================================  *** PERIODS CRUD *** ================================

Route::post('add-period', [AttendanceController::class, 'addPeriod']);

// TODO::
Route::post('update-period', [AttendanceController::class, 'updatePeriod']);

Route::post('delete-period', [AttendanceController::class, 'deletePeriod']);

Route::post('get-periods', [AttendanceController::class, 'getPeriods']);

// =====================================   // PERIODS CRUD  =================================

// =====================================   *** LECTURERS CRUD ***  =================================

Route::post('add-lecturer', [AttendanceController::class, 'addLecturer']);

Route::post('update-lecturer', [AttendanceController::class, 'updateLecturer']);

Route::get('get-lecturers', [AttendanceController::class, 'getLecturers']);

Route::delete('delete-lecturer/{lecturer_id}', [AttendanceController::class, 'deleteLecturer']);

Route::get('lecturer/{lecturer_id}/lectures', [AttendanceController::class, 'getLecturerLectures']);


// =====================================   // LECTURERS CRUD  =================================


// =====================   *** SUBJECTS CRUD ***  ========================

Route::post('add-subject', [AttendanceController::class, 'addSubject']);

Route::post('update-subject', [AttendanceController::class, 'updateSubject']);

Route::get('get-subjects', [AttendanceController::class, 'getSubjects']);

// =====================   // SUBJECTS CRUD   ========================





// =====================   *** ATTENDANCE CRUD ***   ========================


// ===================== Get Attendance Table For Student  =========================

Route::get('attendance-table', [AttendanceController::class, 'getAttendanceTableForStudent']);

// =================================================================================


// =====================   Generate QR code   =====================================

Route::post('generate-qr/{lecture_id}/{week_no}', [AttendanceController::class, 'addStudentForAttendance']);
// =================================================================================

// ===================== Student Scan Attendance  =================================
//put
Route::post('studentScanAttendance/{student_id}/{lecture_id}/{week_no}', [AttendanceController::class, 'studentScanAttendance']);
// =================================================================================



// ===================== Get Student For Manual Attendance   =========================

Route::get('students-list-manual-attendance/{lecture_id}/{week_no}', [AttendanceController::class, 'showStuForMenualAttend']);
// =================================================================================


// ===================== StudentManual Attendance  =========================
//put
Route::post('student-attend/{student_id}/{lecture_id}/{week_no}', [AttendanceController::class, 'studentManualAttendance']);
// ===================================================================



Route::get('checkAttendanceForOne/{student_id}/{lecture_id}', [AttendanceController::class, 'checkStateOfOneLecture']);




// =====================   // ATTENDANCE CRUD   ========================
