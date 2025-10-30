<?php

use App\Http\Controllers\SmsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappController;


// Página inicial
Route::get('/', function () {
    return redirect()->route('send.form');
});

// Autenticación
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/verify-email', [RegisterController::class, 'verifyEmail'])->name('verify.email');

// Dashboard y otros módulos
Route::get('/dashboard', [DashboardController::class, 'show'])->middleware('auth')->name('dashboard');
//Route::get('/customer', [CustomerController::class, 'show'])->middleware('auth')->name('customer');

// WhatsApp
Route::get('/whatsapp', [WhatsappController::class, 'showInbox'])->middleware('auth')->name('whatsapp.inbox');
Route::post('/whatsapp/sync', [WhatsappController::class, 'syncFromTwilio'])->name('whatsapp.sync');
Route::post('/send', [WhatsappController::class, 'sendMessage'])->name('send.action');

Route::delete('/whatsapp/delete/{id}', [WhatsappController::class, 'delete'])->name('whatsapp.delete');
Route::delete('/whatsapp/delete-multiple', [WhatsappController::class, 'deleteMultiple'])->name('whatsapp.deleteMultiple');
Route::get('/sent', [WhatsappController::class, 'showSent'])->middleware('auth')->name('whatsapp.sent');

// SMS
Route::get('/sms', [SmsController::class, 'index'])->middleware('auth')->name('sms');
Route::get('/sms/messages/{contact}', [SmsController::class, 'messages'])->name('sms.messages');
Route::post('/sms/sync', [SmsController::class, 'sync'])->name('sms.sync');
Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');
Route::delete('/sms/delete/{contact}', [SmsController::class, 'deleteOne'])->name('sms.deleteOne');
Route::post('/sms/delete-multiple', [SmsController::class, 'deleteMany'])->name('sms.deleteMany');
Route::get('/sms/search', [SmsController::class, 'search'])->name('sms.search');

// Customers

Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');
Route::post('/customers', [CustomersController::class, 'store'])->name('customers.store'); // guarda los 4 campos (AJAX)
Route::get('/profile/{id}', [CustomersController::class, 'profile'])->name('profile');
Route::put('/profile/{id}', [CustomersController::class, 'update'])->name('customers.update'); // guarda el resto del perfil
Route::post('/customers/delete-multiple', [CustomersController::class, 'deleteMultiple']);

Route::middleware(\App\Http\Middleware\RememberMeMiddleware::class)->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
});



