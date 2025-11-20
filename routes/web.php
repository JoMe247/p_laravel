<?php

use App\Http\Controllers\SmsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\SubUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HelpController;


// Página inicial
Route::get('/', function () {
    return redirect()->route('send.form');
});

// =======================
// 🔐 Autenticación
// =======================
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/verify-email', [RegisterController::class, 'verifyEmail'])->name('verify.email');

// =======================
// 🏠 Dashboard y módulos protegidos
// =======================
Route::middleware('auth.multi')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    // WhatsApp
    Route::get('/whatsapp', [WhatsappController::class, 'showInbox'])->name('whatsapp.inbox');
    Route::post('/whatsapp/sync', [WhatsappController::class, 'syncFromTwilio'])->name('whatsapp.sync');
    Route::post('/send', [WhatsappController::class, 'sendMessage'])->name('send.action');
    Route::delete('/whatsapp/delete/{id}', [WhatsappController::class, 'delete'])->name('whatsapp.delete');
    Route::delete('/whatsapp/delete-multiple', [WhatsappController::class, 'deleteMultiple'])->name('whatsapp.deleteMultiple');
    Route::get('/sent', [WhatsappController::class, 'showSent'])->name('whatsapp.sent');

    // Office (registro de sub-users)
    Route::get('/office', [SubUserController::class, 'create'])->name('office.create');
    Route::post('/office', [SubUserController::class, 'store'])->name('office.store');
    Route::get('/verify-subuser-email', [SubUserController::class, 'verifyEmail'])->name('subuser.verify');
});

// =======================
// 💬 SMS (protegido para users y sub_users)
// =======================
Route::middleware('auth.multi')->group(function () {
    Route::get('/sms', [SmsController::class, 'index'])->name('sms.index');
    Route::get('/sms/messages/{contact}', [SmsController::class, 'messages'])->name('sms.messages');
    Route::post('/sms/sync', [SmsController::class, 'sync'])->name('sms.sync');
    Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');
    Route::delete('/sms/delete/{contact}', [SmsController::class, 'deleteOne'])->name('sms.deleteOne');
    Route::post('/sms/delete-multiple', [SmsController::class, 'deleteMany'])->name('sms.deleteMany');
    Route::get('/sms/search', [SmsController::class, 'search'])->name('sms.search');
});

// =======================
// 👥 Customers
// =======================
Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');
Route::post('/customers', [CustomersController::class, 'store'])->name('customers.store'); // guarda los 4 campos (AJAX)
Route::get('/profile/{id}', [CustomersController::class, 'profile'])->name('profile');
Route::put('/profile/{id}', [CustomersController::class, 'update'])->name('customers.update'); // guarda el resto del perfil
Route::post('/customers/delete-multiple', [CustomersController::class, 'deleteMultiple']);

// =======================
// 🍪 Middleware RememberMe
// =======================
Route::middleware(\App\Http\Middleware\RememberMeMiddleware::class)->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
});


// Recuperar contraseña (simulado)
Route::get('/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.request');
Route::post('/reset', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

// Nueva contraseña
Route::get('/new-password/{token}', [ForgotPasswordController::class, 'showNewPassForm'])->name('password.reset');
Route::post('/new-password', [ForgotPasswordController::class, 'updatePassword'])->name('password.update');




Route::get('/office', [OfficeController::class, 'index'])->name('office.index');

// ✅ Ruta de eliminación con model binding
Route::delete('/office/subusers/{id}', [OfficeController::class, 'destroy'])
    ->name('office.delete');

Route::post('/office/agency/save', [OfficeController::class, 'saveAgency'])
    ->name('agency.save');

// Logo upload

Route::post('/office/upload-logo', [OfficeController::class, 'uploadLogo'])->name('office.uploadLogo');


Route::middleware(['auth:web,sub'])->group(function () {
    Route::get('/account', [AccountController::class, 'show'])
        ->name('account.show');
});

// Company Routes
Route::get('/company', [CompanyController::class, 'index'])->name('company');
Route::post('/company/store', [CompanyController::class, 'store']);
Route::get('/company/edit/{id}', [CompanyController::class, 'edit']);
Route::post('/company/update/{id}', [CompanyController::class, 'update']);
Route::post('/company/delete/{id}', [CompanyController::class, 'delete'])->name('company.delete');

// Help Routes
Route::get('/help', [App\Http\Controllers\HelpController::class, 'index'])->name('help');

