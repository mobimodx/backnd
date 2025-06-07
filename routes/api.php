<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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
Artisan::call('up');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/get_apis','ApiController@GetApis');
Route::post('/text_to_img','ApiController@TextToImg');
Route::post('/img_to_img','ApiController@ImgToImg');
Route::post('/outfit','ApiController@Outfit');
Route::post('/interior_design','ApiController@InteriorDesign');
