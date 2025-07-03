<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\ServiceController;
use App\Http\Controllers\admin\TempImageController;
use App\Http\Controllers\AuthenticationController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("authenticate", [AuthenticationController::class, "authenticate"]);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(["middleware" => ["auth:sanctum"]], function() {

    Route::get("dashboard", [DashboardController::class, "index"]);
    Route::get("logout", [AuthenticationController::class, "logout"]);

    Route::post("services", [ServiceController::class, "store"]);
    Route::get("services", [ServiceController::class, "index"]);
    Route::put("services/{id}", [ServiceController::class,"update"]);
    Route::get("services/{id}", [ServiceController::class, "show"]);
    Route::delete("services/{id}", [ServiceController::class,"destroy"]);

    Route::post("temp-images", [TempImageController::class, "store"]);

});

Route::get('/users', function () {
    return User::all();
});

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return "DB connection successs!";
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});