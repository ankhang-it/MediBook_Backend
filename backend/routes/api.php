<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Public routes for departments page
Route::get('public/doctors', [AdminController::class, 'getDoctorsPublic']);
Route::get('public/specialties', [SpecialtyController::class, 'getSpecialties']);

// Public routes for time slots
Route::get('public/time-slots', [TimeSlotController::class, 'getAvailableSlots']);
Route::post('public/book-slot', [TimeSlotController::class, 'bookSlot']);

// Payment routes
Route::post('payment/create', [PaymentController::class, 'createPayment']);
Route::get('payment/status', [PaymentController::class, 'getPaymentStatus']);
Route::match(['get', 'post'], 'payment/callback', [PaymentController::class, 'callback'])
    ->withoutMiddleware(['auth:api']);

// Protected routes
Route::group(['middleware' => 'api.auth'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);
    Route::post('upload-avatar', [AuthController::class, 'uploadAvatar']);

    // User routes
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    // Patient routes
    Route::prefix('patient')->group(function () {
        Route::get('profile', [PatientController::class, 'getProfile']);
        Route::put('profile', [PatientController::class, 'updateProfile']);
        Route::post('avatar', [PatientController::class, 'uploadAvatar']);
        Route::get('medical-history', [PatientController::class, 'getMedicalHistory']);
        Route::get('appointments', [PatientController::class, 'getAppointments']);
    });

    // Doctor routes
    Route::prefix('doctor')->group(function () {
        Route::get('profile', [DoctorController::class, 'getProfile']);
        Route::post('profile', [DoctorController::class, 'updateProfile']);
        Route::get('appointments', [DoctorController::class, 'getAppointments']);
        Route::put('appointments/{appointmentId}/status', [DoctorController::class, 'updateAppointmentStatus']);
        Route::get('statistics', [DoctorController::class, 'getStatistics']);
    });

    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'getDashboardStats']);
        Route::get('users', [AdminController::class, 'getUsers']);
        Route::post('users', [AdminController::class, 'createUser']);
        Route::put('users/{userId}', [AdminController::class, 'updateUser']);
        Route::delete('users/{userId}', [AdminController::class, 'deleteUser']);
        Route::get('doctors', [AdminController::class, 'getDoctors']);
        Route::get('patients', [AdminController::class, 'getPatients']);
        Route::get('appointments', [AdminController::class, 'getAppointments']);
    });

    // Specialty routes
    Route::prefix('specialties')->group(function () {
        Route::get('/', [SpecialtyController::class, 'getSpecialties']);
        Route::get('/{id}', [SpecialtyController::class, 'getSpecialty']);
        Route::post('/', [SpecialtyController::class, 'createSpecialty']);
        Route::put('/{id}', [SpecialtyController::class, 'updateSpecialty']);
        Route::delete('/{id}', [SpecialtyController::class, 'deleteSpecialty']);
        Route::get('/{id}/doctors', [SpecialtyController::class, 'getDoctorsBySpecialty']);
    });
});

// Payment routes
Route::post('payment/create', [PaymentController::class, 'createPayment']);
Route::get('payment/status', [PaymentController::class, 'getPaymentStatus']);
Route::match(['get', 'post'], 'payment/callback', [PaymentController::class, 'callback'])
    ->withoutMiddleware(['auth:api']);

// // Health check route
// Route::get('/health', function () {
//     return response()->json([
//         'status' => 'OK',
//         'message' => 'MediBook API is running',
//         'timestamp' => now()
//     ]);
// });
