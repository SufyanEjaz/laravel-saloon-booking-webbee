<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

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

// Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1', 'middleware' => ['auth:api']], function () {

    // Get all available slots for a specific service and date
    Route::get('/available-slots', [AppointmentController::class, 'getAvailableSlots']);

    // Create a new booking for a service and time slot
    Route::post('/appointments', [AppointmentController::class, 'storeAppointment']);
// });