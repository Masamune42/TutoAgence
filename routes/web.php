<?php

use App\Http\Controllers\Admin\PropertyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Création d'une route /admin
Route::prefix('admin')->name('admin.')->group(function() {
    // Création d'une route /admin/property qui utilise le PropertyController
    Route::resource('property', PropertyController::class)->except(['show']);
});