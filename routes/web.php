<?php
use App\Http\Controllers\SmsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomersController;
use Illuminate\Support\Facades\Route;//por defecto
use App\Http\Controllers\WhatsappController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
Route::get('/customers', [CustomersController::class, 'show'])->name('customers');


Route::get('/', function () { return redirect()->route('send.form'); });

//Route::get('/send', [WhatsappController::class, 'showSendForm'])->name('send.form');
Route::post('/send', [WhatsappController::class, 'sendMessage'])->name('send.action');

Route::get('/inbox', [WhatsappController::class, 'showInbox'])->name('inbox');

// Botón/manual: sincroniza desde Twilio y guarda en tu BD
Route::post('/inbox/sync', [WhatsappController::class, 'syncFromTwilio'])->name('inbox.sync');

// Botón/manual: eliminar mensajes

Route::delete('/inbox/delete/{id}', [App\Http\Controllers\WhatsappController::class, 'delete'])->name('inbox.delete');
Route::delete('/inbox/delete-multiple', [App\Http\Controllers\WhatsappController::class, 'deleteMultiple'])->name('inbox.deleteMultiple');

Route::get('/sent', [WhatsappController::class, 'showSent'])->name('sent');

// Rutas SMS

Route::get('/sms', [SmsController::class, 'index'])->name('sms.index');
Route::get('/sms/messages/{contact}', [SmsController::class, 'messages'])->name('sms.messages');
Route::post('/sms/sync', [SmsController::class, 'sync'])->name('sms.sync'); // botón para sincronizar
Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');

// Eliminar mensajes sms
Route::delete('/sms/delete/{contact}', [SmsController::class, 'deleteOne'])->name('sms.deleteOne');
Route::post('/sms/delete-multiple', [SmsController::class, 'deleteMany'])->name('sms.deleteMany');
