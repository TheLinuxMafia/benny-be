<?php
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProfileController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', [PDFController::class, 'generatePDF']);

Route::get('/', function () {
   return view('welcome');
});

Route::get('/app/profile/2fa', [ProfileController::class, 'twofa']);
Route::post('/app/profile/2fa', [ProfileController::class, 'twofaEnable']);
Route::get('/login/otp', 'Auth\OTPController@show');
Route::post('/login/otp', 'Auth\OTPController@check');
