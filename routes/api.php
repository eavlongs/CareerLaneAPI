<?php

use App\FileHelper;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureIsCompany;
use App\Http\Middleware\EnsureIsUser;

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

Route::prefix("sessions")->group(function () {
    Route::get("/{session_id}", [AuthController::class, 'getSessionAndUser']);
    Route::post("/", [AuthController::class, 'setSession']);
    Route::patch("/{session_id}", [AuthController::class, 'updateSessionExpiration']);
    Route::delete("/{session_id}", [AuthController::class, 'deleteSession']);
    Route::delete("/{account_id}/sessions", [AuthController::class, 'deleteUserSessions']);
    Route::delete("/expired", [AuthController::class, 'deleteExpiredSessions']);
});

Route::prefix("jobs")->group(function () {
    Route::middleware([EnsureIsCompany::class])->group(function () {
        Route::post("/", [JobController::class, 'createJob']);
        Route::patch("/{id}/inactive", [JobController::class, 'markJobAsInactive']);
        Route::patch("/{id}", [JobController::class, 'updateJob']);
    });
    Route::get("/categories", [JobController::class, "getJobCategories"]);
    Route::get("/{id}", [JobController::class, 'getJob']);
    Route::get("/", [JobController::class, 'getJobs']);
    Route::get("/{id}/applications", [JobController::class, 'getJobApplications'])->middleware(EnsureIsCompany::class);

    Route::post("/{id}/apply", [JobController::class, 'applyJob'])->middleware(EnsureIsUser::class);
});

Route::prefix("companies")->group(function () {
    Route::get("/{company_id}/jobs", [JobController::class, 'getCompanyJobs']);
    Route::get("/featured", [CompanyController::class, 'getFeaturedCompanies']);
});

Route::prefix("provinces")->group(function () {
    Route::get("/", [GeneralController::class, 'getProvinces']);
});

Route::prefix("accounts")->group(function () {
    Route::get("/{account_id}/sessions", [AuthController::class, 'getUserSessions']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register-company', [AuthController::class, 'registerCompany']);
    Route::post('/login', [AuthController::class, 'login']);
    // Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/login/provider', [AuthController::class, 'loginProvider']);
    Route::post('/send-verification-email', [AuthController::class, 'sendEmail'])->middleware('company:false');
    Route::get('/verify-token', [AuthController::class, 'verifyToken']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/send-reset-password-email', [AuthController::class, 'sendResetPasswordEmail']);
    Route::get('/verify-reset-password-token/{token}', [AuthController::class, 'verifyResetPasswordToken']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::post('/link-account', [AuthController::class, 'linkAccount'])->middleware(EnsureIsCompany::class);
    Route::post('/delete-account', [AuthController::class, 'deleteAccount'])->middleware(EnsureIsCompany::class);
});

Route::prefix('user')->group(function () {
    Route::get('/profile-information', [UserController::class, 'userProfileInformation']);
    Route::post('/edit-profile', [UserController::class, "editUserProfile"]);
    Route::post('/upload-profile-picture', [UserController::class, "uploadProfilePicture"]);
});

Route::prefix('company')->middleware(EnsureIsCompany::class)->group(function () {
    Route::get('/information', [CompanyController::class, 'companyInformation']);
    Route::get('/all', [CompanyController::class, 'getAllCompanies']);
});
