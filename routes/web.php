<?php

use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Inertia\Inertia;

//Auth Routes
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('home');

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/students', function () {
    return Inertia::render('students/StudentForm');
})->middleware(['auth'])->name('students.create');

Route::get('/students-list', [StudentController::class, 'showStudentListPage'])
    ->middleware(['auth'])
    ->name('students.list');

Route::get('/students/{student}', [StudentController::class, 'showStudentDetailPage'])
    ->middleware(['auth'])
    ->name('students.show');

Route::get('/students/{student}/edit', [StudentController::class, 'showStudentEditPage'])
    ->middleware(['auth'])
    ->name('students.edit');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
